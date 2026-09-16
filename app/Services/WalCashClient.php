<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Client WalCash Pay — encaissement MonCash et portefeuille EziPay.
 *
 * MonCash est le moyen de paiement du public haïtien ; PayPal sert
 * surtout la diaspora. Les deux cohabitent : le lecteur choisit.
 *
 * Le parcours tient en trois temps :
 *   1. on crée le paiement côté serveur et on reçoit une checkout_url ;
 *   2. le payeur est redirigé vers cette page ;
 *   3. WalCash confirme par webhook signé — et c'est seulement là qu'on
 *      valide la contrepartie, jamais au retour du navigateur, qui peut
 *      être falsifié ou simplement abandonné.
 */
class WalCashClient
{
    private const BASE_URL = 'https://pay.walcash.com/v1';

    public function __construct(
        private readonly ?string $apiKey = null,
        private readonly string $mode = 'test',
    ) {
    }

    public static function fromConfig(): self
    {
        $mode = SiteSetting::get('walcash_mode', 'test');

        $key = $mode === 'live'
            ? SiteSetting::secret('walcash_live_key')
            : SiteSetting::secret('walcash_test_key');

        return new self($key ?: null, $mode);
    }

    /** Taux appliqué pour convertir un montant en dollars vers la gourde. */
    public static function htgRate(): float
    {
        return max(1, (float) SiteSetting::get('htg_per_usd', '132'));
    }

    /**
     * Montant à débiter en gourdes.
     *
     * MonCash refuse toute autre devise ; une campagne libellée en
     * dollars est convertie ici, au taux réglé par la rédaction.
     *
     * @return array{amount: float, currency: string, converted: bool}
     */
    public static function toHtg(float $amount, string $currency): array
    {
        if (strtoupper($currency) === 'HTG') {
            return ['amount' => $amount, 'currency' => 'HTG', 'converted' => false];
        }

        return [
            'amount'    => round($amount * self::htgRate(), 2),
            'currency'  => 'HTG',
            'converted' => true,
        ];
    }

    public function isConfigured(): bool
    {
        return filled($this->apiKey);
    }

    public function mode(): string
    {
        return $this->mode;
    }

    /**
     * Crée un paiement et renvoie l'identifiant et l'URL de règlement.
     *
     * @param  float   $amount     Montant en unités normales (25.00), converti ici.
     * @param  string  $currency   HTG ou USD.
     * @param  string  $reference  Référence interne, sert aussi de clé d'idempotence.
     * @param  ?string $method     'moncash', 'ezipay', ou null pour laisser choisir.
     * @param  array<string, mixed> $metadata
     *
     * @return array{id: string, status: string, checkout_url: string}
     */
    public function createPayment(
        float $amount,
        string $currency,
        string $reference,
        string $description,
        ?string $method = 'moncash',
        array $metadata = []
    ): array {
        /*
         * MonCash n'accepte que la gourde : on convertit plutôt que de
         * laisser l'API refuser la demande.
         */
        if ($method === 'moncash') {
            $converted = self::toHtg($amount, $currency);
            $amount    = $converted['amount'];
            $currency  = $converted['currency'];
        }

        $payload = array_filter([
            // L'API attend des unités mineures : 2500 = 25,00 HTG.
            'amount'        => (int) round($amount * 100),
            'currency'      => strtoupper($currency),
            'paymentMethod' => $method,
            'description'   => $description,
            'metadata'      => $metadata ?: null,
        ], fn ($value) => $value !== null);

        /*
         * Pas de nouvelle tentative ici : la clé d'idempotence étant la
         * même, un second envoi se heurte à un 409 « duplicate » qui
         * masque l'erreur d'origine. Mieux vaut remonter le vrai motif.
         */
        $response = $this->request(retry: false)
            ->withHeaders(['Idempotency-Key' => $reference])
            ->post('/payments/create', $payload);

        if (! $response->successful()) {
            throw new RuntimeException(
                'WalCash: création de paiement refusée — '.$response->body()
            );
        }

        return [
            'id'           => (string) $response->json('id'),
            'status'       => (string) $response->json('status'),
            'checkout_url' => (string) $response->json('checkout_url'),
        ];
    }

    /** État d'un paiement : requires_action, processing, succeeded, failed, canceled. */
    public function getPayment(string $paymentId): array
    {
        $response = $this->request()->get('/payments/'.$paymentId);

        if (! $response->successful()) {
            throw new RuntimeException(
                'WalCash: paiement introuvable — '.$response->body()
            );
        }

        return (array) $response->json();
    }

    /** Vérifie que les clés permettent d'interroger le compte marchand. */
    public function check(): array
    {
        if (! $this->isConfigured()) {
            return ['ok' => false, 'message' => 'Clé API absente.'];
        }

        try {
            $response = $this->request()->get('/balance');

            return $response->successful()
                ? ['ok' => true, 'message' => 'Connexion réussie en mode '.$this->mode.'.']
                : ['ok' => false, 'message' => 'Réponse '.$response->status().' — '.$response->body()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Valide la signature d'un webhook.
     *
     * En-tête « WalCashPay-Signature: t=<unix>,v1=<hex> », où v1 est le
     * HMAC-SHA256 de "<t>.<corps brut>". On compare avec hash_equals,
     * pour ne pas laisser fuir d'information par le temps de comparaison.
     */
    public static function verifySignature(
        string $rawBody,
        ?string $signatureHeader,
        ?string $secret = null,
        int $toleranceSeconds = 300
    ): bool {
        $secret ??= SiteSetting::secret('walcash_webhook_secret');

        if (blank($secret) || blank($signatureHeader)) {
            return false;
        }

        $parts = [];

        foreach (explode(',', $signatureHeader) as $chunk) {
            $pair = explode('=', trim($chunk), 2);

            if (count($pair) === 2) {
                $parts[$pair[0]] = $pair[1];
            }
        }

        $timestamp = $parts['t'] ?? null;
        $signature = $parts['v1'] ?? null;

        if ($timestamp === null || $signature === null) {
            return false;
        }

        // Rejoue tardif : une signature valable ne l'est pas indéfiniment.
        if (abs(time() - (int) $timestamp) > $toleranceSeconds) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$rawBody, $secret);

        return hash_equals($expected, $signature);
    }

    private function request(bool $retry = true): PendingRequest
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'WalCash Pay n’est pas configuré : renseignez la clé API '
                .'dans Administration → Réglages du site.'
            );
        }

        $request = Http::baseUrl(self::BASE_URL)
            ->withToken((string) $this->apiKey)
            ->acceptJson()
            ->timeout(30);

        return $retry ? $request->retry(2, 500) : $request;
    }
}
