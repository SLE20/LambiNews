<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Fundraiser;
use App\Models\FundraiserContribution;
use App\Services\PayPalClient;
use App\Services\WalCashClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

/**
 * Campagnes de financement participatif.
 *
 * Deux moyens de paiement cohabitent : MonCash pour le public haïtien,
 * PayPal pour la diaspora. Dans les deux cas la contribution n'est
 * comptée qu'une fois le paiement confirmé côté serveur.
 */
class FundraiserController extends Controller
{
    private const MIN_AMOUNT = 1;

    private const MAX_AMOUNT = 10000;

    public function index(): View
    {
        $fundraisers = Fundraiser::query()
            ->live()
            ->orderByDesc('is_featured')
            ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(9);

        return view('front.fundraisers.index', compact('fundraisers'));
    }

    public function show(string $slug): View
    {
        $fundraiser = Fundraiser::query()
            ->live()
            ->where('slug', $slug)
            ->firstOrFail();

        $contributors = $fundraiser->completedContributions()
            ->latest('paid_at')
            ->limit(12)
            ->get();

        return view('front.fundraisers.show', [
            'fundraiser'     => $fundraiser,
            'contributors'   => $contributors,
            'paypalClientId' => PayPalClient::publicClientId(),
            'paypalReady'    => PayPalClient::fromConfig()->isConfigured(),
            'moncashReady'   => WalCashClient::fromConfig()->isConfigured(),
            'minAmount'      => self::MIN_AMOUNT,
            'maxAmount'      => self::MAX_AMOUNT,
            'presets'        => [10, 25, 50, 100],
        ]);
    }

    /**
     * Enregistre l'intention de don, puis ouvre la commande chez le
     * prestataire choisi. Rien n'est crédité à ce stade.
     */
    public function contribute(Request $request, string $slug): JsonResponse
    {
        $fundraiser = Fundraiser::query()->live()->where('slug', $slug)->firstOrFail();

        if (! $fundraiser->isOpen()) {
            return response()->json(['message' => 'Cette campagne est clôturée.'], 422);
        }

        $validated = $request->validate([
            'amount'       => ['required', 'numeric', 'min:'.self::MIN_AMOUNT, 'max:'.self::MAX_AMOUNT],
            'provider'     => ['required', 'in:paypal,moncash'],
            'donor_name'   => ['nullable', 'string', 'max:120'],
            'donor_email'  => ['nullable', 'email', 'max:190'],
            'donor_phone'  => ['nullable', 'string', 'max:40'],
            'message'      => ['nullable', 'string', 'max:500'],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);

        $amount = round((float) $validated['amount'], 2);

        $contribution = FundraiserContribution::create([
            'fundraiser_id' => $fundraiser->id,
            'amount'        => $amount,
            'currency'      => $fundraiser->currency,
            'donor_name'    => $validated['donor_name'] ?? null,
            'donor_email'   => $validated['donor_email'] ?? null,
            'donor_phone'   => $validated['donor_phone'] ?? null,
            'message'       => $validated['message'] ?? null,
            'is_anonymous'  => (bool) ($validated['is_anonymous'] ?? false),
            'provider'      => $validated['provider'],
            'status'        => FundraiserContribution::STATUS_PENDING,
            'ip_hash'       => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
        ]);

        return $validated['provider'] === FundraiserContribution::PROVIDER_MONCASH
            ? $this->startMonCash($fundraiser, $contribution)
            : $this->startPayPal($fundraiser, $contribution);
    }

    /** MonCash : on renvoie l'adresse de la page de paiement WalCash. */
    private function startMonCash(Fundraiser $fundraiser, FundraiserContribution $c): JsonResponse
    {
        try {
            $payment = WalCashClient::fromConfig()->createPayment(
                (float) $c->amount,
                $fundraiser->currency,
                $c->reference,
                'Lambi News — '.$fundraiser->title,
                'moncash',
                ['contribution' => $c->reference, 'fundraiser' => $fundraiser->slug]
            );
        } catch (Throwable $e) {
            Log::error('WalCash createPayment: '.$e->getMessage());
            $c->update(['status' => FundraiserContribution::STATUS_FAILED]);

            return response()->json([
                'message' => 'Impossible de joindre MonCash. Veuillez réessayer.',
            ], 502);
        }

        $c->update([
            'provider_order_id' => $payment['id'],
            'checkout_url'      => $payment['checkout_url'],
        ]);

        return response()->json([
            'provider'     => 'moncash',
            'checkout_url' => $payment['checkout_url'],
        ]);
    }

    private function startPayPal(Fundraiser $fundraiser, FundraiserContribution $c): JsonResponse
    {
        try {
            $order = PayPalClient::fromConfig()->createOrder(
                number_format((float) $c->amount, 2, '.', ''),
                $fundraiser->currency,
                $c->reference,
                'Lambi News — '.$fundraiser->title
            );
        } catch (Throwable $e) {
            Log::error('PayPal fundraiser createOrder: '.$e->getMessage());
            $c->update(['status' => FundraiserContribution::STATUS_FAILED]);

            return response()->json([
                'message' => 'Impossible de joindre PayPal. Veuillez réessayer.',
            ], 502);
        }

        $c->update(['provider_order_id' => $order['id']]);

        return response()->json(['provider' => 'paypal', 'orderID' => $order['id']]);
    }

    /** Capture PayPal, déclenchée par le navigateur après approbation. */
    public function capture(Request $request, string $slug): JsonResponse
    {
        $validated = $request->validate([
            'orderID' => ['required', 'string', 'max:64'],
        ]);

        $c = FundraiserContribution::query()
            ->with('fundraiser')
            ->where('provider_order_id', $validated['orderID'])
            ->firstOrFail();

        if ($c->status === FundraiserContribution::STATUS_COMPLETED) {
            return response()->json(['redirect_to' => route('fundraisers.thanks', $c->reference)]);
        }

        try {
            $payload = PayPalClient::fromConfig()->captureOrder($validated['orderID']);
        } catch (Throwable $e) {
            Log::error('PayPal fundraiser capture: '.$e->getMessage());

            return response()->json([
                'message' => 'Le paiement n’a pas abouti. Aucun montant n’a été prélevé.',
            ], 502);
        }

        if (data_get($payload, 'status') !== 'COMPLETED') {
            return response()->json(['message' => 'PayPal n’a pas confirmé le paiement.'], 422);
        }

        self::confirm($c, (string) data_get($payload, 'purchase_units.0.payments.captures.0.id'));

        return response()->json(['redirect_to' => route('fundraisers.thanks', $c->reference)]);
    }

    /** Le donateur revient de MonCash ; la confirmation vient du webhook. */
    public function moncashReturn(string $reference): RedirectResponse
    {
        $c = FundraiserContribution::where('reference', $reference)->firstOrFail();

        return $c->status === FundraiserContribution::STATUS_COMPLETED
            ? redirect()->route('fundraisers.thanks', $c->reference)
            : redirect()
                ->route('fundraisers.show', $c->fundraiser->slug)
                ->with('fundraiser_status',
                    'Nous attendons la confirmation de MonCash. Votre référence : '.$c->reference);
    }

    public function thanks(string $reference): View
    {
        $c = FundraiserContribution::query()
            ->with('fundraiser')
            ->where('reference', $reference)
            ->where('status', FundraiserContribution::STATUS_COMPLETED)
            ->firstOrFail();

        return view('front.fundraisers.thanks', ['contribution' => $c]);
    }

    /**
     * Valide une contribution et met à jour le total de la campagne.
     *
     * Idempotent : appelé aussi bien par la capture PayPal que par le
     * webhook MonCash, qui peut être rejoué par le prestataire.
     */
    public static function confirm(FundraiserContribution $c, string $captureId = ''): void
    {
        if ($c->status === FundraiserContribution::STATUS_COMPLETED) {
            return;
        }

        DB::transaction(function () use ($c, $captureId): void {
            $c->forceFill([
                'status'              => FundraiserContribution::STATUS_COMPLETED,
                'provider_capture_id' => $captureId ?: $c->provider_capture_id,
                'paid_at'             => now(),
            ])->save();

            // Query builder brut : ne pas toucher updated_at de la campagne.
            DB::table('fundraisers')
                ->where('id', $c->fundraiser_id)
                ->update([
                    'raised_amount' => DB::raw('raised_amount + '.(float) $c->amount),
                    'contributions_count' => DB::raw('contributions_count + 1'),
                ]);
        });
    }
}
