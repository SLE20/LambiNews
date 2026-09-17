<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\MonCashCheckout;
use App\Services\PayPalClient;
use App\Services\WalCashClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

/**
 * Annonces payantes : avis de décès, remerciements, félicitations,
 * annonces commerciales.
 *
 * Le parcours est : formulaire → paiement PayPal → relecture par la
 * rédaction → publication. Rien n’apparaît sur le site sans être payé
 * ET relu.
 */
class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $type = (string) $request->query('type', '');

        $announcements = Announcement::query()
            ->visible()
            ->when(
                array_key_exists($type, Announcement::catalogue()),
                fn ($query) => $query->where('type', $type)
            )
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        return view('front.announcements.index', [
            'announcements' => $announcements,
            'catalogue'     => Announcement::catalogue(),
            'activeType'    => $type,
        ]);
    }

    public function show(string $slug): View
    {
        $announcement = Announcement::query()
            ->visible()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('front.announcements.show', compact('announcement'));
    }

    public function create(): View
    {
        return view('front.announcements.create', [
            'catalogue'      => Announcement::catalogue(),
            'paypalClientId' => PayPalClient::publicClientId(),
            'paypalReady'    => PayPalClient::fromConfig()->isConfigured(),
            'moncashReady'   => WalCashClient::fromConfig()->isConfigured(),
            'htgRate'        => WalCashClient::htgRate(),
            'displayDays'    => Announcement::DISPLAY_DAYS,
        ]);
    }

    /**
     * Enregistre l’annonce et ouvre la commande PayPal.
     *
     * Le tarif vient du catalogue, côté serveur : le navigateur choisit
     * une catégorie, jamais un prix.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'            => ['required', 'string', 'in:'.implode(',', array_keys(Announcement::catalogue()))],
            'title'           => ['required', 'string', 'max:150'],
            'body'            => ['required', 'string', 'min:20', 'max:4000'],
            'requester_name'  => ['required', 'string', 'max:120'],
            'requester_email' => ['required', 'email', 'max:190'],
            'requester_phone' => ['nullable', 'string', 'max:40'],
            'provider'        => ['nullable', 'in:paypal,moncash'],
            'location'        => ['nullable', 'string', 'max:120'],
            'public_contact'  => ['nullable', 'string', 'max:120'],
        ]);

        $price = Announcement::priceFor($validated['type']);

        if ($price === null) {
            return response()->json(['message' => 'Cette catégorie n’existe pas.'], 422);
        }

        $announcement = Announcement::create([
            ...$validated,
            'amount'   => $price,
            'currency' => 'USD',
            'status'   => Announcement::STATUS_PENDING,
            'provider' => $validated['provider'] ?? 'paypal',
            'ip_hash'  => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
        ]);

        if (($validated['provider'] ?? 'paypal') === 'moncash') {
            $result = MonCashCheckout::open(
                $announcement,
                (float) $price,
                'USD',
                $announcement->reference,
                'Lambi News — '.$announcement->getTypeLabel(),
                'paypal_order_id'
            );

            if (! $result['ok']) {
                return response()->json(['message' => $result['message']], 502);
            }

            return response()->json([
                'provider'     => 'moncash',
                'checkout_url' => $result['checkout_url'],
            ]);
        }

        try {
            $order = PayPalClient::fromConfig()->createOrder(
                number_format($price, 2, '.', ''),
                'USD',
                $announcement->reference,
                'Lambi News — '.$announcement->getTypeLabel()
            );
        } catch (Throwable $e) {
            Log::error('PayPal announcement createOrder: '.$e->getMessage());

            return response()->json([
                'message' => 'Impossible de joindre PayPal. Veuillez réessayer.',
            ], 502);
        }

        $announcement->update(['paypal_order_id' => $order['id']]);

        return response()->json([
            'orderID'   => $order['id'],
            'reference' => $announcement->reference,
        ]);
    }

    public function capture(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'orderID' => ['required', 'string', 'max:64'],
        ]);

        $announcement = Announcement::where('paypal_order_id', $validated['orderID'])->firstOrFail();

        if ($announcement->status !== Announcement::STATUS_PENDING) {
            return response()->json([
                'redirect_to' => route('announcements.thanks', $announcement->reference),
            ]);
        }

        try {
            $payload = PayPalClient::fromConfig()->captureOrder($validated['orderID']);
        } catch (Throwable $e) {
            Log::error('PayPal announcement captureOrder: '.$e->getMessage());

            return response()->json([
                'message' => 'Le paiement n’a pas abouti. Aucun montant n’a été prélevé.',
            ], 502);
        }

        if (data_get($payload, 'status') !== 'COMPLETED') {
            return response()->json(['message' => 'PayPal n’a pas confirmé le paiement.'], 422);
        }

        $announcement->update([
            'status'            => Announcement::STATUS_PAID,
            'paypal_capture_id' => data_get($payload, 'purchase_units.0.payments.captures.0.id'),
            'paid_at'           => now(),
        ]);

        return response()->json([
            'redirect_to' => route('announcements.thanks', $announcement->reference),
        ]);
    }

    public function thanks(string $reference): View
    {
        $announcement = Announcement::where('reference', $reference)
            ->whereIn('status', [
                Announcement::STATUS_PAID,
                Announcement::STATUS_PUBLISHED,
            ])
            ->firstOrFail();

        return view('front.announcements.thanks', compact('announcement'));
    }
}
