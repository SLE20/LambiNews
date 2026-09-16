@extends('front.layouts.app')

@section('title', 'Sipòte Lambi News')

@section(
    'meta_description',
    'Ede Lambi News kontinye bay enfòmasyon endepandan. '
    .'Yon don an sekirite ak PayPal oswa kat bankè.'
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
            <p class="don__eyebrow">Sipòte jounalis endepandan</p>
            <h1 class="don__title">Ede nou kontinye enfòme</h1>
            <p class="don__lead">
                Lambi News se yon medya endepandan. Chak don ede nou kouvri
                depans yo: ekipman, entènèt, ebèjman, epi travay jounalis yo
                sou teren an.
            </p>

            @if($donorCount > 0)
                <p class="don__counter">
                    <strong>{{ number_format($totalRaised, 2) }} USD</strong>
                    ranmase gras a
                    <strong>{{ $donorCount }}</strong>
                    {{ $donorCount > 1 ? 'donatè' : 'donatè' }}
                </p>
            @endif
        </header>

        @unless($paypalReady)
            <p class="don__alert">
                Sistèm peman an poko konfigire. Tanpri retounen pita.
            </p>
        @else

        <form id="don-form" class="don__card" novalidate>
            <fieldset class="don__field">
                <legend class="don__label">Chwazi yon montan (USD)</legend>

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
                    <span>Lòt montan</span>
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
                    Non ou <span class="don__opt">(opsyonèl)</span>
                    <input type="text" name="donor_name" maxlength="120">
                </label>

                <label class="don__label">
                    Imèl <span class="don__opt">(pou resi a)</span>
                    <input type="email" name="donor_email" maxlength="190">
                </label>
            </div>

            <label class="don__label">
                Yon mesaj <span class="don__opt">(opsyonèl)</span>
                <textarea name="message" rows="3" maxlength="500"></textarea>
            </label>

            <label class="don__check">
                <input type="checkbox" name="is_anonymous" value="1">
                <span>Kenbe don mwen an anonim</span>
            </label>

            <p class="don__error" id="don-error" hidden></p>

            <div id="paypal-buttons" class="don__paypal"></div>

            <p class="don__secure">
                Peman an fèt sou sèvè PayPal. Lambi News pa janm wè ni estoke
                nimewo kat ou.
            </p>
        </form>

        @endunless
    </div>
</section>
@endsection

@push('scripts')
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
                    throw new Error(data.message || 'Yon erè rive.');
                }
                return data;
            });
        });
    }

    if (!window.paypalSdk) {
        showError('Nou pa rive chaje PayPal. Verifye koneksyon ou.');
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
            showError('PayPal rankontre yon pwoblèm. Tanpri eseye ankò.');
        }
    }).render('#paypal-buttons');
})();
</script>
@endif
@endpush
