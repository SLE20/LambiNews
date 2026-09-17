<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Services\MonCashCheckout;
use App\Services\PayPalClient;
use App\Services\WalCashClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class DonationController extends Controller
{
    /** Montants proposés, en dollars. */
    private const PRESETS = [10, 25, 50, 100];

    private const MIN_AMOUNT = 2;

    private const MAX_AMOUNT = 5000;

    private const CURRENCY = 'USD';

    public function create(): View
    {
        $paypal = PayPalClient::fromConfig();

        return view('front.donations.create', [
            'presets'        => self::PRESETS,
            'minAmount'      => self::MIN_AMOUNT,
            'maxAmount'      => self::MAX_AMOUNT,
            'currency'       => self::CURRENCY,
            'paypalClientId' => PayPalClient::publicClientId(),
            'paypalReady'    => $paypal->isConfigured(),
            'moncashReady'   => WalCashClient::fromConfig()->isConfigured(),
            'htgRate'        => WalCashClient::htgRate(),
            'totalRaised'    => Donation::completed()->sum('amount'),
            'donorCount'     => Donation::completed()->count(),
        ]);
    }

    /**
     * Crée la commande PayPal et l’enregistre en « pending ».
     *
     * Le montant est fixé ici, côté serveur : le navigateur n’envoie qu’une
     * intention, jamais un prix que PayPal accepterait tel quel.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount'       => ['required', 'numeric', 'min:'.self::MIN_AMOUNT, 'max:'.self::MAX_AMOUNT],
            'donor_name'   => ['nullable', 'string', 'max:120'],
            'donor_email'  => ['nullable', 'email', 'max:190'],
            'message'      => ['nullable', 'string', 'max:500'],
            'is_anonymous' => ['nullable', 'boolean'],
            'provider'     => ['nullable', 'in:paypal,moncash'],
        ]);

        $amount = number_format((float) $validated['amount'], 2, '.', '');

        $donation = Donation::create([
            'amount'       => $amount,
            'currency'     => self::CURRENCY,
            'donor_name'   => $validated['donor_name'] ?? null,
            'donor_email'  => $validated['donor_email'] ?? null,
            'message'      => $validated['message'] ?? null,
            'is_anonymous' => (bool) ($validated['is_anonymous'] ?? false),
            'status'       => Donation::STATUS_PENDING,
            'provider'     => $validated['provider'] ?? 'paypal',
            'ip_hash'      => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
        ]);

        // MonCash : on renvoie l'adresse de règlement, la confirmation
        // arrivera par webhook.
        if (($validated['provider'] ?? 'paypal') === 'moncash') {
            $result = MonCashCheckout::open(
                $donation,
                (float) $amount,
                self::CURRENCY,
                $donation->reference,
                'Soutien à Lambi News',
                'paypal_order_id'
            );

            if (! $result['ok']) {
                $donation->update(['status' => Donation::STATUS_FAILED]);

                return response()->json(['message' => $result['message']], 502);
            }

            return response()->json([
                'provider'     => 'moncash',
                'checkout_url' => $result['checkout_url'],
            ]);
        }

        try {
            $order = PayPalClient::fromConfig()->createOrder(
                $amount,
                self::CURRENCY,
                $donation->reference,
                'Soutien à Lambi News'
            );
        } catch (Throwable $e) {
            $donation->update(['status' => Donation::STATUS_FAILED]);
            Log::error('PayPal createOrder: '.$e->getMessage());

            return response()->json([
                'message' => 'Impossible de joindre PayPal. Veuillez réessayer.',
            ], 502);
        }

        $donation->update(['paypal_order_id' => $order['id']]);

        return response()->json([
            'orderID'   => $order['id'],
            'reference' => $donation->reference,
        ]);
    }

    /**
     * Capture le paiement une fois le donateur passé par PayPal.
     */
    public function capture(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'orderID' => ['required', 'string', 'max:64'],
        ]);

        $donation = Donation::where('paypal_order_id', $validated['orderID'])->firstOrFail();

        // Si la capture a déjà réussi, on ne rejoue pas le paiement.
        if ($donation->status === Donation::STATUS_COMPLETED) {
            return response()->json([
                'status'      => 'completed',
                'redirect_to' => route('donations.thanks', $donation->reference),
            ]);
        }

        try {
            $payload = PayPalClient::fromConfig()->captureOrder($validated['orderID']);
        } catch (Throwable $e) {
            $donation->update(['status' => Donation::STATUS_FAILED]);
            Log::error('PayPal captureOrder: '.$e->getMessage());

            return response()->json([
                'message' => 'Le paiement n’a pas abouti. Aucun montant n’a été prélevé.',
            ], 502);
        }

        $capture = data_get($payload, 'purchase_units.0.payments.captures.0', []);

        /*
         * On recoupe le montant réellement capturé avec celui enregistré.
         * Un écart signale une commande falsifiée : on encaisse quand même
         * (l’argent est chez PayPal) mais on journalise pour vérification.
         */
        $capturedAmount = (float) data_get($capture, 'amount.value', 0);

        if (abs($capturedAmount - (float) $donation->amount) > 0.01) {
            Log::warning('Don '.$donation->reference.': montant capturé '
                .$capturedAmount.' ≠ montant attendu '.$donation->amount);
        }

        $isCompleted = data_get($payload, 'status') === 'COMPLETED';

        $donation->update([
            'status'            => $isCompleted ? Donation::STATUS_COMPLETED : Donation::STATUS_FAILED,
            'paypal_capture_id' => data_get($capture, 'id'),
            'payer_email'       => data_get($payload, 'payer.email_address'),
            'amount'            => $capturedAmount > 0 ? $capturedAmount : $donation->amount,
            'payload'           => $payload,
            'paid_at'           => $isCompleted ? now() : null,
        ]);

        if (! $isCompleted) {
            return response()->json([
                'message' => 'PayPal n’a pas confirmé le paiement.',
            ], 422);
        }

        return response()->json([
            'status'      => 'completed',
            'redirect_to' => route('donations.thanks', $donation->reference),
        ]);
    }

    /** Le donateur a fermé la fenêtre PayPal sans payer. */
    public function cancel(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'orderID' => ['required', 'string', 'max:64'],
        ]);

        Donation::where('paypal_order_id', $validated['orderID'])
            ->where('status', Donation::STATUS_PENDING)
            ->update(['status' => Donation::STATUS_CANCELLED]);

        return response()->json(['status' => 'cancelled']);
    }

    public function thanks(string $reference): View
    {
        $donation = Donation::where('reference', $reference)
            ->completed()
            ->firstOrFail();

        return view('front.donations.thanks', compact('donation'));
    }
}
