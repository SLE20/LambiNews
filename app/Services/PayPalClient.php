<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Client minimal pour l’API PayPal Orders v2.
 *
 * Écrit directement sur le client HTTP de Laravel plutôt qu’avec le SDK
 * officiel : l’hébergement est mutualisé, et une dépendance de moins
 * signifie un déploiement de moins à reconstruire.
 */
class PayPalClient
{
    private const LIVE_BASE    = 'https://api-m.paypal.com';
    private const SANDBOX_BASE = 'https://api-m.sandbox.paypal.com';

    public function __construct(
        private readonly ?string $clientId = null,
        private readonly ?string $secret = null,
        private readonly string $mode = 'sandbox',
    ) {
    }

    public static function fromConfig(): self
    {
        return new self(
            config('services.paypal.client_id'),
            config('services.paypal.secret'),
            (string) config('services.paypal.mode', 'sandbox'),
        );
    }

    public function isConfigured(): bool
    {
        return filled($this->clientId) && filled($this->secret);
    }

    public function baseUrl(): string
    {
        return $this->mode === 'live' ? self::LIVE_BASE : self::SANDBOX_BASE;
    }

    /**
     * Crée une commande PayPal et renvoie son identifiant.
     *
     * @return array{id: string, status: string}
     */
    public function createOrder(
        string $amount,
        string $currency,
        string $reference,
        string $description
    ): array {
        $response = $this->request()->post('/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => $reference,
                'custom_id'    => $reference,
                'description'  => $description,
                'amount'       => [
                    'currency_code' => $currency,
                    'value'         => $amount,
                ],
            ]],
            'application_context' => [
                'brand_name'          => 'Lambi News',
                'locale'              => 'fr-FR',
                'landing_page'        => 'NO_PREFERENCE',
                'shipping_preference' => 'NO_SHIPPING',
                'user_action'         => 'PAY_NOW',
            ],
        ]);

        if (! $response->successful()) {
            throw new RuntimeException(
                'PayPal: création de commande refusée — '.$response->body()
            );
        }

        return [
            'id'     => (string) $response->json('id'),
            'status' => (string) $response->json('status'),
        ];
    }

    /**
     * Capture une commande approuvée par le donateur.
     *
     * @return array<string, mixed> Réponse brute de PayPal.
     */
    public function captureOrder(string $orderId): array
    {
        $response = $this->request()
            // Empêche une double capture si le donateur rafraîchit la page.
            ->withHeaders(['PayPal-Request-Id' => 'capture-'.$orderId])
            ->post("/v2/checkout/orders/{$orderId}/capture");

        if (! $response->successful()) {
            throw new RuntimeException(
                'PayPal: capture refusée — '.$response->body()
            );
        }

        return (array) $response->json();
    }

    /** Détail d’une commande, utilisé pour vérifier le montant réellement payé. */
    public function getOrder(string $orderId): array
    {
        $response = $this->request()->get("/v2/checkout/orders/{$orderId}");

        if (! $response->successful()) {
            throw new RuntimeException(
                'PayPal: commande introuvable — '.$response->body()
            );
        }

        return (array) $response->json();
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())
            ->withToken($this->accessToken())
            ->acceptJson()
            ->timeout(30)
            ->retry(2, 500);
    }

    /**
     * Jeton OAuth2, mis en cache jusqu’à peu avant son expiration.
     */
    private function accessToken(): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'PayPal n’est pas configuré : renseignez PAYPAL_CLIENT_ID '
                .'et PAYPAL_SECRET dans le fichier .env.'
            );
        }

        $cacheKey = 'paypal.token.'.$this->mode.'.'.substr(sha1((string) $this->clientId), 0, 12);

        return Cache::remember($cacheKey, now()->addMinutes(20), function (): string {
            $response = Http::baseUrl($this->baseUrl())
                ->asForm()
                ->withBasicAuth((string) $this->clientId, (string) $this->secret)
                ->timeout(30)
                ->post('/v1/oauth2/token', ['grant_type' => 'client_credentials']);

            if (! $response->successful()) {
                throw new RuntimeException(
                    'PayPal: authentification échouée — '.$response->body()
                );
            }

            return (string) $response->json('access_token');
        });
    }
}
