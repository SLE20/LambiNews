<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Ouvre un règlement MonCash pour n'importe quel encaissement du site :
 * don, annonce payante, vote payant, contribution à une campagne.
 *
 * Toutes ces tables partagent les mêmes colonnes (`provider`,
 * `checkout_url`, un identifiant de commande), si bien qu'un seul
 * passage suffit au lieu d'un par fonctionnalité.
 */
class MonCashCheckout
{
    /**
     * @param  Model   $payable    L'objet à encaisser.
     * @param  string  $orderField Colonne où ranger l'identifiant WalCash.
     *
     * @return array{ok: bool, checkout_url?: string, message?: string}
     */
    public static function open(
        Model $payable,
        float $amount,
        string $currency,
        string $reference,
        string $description,
        string $orderField = 'provider_order_id',
        array $metadata = []
    ): array {
        $client = WalCashClient::fromConfig();

        if (! $client->isConfigured()) {
            return [
                'ok'      => false,
                'message' => 'MonCash poko konfigire. Chwazi yon lòt mwayen.',
            ];
        }

        try {
            $payment = $client->createPayment(
                $amount,
                $currency,
                $reference,
                $description,
                'moncash',
                $metadata + ['reference' => $reference]
            );
        } catch (Throwable $e) {
            Log::error('MonCash checkout ('.$payable::class.' '.$payable->getKey().'): '.$e->getMessage());

            return [
                'ok'      => false,
                'message' => 'Nou pa rive kontakte MonCash. Tanpri eseye ankò.',
            ];
        }

        $payable->forceFill([
            'provider'    => 'moncash',
            $orderField   => $payment['id'],
            'checkout_url' => $payment['checkout_url'],
        ])->save();

        return ['ok' => true, 'checkout_url' => $payment['checkout_url']];
    }

    /**
     * Montant réellement débité, en gourdes — ce que le payeur verra sur
     * son téléphone. Affiché avant le paiement pour éviter la surprise.
     */
    public static function htgPreview(float $amount, string $currency): string
    {
        $converted = WalCashClient::toHtg($amount, $currency);

        return number_format($converted['amount'], 0, ',', ' ').' HTG';
    }
}
