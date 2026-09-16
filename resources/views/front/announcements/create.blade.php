@extends('front.layouts.app')

@section('title', 'Publier une annonce — Lambi News')

@section(
    'meta_description',
    'Publiez un avis de décès, des remerciements, des félicitations ou '
    .'une annonce commerciale dans Lambi News.'
)

@push('styles')
    @include('front.announcements._styles')
@endpush

@section('content')
<section class="anons">
    <div class="anons__wrap" style="max-width:720px">

        <header class="anons__head">
            <p class="anons__eyebrow">Annonces</p>
            <h1 class="anons__title">Publier une annonce</h1>
            <p class="anons__lead">
                Choisissez une catégorie, rédigez votre texte et payez en
                ligne. Votre annonce paraît {{ $displayDays }} jours après
                relecture par la rédaction.
            </p>
        </header>

        @unless($paypalReady)
            <p class="anons__alert">
                Sistèm peman an poko konfigire. Tanpri retounen pita.
            </p>
        @else

        <form id="anons-form" class="anons__form" novalidate>

            <label class="anons__label">
                Catégorie
                <select name="type" id="anons-type" required>
                    @foreach($catalogue as $key => $row)
                        <option
                            value="{{ $key }}"
                            data-price="{{ number_format($row['price'], 2, '.', '') }}"
                            data-intro="{{ $row['intro'] }}"
                        >{{ $row['label'] }} — ${{ number_format($row['price'], 2) }}</option>
                    @endforeach
                </select>
            </label>

            <p id="anons-intro" style="margin:-8px 0 0; color:var(--muted); font-size:.9rem"></p>

            <label class="anons__label">
                Titre de l’annonce
                <input type="text" name="title" maxlength="150" required>
            </label>

            <label class="anons__label">
                Texte de l’annonce
                <textarea
                    name="body"
                    rows="10"
                    minlength="20"
                    maxlength="4000"
                    required
                ></textarea>
                <span class="anons__opt">
                    Entre 20 et 4000 caractères. La rédaction peut corriger
                    l’orthographe.
                </span>
            </label>

            <div class="anons__grid">
                <label class="anons__label">
                    Votre nom
                    <input type="text" name="requester_name" maxlength="120" required>
                </label>

                <label class="anons__label">
                    Votre courriel
                    <input type="email" name="requester_email" maxlength="190" required>
                </label>
            </div>

            <label class="anons__label">
                Téléphone <span class="anons__opt">(facultatif)</span>
                <input type="tel" name="requester_phone" maxlength="40">
            </label>

            <div class="anons__price">
                <span>Montant à payer</span>
                <strong id="anons-price">$0.00</strong>
            </div>

            <p class="anons__error" id="anons-error" hidden></p>

            <div id="paypal-buttons"></div>

            <p class="anons__secure">
                Vos coordonnées ne sont jamais publiées : elles servent
                uniquement à vous joindre au sujet de cette annonce.
            </p>
        </form>

        @endunless
    </div>
</section>
@endsection

@push('scripts')
@if($paypalReady)
<script
    src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency=USD&intent=capture&locale=fr_FR"
    data-namespace="paypalSdk"
></script>
<script>
(function () {
    var form    = document.getElementById('anons-form');
    var typeSel = document.getElementById('anons-type');
    var priceEl = document.getElementById('anons-price');
    var introEl = document.getElementById('anons-intro');
    var errorEl = document.getElementById('anons-error');
    var token   = '{{ csrf_token() }}';

    function refresh() {
        var opt = typeSel.options[typeSel.selectedIndex];
        priceEl.textContent = '$' + parseFloat(opt.dataset.price).toFixed(2);
        introEl.textContent = opt.dataset.intro || '';
    }

    typeSel.addEventListener('change', refresh);
    refresh();

    function hideError() { errorEl.hidden = true; errorEl.textContent = ''; }

    function showError(msg) {
        errorEl.textContent = msg;
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
        }).then(function (r) {
            return r.json().then(function (data) {
                if (!r.ok) {
                    // Laravel renvoie les erreurs champ par champ.
                    var first = data.errors
                        ? data.errors[Object.keys(data.errors)[0]][0]
                        : null;
                    throw new Error(first || data.message || 'Yon erè rive.');
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
            var d = new FormData(form);

            return post('{{ route('announcements.store') }}', {
                type: d.get('type'),
                title: d.get('title'),
                body: d.get('body'),
                requester_name: d.get('requester_name'),
                requester_email: d.get('requester_email'),
                requester_phone: d.get('requester_phone')
            }).then(function (res) {
                return res.orderID;
            }).catch(function (err) {
                showError(err.message);
                throw err;
            });
        },

        onApprove: function (data) {
            return post('{{ route('announcements.capture') }}', { orderID: data.orderID })
                .then(function (res) { window.location.href = res.redirect_to; })
                .catch(function (err) { showError(err.message); });
        },

        onError: function () {
            showError('PayPal rankontre yon pwoblèm. Tanpri eseye ankò.');
        }
    }).render('#paypal-buttons');
})();
</script>
@endif
@endpush
