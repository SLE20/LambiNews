@extends('front.layouts.app')

@section('title', 'Soutenir Lambi News')

@section(
    'meta_description',
    'Aidez Lambi News à poursuivre une information indépendante. '
    .'Un don sécurisé par MonCash, PayPal ou carte bancaire.'
)

@push('styles')
<style>
    .don { padding: 48px 16px 72px; }
    .don__wrap { max-width: 720px; margin: 0 auto; }
    .don__head { text-align: center; margin-bottom: 32px; }
    .don__eyebrow {
        text-transform: uppercase; letter-spacing: .14em; font-size: .78rem;
        font-weight: 700; color: var(--primary-dark); margin: 0 0 8px;
    }
    .don__title {
        font-family: "Playfair Display", Georgia, serif;
        font-size: clamp(1.9rem, 5vw, 2.8rem); line-height: 1.15; margin: 0 0 14px;
    }
    .don__lead { color: var(--muted); margin: 0 auto; max-width: 52ch; }
    .don__counter {
        margin-top: 18px; padding: 10px 16px; display: inline-block;
        background: var(--surface); border: 1px solid var(--border);
        border-radius: 999px; font-size: .95rem;
    }
    .don__card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius); box-shadow: var(--shadow);
        padding: 28px; display: grid; gap: 20px;
    }
    .don__field { border: 0; padding: 0; margin: 0; }
    .don__label {
        display: block; font-weight: 600; font-size: .92rem; margin-bottom: 6px;
    }
    .don__opt { font-weight: 400; color: var(--muted); }
    .don__amounts {
        display: grid; grid-template-columns: repeat(4, 1fr);
        gap: 10px; margin: 12px 0;
    }
    .don__chip {
        padding: 14px 6px; border-radius: 12px; cursor: pointer;
        border: 1.5px solid var(--border); background: var(--background);
        font-weight: 700; font-size: 1.05rem; font-family: inherit;
        transition: border-color .15s, background .15s;
    }
    .don__chip:hover { border-color: var(--primary); }
    .don__chip.is-active {
        border-color: var(--primary); background: var(--primary);
        color: var(--black);
    }
    .don__custom span {
        display: block; font-size: .85rem; color: var(--muted); margin-bottom: 6px;
    }
    .don__grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .don input[type="text"], .don input[type="email"],
    .don input[type="number"], .don textarea {
        width: 100%; padding: 12px 14px; border-radius: 10px;
        border: 1.5px solid var(--border); background: var(--background);
        font: inherit; color: inherit;
    }
    .don input:focus, .don textarea:focus {
        outline: none; border-color: var(--primary);
    }
    .don__check {
        display: flex; align-items: center; gap: 10px; font-size: .92rem;
    }
    .don__check input { width: 18px; height: 18px; accent-color: var(--primary); }
    .don__paypal { min-height: 52px; }
    .don__moncash {
        width: 100%; padding: 13px; border: 0; border-radius: 999px;
        background: var(--black); color: #fff; font: inherit; font-weight: 700;
        cursor: pointer;
    }
    .don__moncash:hover { background: var(--black-light); }
    .don__secure {
        font-size: .82rem; color: var(--muted); text-align: center; margin: 0;
    }
    .don__error, .don__alert {
        margin: 0; padding: 12px 16px; border-radius: 10px;
        background: #fdecea; border: 1px solid #f5c2bd; color: #8d2419;
        font-size: .92rem;
    }
    @media (max-width: 560px) {
        .don__grid { grid-template-columns: 1fr; }
        .don__amounts { grid-template-columns: repeat(2, 1fr); }
        .don__card { padding: 20px; }
    }
</style>
@endpush

@section('content')
<section class="don">
    <div class="don__wrap">

        <header class="don__head">
            <p class="don__eyebrow">Soutenez le journalisme indépendant</p>
            <h1 class="don__title">Aidez-nous à continuer d’informer</h1>
            <p class="don__lead">
                Lambi News est un média indépendant. Chaque don nous aide à couvrir
                nos dépenses : matériel, internet, hébergement et travail des
                journalistes sur le terrain.
            </p>

            @if($donorCount > 0)
                <p class="don__counter">
                    <strong>{{ number_format($totalRaised, 2) }} USD</strong>
                    récoltés grâce à
                    <strong>{{ $donorCount }}</strong>
                    {{ $donorCount > 1 ? 'donateurs' : 'donateur' }}
                </p>
            @endif
        </header>

        @if(! $paypalReady && ! $moncashReady)
            <p class="don__alert">
                Le paiement n’est pas encore configuré. Merci de revenir plus tard.
            </p>
        @else

        <form id="don-form" class="don__card" novalidate>
            <fieldset class="don__field">
                <legend class="don__label">Choisissez un montant (USD)</legend>

                <div class="don__amounts">
                    @foreach($presets as $preset)
                        <button
                            type="button"
                            class="don__chip{{ $loop->index === 1 ? ' is-active' : '' }}"
                            data-amount="{{ $preset }}"
                        >${{ $preset }}</button>
                    @endforeach
                </div>

                <label class="don__custom">
                    <span>Autre montant</span>
                    <input
                        type="number"
                        id="don-amount"
                        name="amount"
                        min="{{ $minAmount }}"
                        max="{{ $maxAmount }}"
                        step="0.01"
                        value="{{ $presets[1] }}"
                        required
                    >
                </label>
            </fieldset>

            <div class="don__grid">
                <label class="don__label">
                    Votre nom <span class="don__opt">(facultatif)</span>
                    <input type="text" name="donor_name" maxlength="120">
                </label>

                <label class="don__label">
                    E-mail <span class="don__opt">(pour le reçu)</span>
                    <input type="email" name="donor_email" maxlength="190">
                </label>
            </div>

            <label class="don__label">
                Un message <span class="don__opt">(facultatif)</span>
                <textarea name="message" rows="3" maxlength="500"></textarea>
            </label>

            @include('front.partials.payment-methods', [
                'currency' => $currency,
            ])

            <label class="don__check">
                <input type="checkbox" name="is_anonymous" value="1">
                <span>Garder mon don anonyme</span>
            </label>

            <p class="don__error" id="don-error" hidden></p>

            <div id="paypal-buttons" class="don__paypal"></div>

            <button type="button" class="don__moncash" id="don-moncash" hidden>
                Continuer avec MonCash
            </button>

            <p class="don__secure">
                Le paiement s’effectue sur les serveurs sécurisés du prestataire.
                Lambi News ne voit ni ne conserve jamais votre numéro de carte.
            </p>
        </form>

        @endif
    </div>
</section>
@endsection

@push('scripts')
@include('front.partials.payment-script', ['amountFieldId' => 'don-amount'])

<script>
// Bascule entre les deux moyens : PayPal montre ses boutons, MonCash le sien.
(function () {
    var mc = document.getElementById('don-moncash');
    var pp = document.getElementById('paypal-buttons');
    if (!mc || !pp) { return; }

    function sync() {
        var checked = document.querySelector('input[name=provider]:checked');
        var isMonCash = checked && checked.value === 'moncash';
        mc.hidden = !isMonCash;
        pp.hidden = isMonCash;
    }

    document.querySelectorAll('input[name=provider]').forEach(function (r) {
        r.addEventListener('change', sync);
    });

    mc.addEventListener('click', function () {
        var form = document.getElementById('don-form');
        var data = new FormData(form);
        mc.disabled = true;
        mc.textContent = 'Préparation du paiement…';

        fetch(@json(route('donations.store')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': @json(csrf_token())
            },
            body: JSON.stringify({
                amount: document.getElementById('don-amount').value,
                provider: 'moncash',
                donor_name: data.get('donor_name'),
                donor_email: data.get('donor_email'),
                message: data.get('message'),
                is_anonymous: data.get('is_anonymous') ? 1 : 0
            })
        }).then(function (r) {
            return r.json().then(function (d) {
                if (!r.ok) { throw new Error(d.message || 'Une erreur est survenue.'); }
                return d;
            });
        }).then(function (d) {
            window.location.href = d.checkout_url;
        }).catch(function (e) {
            var err = document.getElementById('don-error');
            err.textContent = e.message;
            err.hidden = false;
            mc.disabled = false;
            mc.textContent = 'Continuer avec MonCash';
        });
    });

    sync();
})();
</script>

@if($paypalReady)
<script
    src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency={{ $currency }}&intent=capture&locale=fr_FR"
    data-namespace="paypalSdk"
></script>
<script>
(function () {
    var form    = document.getElementById('don-form');
    var amount  = document.getElementById('don-amount');
    var errorEl = document.getElementById('don-error');
    var chips   = form.querySelectorAll('.don__chip');
    var token   = '{{ csrf_token() }}';

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            chips.forEach(function (c) { c.classList.remove('is-active'); });
            chip.classList.add('is-active');
            amount.value = chip.dataset.amount;
            hideError();
        });
    });

    // Saisir un montant libre désélectionne les montants proposés.
    amount.addEventListener('input', function () {
        chips.forEach(function (c) { c.classList.remove('is-active'); });
        hideError();
    });

    function hideError() {
        errorEl.hidden = true;
        errorEl.textContent = '';
    }

    function showError(message) {
        errorEl.textContent = message;
        errorEl.hidden = false;
        errorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function post(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify(body)
        }).then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok) {
                    throw new Error(data.message || 'Une erreur est survenue.');
                }
                return data;
            });
        });
    }

    if (!window.paypalSdk) {
        showError('Impossible de charger PayPal. Vérifiez votre connexion.');
        return;
    }

    window.paypalSdk.Buttons({
        style: { layout: 'vertical', color: 'gold', shape: 'pill', label: 'paypal' },

        createOrder: function () {
            hideError();

            var data = new FormData(form);

            return post('{{ route('donations.store') }}', {
                amount: amount.value,
                donor_name: data.get('donor_name'),
                donor_email: data.get('donor_email'),
                message: data.get('message'),
                is_anonymous: data.get('is_anonymous') ? 1 : 0
            }).then(function (res) {
                return res.orderID;
            }).catch(function (err) {
                showError(err.message);
                throw err;
            });
        },

        onApprove: function (data) {
            return post('{{ route('donations.capture') }}', { orderID: data.orderID })
                .then(function (res) {
                    window.location.href = res.redirect_to;
                })
                .catch(function (err) {
                    showError(err.message);
                });
        },

        onCancel: function (data) {
            post('{{ route('donations.cancel') }}', { orderID: data.orderID })
                .catch(function () { /* sans conséquence pour le donateur */ });
        },

        onError: function () {
            showError('PayPal a rencontré un problème. Veuillez réessayer.');
        }
    }).render('#paypal-buttons');
})();
</script>
@endif
@endpush
