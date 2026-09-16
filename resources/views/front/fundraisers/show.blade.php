@extends('front.layouts.app')

@section('title', $fundraiser->title.' — Kanpay — Lambi News')
@section('meta_description', \Illuminate\Support\Str::limit($fundraiser->summary ?: $fundraiser->title, 160))

@push('styles')
    @include('front.fundraisers._styles')
@endpush

@section('content')
<section class="fr">
    <div class="fr__wrap">

        @if(session('fundraiser_status'))
            <p class="fr__info">{{ session('fundraiser_status') }}</p>
        @endif

        <div class="fr__detail">

            <article>
                @if($fundraiser->cover_image)
                    <div class="fr__hero">
                        <img src="{{ asset('storage/'.$fundraiser->cover_image) }}"
                             alt="{{ $fundraiser->title }}">
                    </div>
                @endif

                <span class="fr__badge {{ $fundraiser->isOpen() ? '' : 'fr__badge--closed' }}">
                    {{ $fundraiser->isOpen() ? 'An kou' : 'Fèmen' }}
                </span>

                <h1 class="fr__title" style="text-align:left;margin-top:12px">
                    {{ $fundraiser->title }}
                </h1>

                @if($fundraiser->beneficiary)
                    <p class="fr__sub" style="margin-bottom:18px">
                        Benefisyè : <strong>{{ $fundraiser->beneficiary }}</strong>
                    </p>
                @endif

                @if($fundraiser->story)
                    <div class="fr__story">{{ $fundraiser->story }}</div>
                @endif
            </article>

            <aside>
                {{-- ---------- Progression ---------- --}}
                <div class="fr__panel">
                    <p class="fr__big">{{ $fundraiser->formatted((float) $fundraiser->raised_amount) }}</p>
                    <p class="fr__sub">
                        ranmase sou yon objektif
                        {{ $fundraiser->formatted((float) $fundraiser->goal_amount) }}
                    </p>

                    <div class="fr__bar" style="margin-top:14px">
                        <span class="fr__fill" style="width: {{ $fundraiser->percent() }}%"></span>
                    </div>

                    <div class="fr__stats">
                        <span><b>{{ number_format($fundraiser->rawPercent(), 0) }} %</b> atenn</span>
                        <span><b>{{ $fundraiser->contributions_count }}</b> kontribisyon</span>
                        @if($fundraiser->daysLeft() !== null)
                            <span><b>{{ $fundraiser->daysLeft() }}</b> jou</span>
                        @endif
                    </div>
                </div>

                {{-- ---------- Contribuer ---------- --}}
                @if($fundraiser->isOpen())
                    <div class="fr__panel">
                        <h2 style="margin:0 0 14px;font-size:1.1rem">Bay yon kontribisyon</h2>

                        @if(! $paypalReady && ! $moncashReady)
                            <p class="fr__err">
                                Sistèm peman an poko konfigire. Tanpri retounen pita.
                            </p>
                        @else
                            <form id="fr-form" novalidate>
                                <label class="fr__label">
                                    Montan ({{ $fundraiser->currency }})
                                </label>

                                <div class="fr__chips">
                                    @foreach($presets as $preset)
                                        <button type="button" class="fr__chip{{ $loop->index === 1 ? ' is-active' : '' }}"
                                                data-amount="{{ $preset }}">{{ $preset }}</button>
                                    @endforeach
                                </div>

                                <input type="number" id="fr-amount" name="amount"
                                       min="{{ $minAmount }}" max="{{ $maxAmount }}" step="0.01"
                                       value="{{ $presets[1] }}" required>

                                <div class="fr__methods">
                                    @if($moncashReady)
                                        <label class="fr__method">
                                            <input type="radio" name="provider" value="moncash" checked>
                                            <span>
                                                <strong>MonCash</strong>
                                                <small>
                                                    Peye ak telefòn ou an Ayiti
                                                    @if($fundraiser->currency !== 'HTG')
                                                        — faktire an goud
                                                        (<span id="fr-htg">—</span>)
                                                    @endif
                                                </small>
                                            </span>
                                        </label>
                                    @endif

                                    @if($paypalReady)
                                        <label class="fr__method">
                                            <input type="radio" name="provider" value="paypal"
                                                   {{ $moncashReady ? '' : 'checked' }}>
                                            <span>
                                                <strong>PayPal / kat bankè</strong>
                                                <small>Pou dyaspora a</small>
                                            </span>
                                        </label>
                                    @endif
                                </div>

                                <label class="fr__label">
                                    Non ou <span class="fr__opt">(opsyonèl)</span>
                                    <input type="text" name="donor_name" maxlength="120">
                                </label>

                                <label class="fr__label" style="margin-top:12px">
                                    Imèl <span class="fr__opt">(pou resi a)</span>
                                    <input type="email" name="donor_email" maxlength="190">
                                </label>

                                <label class="fr__label" style="margin-top:12px">
                                    Telefòn <span class="fr__opt">(pou MonCash)</span>
                                    <input type="tel" name="donor_phone" maxlength="40">
                                </label>

                                <label class="fr__label" style="margin-top:12px">
                                    Yon mesaj <span class="fr__opt">(opsyonèl)</span>
                                    <textarea name="message" rows="2" maxlength="500"></textarea>
                                </label>

                                <label style="display:flex;gap:9px;align-items:center;margin:12px 0;font-size:.88rem">
                                    <input type="checkbox" name="is_anonymous" value="1"
                                           style="width:17px;height:17px;accent-color:var(--primary)">
                                    Kenbe kontribisyon mwen an anonim
                                </label>

                                <p class="fr__err" id="fr-err" hidden></p>

                                <button type="submit" class="fr__submit" id="fr-submit">
                                    Kontribye
                                </button>

                                {{-- Les boutons PayPal ne se rendent qu'au besoin. --}}
                                <div id="fr-paypal" style="margin-top:14px" hidden></div>
                            </form>

                            <p class="fr__sub" style="margin-top:12px;text-align:center">
                                Peman an fèt sou paj sekirize founisè a. Lambi News
                                pa janm wè nimewo kat ou.
                            </p>
                        @endif
                    </div>
                @endif

                {{-- ---------- Derniers soutiens ---------- --}}
                @if($contributors->isNotEmpty())
                    <div class="fr__panel">
                        <h2 style="margin:0 0 14px;font-size:1rem">Dènye sipò yo</h2>

                        <div class="fr__donors">
                            @foreach($contributors as $contributor)
                                <div class="fr__donor">
                                    <span>
                                        {{ $contributor->publicName() }}
                                        @if($contributor->message)
                                            <small style="display:block;color:var(--muted)">
                                                « {{ \Illuminate\Support\Str::limit($contributor->message, 60) }} »
                                            </small>
                                        @endif
                                    </span>
                                    <b>{{ $contributor->getFormattedAmount() }}</b>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
</section>
@endsection

@push('scripts')
@if($fundraiser->isOpen() && ($paypalReady || $moncashReady))
@if($paypalReady)
<script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency={{ $fundraiser->currency }}&intent=capture&locale=fr_FR"
        data-namespace="paypalSdk"></script>
@endif
<script>
(function () {
    var form   = document.getElementById('fr-form');
    var amount = document.getElementById('fr-amount');
    var errEl  = document.getElementById('fr-err');
    var submit = document.getElementById('fr-submit');
    var ppBox  = document.getElementById('fr-paypal');
    var token  = '{{ csrf_token() }}';
    var ppRendered = false;
    var htgEl  = document.getElementById('fr-htg');
    var htgRate = {{ \App\Services\WalCashClient::htgRate() }};

    // MonCash n'encaisse qu'en gourdes : on annonce le montant réel.
    function refreshHtg() {
        if (!htgEl) { return; }
        var value = parseFloat(amount.value || '0') * htgRate;
        htgEl.textContent = value.toLocaleString('fr-FR', {
            minimumFractionDigits: 0, maximumFractionDigits: 0
        }) + ' HTG';
    }

    form.querySelectorAll('.fr__chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            form.querySelectorAll('.fr__chip').forEach(function (c) { c.classList.remove('is-active'); });
            chip.classList.add('is-active');
            amount.value = chip.dataset.amount;
            refreshHtg();
        });
    });

    amount.addEventListener('input', function () {
        form.querySelectorAll('.fr__chip').forEach(function (c) { c.classList.remove('is-active'); });
        refreshHtg();
    });

    refreshHtg();

    function showErr(m) { errEl.textContent = m; errEl.hidden = false; }
    function hideErr() { errEl.hidden = true; errEl.textContent = ''; }

    function payload() {
        var d = new FormData(form);
        return {
            amount: amount.value,
            provider: d.get('provider'),
            donor_name: d.get('donor_name'),
            donor_email: d.get('donor_email'),
            donor_phone: d.get('donor_phone'),
            message: d.get('message'),
            is_anonymous: d.get('is_anonymous') ? 1 : 0
        };
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
                    var first = data.errors ? data.errors[Object.keys(data.errors)[0]][0] : null;
                    throw new Error(first || data.message || 'Yon erè rive.');
                }
                return data;
            });
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        hideErr();

        var body = payload();

        // MonCash : le serveur crée le paiement et renvoie l'adresse de règlement.
        if (body.provider === 'moncash') {
            submit.disabled = true;
            submit.textContent = 'Ap prepare peman an…';

            post('{{ route('fundraisers.contribute', $fundraiser->slug) }}', body)
                .then(function (res) { window.location.href = res.checkout_url; })
                .catch(function (err) {
                    showErr(err.message);
                    submit.disabled = false;
                    submit.textContent = 'Kontribye';
                });

            return;
        }

        // PayPal : on affiche ses boutons, qui pilotent la suite.
        if (!window.paypalSdk) { showErr('Nou pa rive chaje PayPal.'); return; }

        submit.hidden = true;
        ppBox.hidden = false;

        if (ppRendered) { return; }
        ppRendered = true;

        window.paypalSdk.Buttons({
            style: { layout: 'vertical', color: 'gold', shape: 'pill', label: 'paypal' },

            createOrder: function () {
                hideErr();
                return post('{{ route('fundraisers.contribute', $fundraiser->slug) }}', payload())
                    .then(function (r) { return r.orderID; })
                    .catch(function (e) { showErr(e.message); throw e; });
            },

            onApprove: function (data) {
                return post('{{ route('fundraisers.capture', $fundraiser->slug) }}', { orderID: data.orderID })
                    .then(function (r) { window.location.href = r.redirect_to; })
                    .catch(function (e) { showErr(e.message); });
            },

            onError: function () { showErr('PayPal rankontre yon pwoblèm.'); }
        }).render('#fr-paypal');
    });
})();
</script>
@endif
@endpush
