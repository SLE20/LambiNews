{{--
    Choix du moyen de paiement, partagé par les dons, les annonces et les
    votes payants.

    $moncashReady / $paypalReady : moyens réellement configurés
    $amount   : montant en devise du site, pour annoncer l'équivalent HTG
    $currency : devise affichée
--}}
@php
    $rate = $htgRate ?? \App\Services\WalCashClient::htgRate();
@endphp

<div class="paymethods">
    @if($moncashReady ?? false)
        <label class="paymethod">
            <input type="radio" name="provider" value="moncash" checked>

            <img src="{{ asset('images/moncash.png') }}" alt="" width="34" height="34">

            <span>
                <strong>MonCash</strong>
                <small>
                    Payez avec votre téléphone en Haïti
                    @if(($currency ?? 'USD') !== 'HTG')
                        — <span data-htg data-rate="{{ $rate }}">—</span>
                    @endif
                </small>
            </span>
        </label>
    @endif

    @if($paypalReady ?? false)
        <label class="paymethod">
            <input type="radio" name="provider" value="paypal"
                   {{ ($moncashReady ?? false) ? '' : 'checked' }}>

            <span class="paymethod__pp" aria-hidden="true">PP</span>

            <span>
                <strong>PayPal / carte bancaire</strong>
                <small>Visa, Mastercard — pour la diaspora</small>
            </span>
        </label>
    @endif
</div>

<style>
    .paymethods { display: grid; gap: 10px; margin: 14px 0; }
    .paymethod {
        display: flex; align-items: center; gap: 12px;
        padding: 11px 14px; cursor: pointer;
        border: 1.5px solid var(--border); border-radius: 10px;
        background: var(--background);
    }
    .paymethod:has(input:checked) {
        border-color: var(--primary);
        background: #fdf8e9;
    }
    .paymethod input { accent-color: var(--primary); flex: none; }
    .paymethod img { border-radius: 7px; flex: none; }
    .paymethod__pp {
        width: 34px; height: 34px; border-radius: 7px; flex: none;
        display: grid; place-items: center;
        background: #003087; color: #fff; font-weight: 800; font-size: .78rem;
    }
    .paymethod small { display: block; color: var(--muted); font-size: .78rem; }
</style>
