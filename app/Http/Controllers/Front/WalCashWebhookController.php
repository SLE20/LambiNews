<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\FundraiserContribution;
use App\Models\PollVote;
use App\Services\PollVoteRecorder;
use App\Services\WalCashClient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Réception des webhooks WalCash Pay.
 *
 * C'est ici — et nulle part ailleurs — qu'un paiement MonCash est
 * considéré comme acquis. Le retour du navigateur ne prouve rien : il
 * peut être falsifié, ou le payeur peut fermer l'onglet avant.
 */
class WalCashWebhookController extends Controller
{
    public function __invoke(Request $request, PollVoteRecorder $recorder): Response
    {
        $raw       = $request->getContent();
        $signature = $request->header('WalCashPay-Signature');

        if (! WalCashClient::verifySignature($raw, $signature)) {
            Log::warning('WalCash webhook: signature invalide.');

            return response('bad signature', 400);
        }

        $event = json_decode($raw, true) ?: [];
        $type  = (string) data_get($event, 'type');

        // Seuls les paiements confirmés nous intéressent.
        if ($type !== 'payment.succeeded') {
            return response('ignored', 200);
        }

        $paymentId = (string) data_get($event, 'data.payment.id');

        if ($paymentId === '') {
            return response('no payment id', 200);
        }

        /*
         * Le même identifiant peut désigner une contribution ou un vote
         * payant : on cherche dans les deux, et chaque confirmation est
         * idempotente, car WalCash rejoue les livraisons échouées.
         */
        $contribution = FundraiserContribution::where('provider_order_id', $paymentId)->first();

        if ($contribution) {
            FundraiserController::confirm($contribution, $paymentId);

            return response('ok', 200);
        }

        $vote = PollVote::with(['poll', 'option'])
            ->where('paypal_order_id', $paymentId)
            ->first();

        if ($vote && $vote->payment_status !== PollVote::PAY_PAID) {
            $recorder->confirmPaidVote($vote);
        }

        return response('ok', 200);
    }
}
