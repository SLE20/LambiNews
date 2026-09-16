@extends('front.layouts.app')

@section('title', $poll->question.' — Lambi News')
@section('meta_description', \Illuminate\Support\Str::limit($poll->subtitle ?: $poll->description ?: $poll->question, 160))

@php
    $ranked   = $poll->ranked();
    $total    = $poll->validVotes();
    $daysLeft = $poll->daysLeft();
    $hasVoted  = $recorder->alreadyVoted($poll);
    $chosenIds = $recorder->votedOptionIds($poll);
    $chosenId  = $recorder->votedOptionId($poll);
    $closed    = $poll->isClosed();
    $canVote   = ! $hasVoted && ! $closed;
    $remaining = $recorder->remainingVotes($poll);
    $isPaid    = $poll->isPaid();

    /*
     * Camembert : on convertit chaque part en segment de cercle.
     * circumference = 2πr ; chaque segment reçoit sa longueur en
     * stroke-dasharray et est décalé par stroke-dashoffset cumulé.
     * Tracé en SVG pur, sans bibliothèque de graphiques à charger.
     */
    $radius = 70;
    $circumference = 2 * M_PI * $radius;
    $offset = 0;
    $segments = [];

    foreach ($ranked as $row) {
        $length = ($row['percent'] / 100) * $circumference;

        $segments[] = [
            'color'  => $row['color'],
            'length' => $length,
            'offset' => -$offset,
            'label'  => $row['option']->label,
            'pct'    => $row['percent'],
        ];

        $offset += $length;
    }
@endphp

@push('styles')
    @include('front.polls._styles')
<style>
    /*
     * Pleine largeur : le bandeau et la grille doivent déborder du
     * conteneur de l'article, sinon les cartes tombent à trois par ligne
     * au lieu de quatre.
     */
    .sx {
        background: #f1f4f9;
        width: 100vw;
        margin-left: calc(50% - 50vw);
        margin-right: calc(50% - 50vw);
        margin-top: -24px;
    }

    /* ---------------- Bandeau ---------------- */
    .sx__hero {
        position: relative;
        padding: 58px 24px 66px;
        background:
            linear-gradient(105deg, rgba(10,26,64,.96) 0%, rgba(10,26,64,.78) 52%, rgba(10,26,64,.35) 100%),
            var(--hero-image, linear-gradient(120deg, #0a1a40, #16337a));
        background-size: cover;
        background-position: center;
        color: #fff;
        overflow: hidden;
    }
    .sx__hero-inner { max-width: 1180px; margin: 0 auto; position: relative; z-index: 2; }
    .sx__eyebrow {
        margin: 0 0 10px;
        font-size: .76rem;
        letter-spacing: .34em;
        text-transform: uppercase;
        color: rgba(255,255,255,.72);
    }
    .sx__title {
        margin: 0;
        font-family: "Playfair Display", Georgia, serif;
        font-size: clamp(2rem, 5.4vw, 3.4rem);
        font-weight: 800;
        line-height: 1.05;
        text-transform: uppercase;
        letter-spacing: -.01em;
    }
    .sx__title em { font-style: normal; color: #ef4444; }
    .sx__rule { width: 66px; height: 4px; background: #ef4444; margin: 18px 0 20px; }
    .sx__q { margin: 0 0 8px; font-size: clamp(1rem, 2.2vw, 1.3rem); font-weight: 700; }
    .sx__sub { margin: 0; color: rgba(255,255,255,.75); font-size: .95rem; }
    .sx__rules { margin: 16px 0 0; display: flex; flex-wrap: wrap; gap: 8px; }
    .sx__pill {
        display: inline-block; padding: 5px 13px; border-radius: 999px;
        background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.22);
        font-size: .78rem; font-weight: 600;
    }
    .sx__pill--soft { background: transparent; color: rgba(255,255,255,.7); }
    .sx__vote--paid { background: #b8860b; }
    .sx__vote--paid:hover { background: #9a7009; }
    .sx__paybox {
        margin-top: 16px; padding: 18px; border-radius: 12px;
        background: #fff; border: 1px solid #e3e8f0;
    }
    .sx__paybox h3 { margin: 0 0 6px; font-size: 1rem; }
    .sx__paybox p { margin: 0 0 14px; font-size: .86rem; color: #64748b; }
    .sx__payerr {
        margin: 0 0 12px; padding: 10px 14px; border-radius: 8px;
        background: #fdecea; border: 1px solid #f5c2bd; color: #8d2419;
        font-size: .86rem;
    }

    /* ---------------- Corps ---------------- */
    .sx__body {
        max-width: 1180px;
        margin: 0 auto;
        padding: 32px 16px 64px;
        display: grid;
        grid-template-columns: minmax(0, 1fr) 340px;
        gap: 26px;
        align-items: start;
    }
    .sx__h2 {
        display: flex; align-items: center; gap: 10px;
        margin: 0 0 18px;
        font-size: 1.15rem; font-weight: 800;
        letter-spacing: .06em; text-transform: uppercase;
        color: #0a1a40;
    }

    /* ---------------- Cartes candidats ---------------- */
    .sx__grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(178px, 1fr));
        gap: 16px;
    }
    .sx__card {
        background: #fff;
        border: 1px solid #e3e8f0;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .sx__card.is-mine { border-color: #1d4ed8; box-shadow: 0 0 0 2px rgba(29,78,216,.22); }
    .sx__photo {
        aspect-ratio: 1 / 1;
        background: var(--card-color, #1d4ed8);
        display: grid; place-items: center;
        overflow: hidden;
    }
    .sx__photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .sx__photo span { font-size: 2.6rem; font-weight: 800; color: rgba(255,255,255,.85); }
    /*
     * Colonne flexible : le bouton est poussé en bas, si bien que tous
     * les boutons s'alignent même quand un nom tient sur deux lignes.
     */
    .sx__card-body {
        padding: 14px;
        text-align: center;
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    .sx__name {
        margin: 0 0 8px;
        font-size: .98rem;
        font-weight: 800;
        color: #0f172a;
        min-height: 2.5em;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .sx__party {
        display: inline-flex; align-items: center; justify-content: center; gap: 7px;
        margin-bottom: 12px; font-size: .82rem; color: #64748b;
    }
    .sx__party i {
        width: 18px; height: 18px; border-radius: 50%;
        background: var(--card-color, #1d4ed8); display: inline-block;
        background-size: cover; background-position: center;
    }
    .sx__vote {
        display: block; width: 100%; margin-top: auto;
        padding: 11px 10px; border: 0; border-radius: 8px;
        background: #1d4ed8; color: #fff;
        font: inherit; font-weight: 700; font-size: .88rem;
        cursor: pointer; letter-spacing: .04em;
    }
    .sx__vote:hover { background: #1a43bd; }
    .sx__vote[disabled] { background: #cbd5e1; color: #64748b; cursor: default; }
    .sx__votepct {
        display: block; margin-top: auto; padding: 11px 10px; border-radius: 8px;
        background: #f1f5f9; color: #0f172a;
        font-weight: 800; font-size: .95rem;
    }

    /* ---------------- Panneaux de droite ---------------- */
    .sx__panel {
        background: #fff; border: 1px solid #e3e8f0;
        border-radius: 12px; padding: 20px; margin-bottom: 20px;
    }
    .sx__live {
        display: flex; align-items: center; gap: 10px; margin-bottom: 16px;
    }
    .sx__badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 10px; border-radius: 6px;
        background: #ef4444; color: #fff;
        font-size: .66rem; font-weight: 800; letter-spacing: .1em;
    }
    .sx__badge::before {
        content: ""; width: 6px; height: 6px; border-radius: 50%;
        background: #fff; animation: sxpulse 1.6s infinite;
    }
    @keyframes sxpulse { 0%,100% { opacity: 1 } 50% { opacity: .25 } }
    .sx__livelabel {
        font-size: .72rem; font-weight: 800; letter-spacing: .1em;
        text-transform: uppercase; color: #64748b;
    }
    .sx__stat {
        display: flex; align-items: center; gap: 12px;
        padding: 11px 0; border-top: 1px solid #eef2f7;
    }
    .sx__stat:first-of-type { border-top: 0; }
    .sx__ico {
        width: 34px; height: 34px; border-radius: 8px; flex: none;
        background: #eef3fd; display: grid; place-items: center; font-size: 1rem;
    }
    .sx__stat small {
        display: block; color: #64748b; font-size: .76rem; margin-bottom: 1px;
    }
    .sx__stat strong { font-size: 1.05rem; color: #0f172a; }

    .sx__res { display: grid; gap: 13px; }
    .sx__resrow { font-size: .84rem; }
    .sx__resline {
        display: grid; grid-template-columns: 1fr auto auto;
        gap: 10px; align-items: baseline; margin-bottom: 5px;
    }
    .sx__resname { color: #0f172a; font-weight: 600; }
    .sx__respct { font-weight: 800; }
    .sx__resvotes { color: #94a3b8; font-size: .76rem; }
    .sx__bar { height: 6px; border-radius: 999px; background: #eef2f7; overflow: hidden; }
    .sx__barfill { display: block; height: 100%; border-radius: 999px; }

    .sx__donut { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; }
    .sx__donut svg { flex: none; transform: rotate(-90deg); }
    .sx__legend { display: grid; gap: 6px; font-size: .78rem; flex: 1; min-width: 130px; }
    .sx__legend div { display: grid; grid-template-columns: 11px 1fr auto; gap: 8px; align-items: center; }
    .sx__dot { width: 11px; height: 11px; border-radius: 50%; }
    .sx__center { font-weight: 800; fill: #0f172a; }
    .sx__centersub { fill: #94a3b8; font-size: 11px; }

    .sx__cta {
        grid-column: 1 / -1;
        margin-top: 6px;
        padding: 28px 30px;
        border-radius: 12px;
        background: linear-gradient(100deg, #0a1a40, #16337a);
        color: #fff;
    }
    .sx__cta h2 {
        margin: 0 0 8px;
        font-family: "Playfair Display", Georgia, serif;
        font-size: 1.5rem;
    }
    .sx__cta p { margin: 0; color: rgba(255,255,255,.76); font-size: .93rem; }

    .sx__note {
        grid-column: 1 / -1;
        margin: 4px 0 0; font-size: .78rem; color: #64748b; line-height: 1.6;
    }
    .sx__flash {
        grid-column: 1 / -1;
        margin: 0 0 4px; padding: 12px 16px; border-radius: 10px;
        background: #eef7ee; border: 1px solid #cfe6cf; color: #245b28;
        font-size: .9rem;
    }

    @media (max-width: 900px) {
        .sx__body { grid-template-columns: 1fr; }
    }

    /*
     * Téléphone : deux cartes par ligne. Sans cette règle, le minmax de
     * 178px n'en laisse passer qu'une seule sur un écran de 360 px, et la
     * page devient une colonne interminable.
     */
    @media (max-width: 620px) {
        /*
         * On abandonne le débordement pleine largeur : sur téléphone il
         * n'apporte rien, et 100vw dépasse la zone visible dès qu'un
         * autre élément de la page élargit le document.
         */
        .sx {
            width: auto;
            margin-left: 0;
            margin-right: 0;
        }

        .sx__grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }
        .sx__card-body { padding: 11px 9px; }
        .sx__name { font-size: .88rem; min-height: 2.6em; }
        .sx__party { font-size: .74rem; margin-bottom: 10px; }
        .sx__party i { width: 14px; height: 14px; }
        .sx__vote, .sx__votepct { padding: 9px 6px; font-size: .8rem; }
        .sx__hero { padding: 40px 18px 46px; }
        .sx__body { padding: 24px 12px 48px; gap: 20px; }
        .sx__panel { padding: 16px; }
        .sx__donut { justify-content: center; }
    }

    /* Très petits écrans : on garde deux colonnes, en plus compact. */
    @media (max-width: 380px) {
        .sx__grid { gap: 9px; }
        .sx__name { font-size: .82rem; }
        .sx__vote, .sx__votepct { font-size: .74rem; padding: 8px 4px; }
    }
</style>
@endpush

@section('content')
<div class="sx">

    <header
        class="sx__hero"
        @if($poll->hero_image)
            style="--hero-image: url('{{ asset('storage/'.$poll->hero_image) }}')"
        @endif
    >
        <div class="sx__hero-inner">
            <p class="sx__eyebrow">{{ $poll->eyebrow ?: 'Votre avis compte' }}</p>

            <h1 class="sx__title">{!! $poll->headlineHtml() !!}</h1>

            <div class="sx__rule"></div>

            <p class="sx__q">{{ $poll->question }}</p>

            @if($poll->subtitle)
                <p class="sx__sub">{{ $poll->subtitle }}</p>
            @endif

            {{-- Les règles sont annoncées avant le vote, pas découvertes après. --}}
            <p class="sx__rules">
                @if($isPaid)
                    <span class="sx__pill">{{ $poll->formattedPrice() }} pa vòt</span>
                @endif

                <span class="sx__pill">
                    @if($poll->allowsMultipleVotes())
                        {{ $poll->voteQuota() }} vòt {{ $poll->identityLabel() }}
                    @else
                        Yon sèl vòt {{ $poll->identityLabel() }}
                    @endif
                </span>

                @if($canVote && $poll->allowsMultipleVotes())
                    <span class="sx__pill sx__pill--soft">Rete {{ $remaining }} vòt pou ou</span>
                @endif
            </p>
        </div>
    </header>

    <div class="sx__body">

        @if(session('poll_status'))
            <p class="sx__flash">{{ session('poll_status') }}</p>
        @endif

        {{-- ---------------- Candidats ---------------- --}}
        <section>
            <h2 class="sx__h2">👥 Kandida yo</h2>

            <form method="POST" action="{{ route('polls.vote', $poll->slug) }}">
                @csrf
                <input type="hidden" name="opened_at" value="{{ time() }}">

                {{-- Leurre à robots. --}}
                <div class="poll__trap" aria-hidden="true">
                    <label>Ne pas remplir
                        <input type="text" name="website" tabindex="-1" autocomplete="off">
                    </label>
                </div>

                <div class="sx__grid">
                    @foreach($ranked as $row)
                        @php($option = $row['option'])
                        <article
                            class="sx__card{{ in_array($option->id, $chosenIds, true) ? ' is-mine' : '' }}"
                            style="--card-color: {{ $row['color'] }}"
                        >
                            <div class="sx__photo">
                                @if($option->image)
                                    <img
                                        src="{{ asset('storage/'.$option->image) }}"
                                        alt="{{ $option->label }}"
                                        loading="lazy"
                                    >
                                @else
                                    <span>{{ \Illuminate\Support\Str::of($option->label)->substr(0, 2)->upper() }}</span>
                                @endif
                            </div>

                            <div class="sx__card-body">
                                <h3 class="sx__name">{{ $option->label }}</h3>

                                @if($option->subtitle)
                                    <span class="sx__party">
                                        <i @if($option->party_logo) style="background-image:url('{{ asset('storage/'.$option->party_logo) }}')" @endif></i>
                                        {{ $option->subtitle }}
                                    </span>
                                @endif

                                @if($canVote && $isPaid)
                                    <button
                                        type="button"
                                        class="sx__vote sx__vote--paid"
                                        data-option="{{ $option->id }}"
                                        data-name="{{ $option->label }}"
                                    >🗳 VOTE · {{ $poll->formattedPrice() }}</button>
                                @elseif($canVote)
                                    <button
                                        type="submit"
                                        name="poll_option_id"
                                        value="{{ $option->id }}"
                                        class="sx__vote"
                                    >🗳 VOTE</button>
                                @else
                                    <span class="sx__votepct">
                                        {{ number_format($row['percent'], 0, ',', ' ') }} %
                                        @if(in_array($option->id, $chosenIds, true)) ✓ @endif
                                    </span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </form>

            @if($canVote && $isPaid)
                <div class="sx__paybox" id="sx-paybox" hidden>
                    <h3>Peye vòt ou an : <span id="sx-payfor"></span></h3>
                    <p>
                        {{ $poll->formattedPrice() }} pa vòt. Vòt ou an konte
                        sèlman apre peman an konfime.
                    </p>
                    @include('front.partials.payment-methods', [
                        'currency' => $poll->currency ?: 'USD',
                    ])

                    <p class="sx__payerr" id="sx-payerr" hidden></p>

                    <div id="sx-paypal"></div>

                    <button type="button" class="sx__vote" id="sx-moncash" hidden
                            style="margin-top:8px">Kontinye ak MonCash</button>
                </div>

                @if(! $paypalReady && ! $moncashReady)
                    <p class="sx__payerr" style="margin-top:16px">
                        Sistèm peman an poko konfigire : sondaj peyan an pa ka
                        resevwa vòt pou kounye a.
                    </p>
                @endif
            @endif
        </section>

        {{-- ---------------- Colonne de droite ---------------- --}}
        <aside>
            <div class="sx__panel">
                <div class="sx__live">
                    <span class="sx__badge">LIVE</span>
                    <span class="sx__livelabel">
                        {{ $closed ? 'Sondaj fèmen' : 'Sondaj an kou' }}
                    </span>
                </div>

                <div class="sx__stat">
                    <span class="sx__ico">👤</span>
                    <span>
                        <small>Total patisipan</small>
                        <strong>{{ number_format($total, 0, ',', ' ') }}</strong>
                    </span>
                </div>

                @if($poll->ends_at)
                    <div class="sx__stat">
                        <span class="sx__ico">📅</span>
                        <span>
                            <small>Dat limit</small>
                            <strong>{{ $poll->ends_at->translatedFormat('d F Y') }}</strong>
                        </span>
                    </div>

                    <div class="sx__stat">
                        <span class="sx__ico">⏱</span>
                        <span>
                            <small>Rete sèlman</small>
                            <strong>{{ $daysLeft }} jou</strong>
                        </span>
                    </div>
                @endif
            </div>

            <div class="sx__panel">
                <h2 class="sx__h2" style="font-size:.95rem;margin-bottom:14px">📊 Rezilta sondaj la</h2>

                @if($total < 1)
                    <p style="color:#64748b;font-size:.86rem;margin:0">
                        Poko gen vòt. Rezilta yo parèt depi premye patisipan an.
                    </p>
                @else
                    <div class="sx__res">
                        @foreach($ranked as $row)
                            <div class="sx__resrow">
                                <div class="sx__resline">
                                    <span class="sx__resname">{{ $row['option']->label }}</span>
                                    <span class="sx__respct">{{ number_format($row['percent'], 0, ',', ' ') }}%</span>
                                    <span class="sx__resvotes">
                                        {{ number_format($row['option']->votes_count, 0, ',', ' ') }} vòt
                                    </span>
                                </div>
                                <div class="sx__bar">
                                    <span
                                        class="sx__barfill"
                                        style="width: {{ $row['percent'] }}%; background: {{ $row['color'] }}"
                                    ></span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Camembert en SVG pur, calculé côté serveur. --}}
                    <div class="sx__donut" style="margin-top:22px">
                        <svg width="170" height="170" viewBox="0 0 170 170" role="img"
                             aria-label="Répartition des voix">
                            <circle cx="85" cy="85" r="{{ $radius }}" fill="none"
                                    stroke="#eef2f7" stroke-width="22"></circle>

                            @foreach($segments as $seg)
                                <circle
                                    cx="85" cy="85" r="{{ $radius }}"
                                    fill="none"
                                    stroke="{{ $seg['color'] }}"
                                    stroke-width="22"
                                    stroke-dasharray="{{ round($seg['length'], 2) }} {{ round($circumference - $seg['length'], 2) }}"
                                    stroke-dashoffset="{{ round($seg['offset'], 2) }}"
                                >
                                    <title>{{ $seg['label'] }} — {{ $seg['pct'] }} %</title>
                                </circle>
                            @endforeach

                            <g transform="rotate(90 85 85)">
                                <text x="85" y="82" text-anchor="middle" class="sx__center"
                                      font-size="19">100%</text>
                                <text x="85" y="99" text-anchor="middle" class="sx__centersub">
                                    {{ number_format($total, 0, ',', ' ') }} vòt
                                </text>
                            </g>
                        </svg>

                        <div class="sx__legend">
                            @foreach($ranked as $row)
                                <div>
                                    <span class="sx__dot" style="background: {{ $row['color'] }}"></span>
                                    <span>{{ \Illuminate\Support\Str::limit($row['option']->label, 16) }}</span>
                                    <strong>{{ number_format($row['percent'], 0, ',', ' ') }}%</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </aside>

        <div class="sx__cta">
            <h2>Ansanm pou yon pi bon demen</h2>
            <p>
                Vòt ou se vwa ou. Patisipe nan sondaj la epi ede nou konprann
                preferans sitwayen ayisyen yo.
            </p>
        </div>

        <p class="sx__note">
            Sondaj enfòmèl, rezève pou lektè Lambi News. Se pa yon ankèt
            syantifik : rezilta yo pa reprezante tout popilasyon an. Yon sèl
            vòt pa moun.
        </p>

    </div>
</div>
@endsection

@push('scripts')
@if($canVote && $isPaid && $moncashReady)
<script>
(function () {
    var box = document.getElementById('sx-paybox');
    var mc  = document.getElementById('sx-moncash');
    var pp  = document.getElementById('sx-paypal');
    var err = document.getElementById('sx-payerr');
    var chosen = null;

    if (!box || !mc) { return; }

    function sync() {
        var checked = document.querySelector('input[name=provider]:checked');
        var isMonCash = checked && checked.value === 'moncash';
        mc.hidden = !isMonCash;
        if (pp) { pp.hidden = isMonCash; }
    }

    document.querySelectorAll('.sx__vote--paid').forEach(function (btn) {
        btn.addEventListener('click', function () {
            chosen = btn.dataset.option;
            sync();
        });
    });

    document.querySelectorAll('input[name=provider]').forEach(function (r) {
        r.addEventListener('change', sync);
    });

    mc.addEventListener('click', function () {
        if (!chosen) { return; }
        mc.disabled = true;
        mc.textContent = 'Ap prepare peman an…';

        fetch(@json(route('polls.pay.start', $poll->slug)), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': @json(csrf_token())
            },
            body: JSON.stringify({
                poll_option_id: chosen,
                provider: 'moncash',
                opened_at: {{ time() }}
            })
        }).then(function (r) {
            return r.json().then(function (d) {
                if (!r.ok) { throw new Error(d.message || 'Yon erè rive.'); }
                return d;
            });
        }).then(function (d) {
            window.location.href = d.checkout_url;
        }).catch(function (e) {
            err.textContent = e.message; err.hidden = false;
            mc.disabled = false; mc.textContent = 'Kontinye ak MonCash';
        });
    });

    sync();
})();
</script>
@endif

@if($canVote && $isPaid && $paypalReady)
<script
    src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency={{ $poll->currency ?: 'USD' }}&intent=capture&locale=fr_FR"
    data-namespace="paypalSdk"
></script>
<script>
(function () {
    var box     = document.getElementById('sx-paybox');
    var forEl   = document.getElementById('sx-payfor');
    var errEl   = document.getElementById('sx-payerr');
    var token   = '{{ csrf_token() }}';
    var chosen  = null;
    var rendered = false;

    function showErr(m) { errEl.textContent = m; errEl.hidden = false; }
    function hideErr() { errEl.hidden = true; errEl.textContent = ''; }

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
            return r.json().then(function (d) {
                if (!r.ok) { throw new Error(d.message || 'Yon erè rive.'); }
                return d;
            });
        });
    }

    // Choisir un candidat ouvre le paiement ; on ne rend les boutons
    // PayPal qu'une fois, puis on change simplement le candidat visé.
    document.querySelectorAll('.sx__vote--paid').forEach(function (btn) {
        btn.addEventListener('click', function () {
            hideErr();
            chosen = btn.dataset.option;
            forEl.textContent = btn.dataset.name;
            box.hidden = false;
            box.scrollIntoView({ behavior: 'smooth', block: 'center' });

            if (rendered || !window.paypalSdk) { return; }
            rendered = true;

            window.paypalSdk.Buttons({
                style: { layout: 'vertical', color: 'gold', shape: 'pill', label: 'pay' },

                createOrder: function () {
                    hideErr();
                    return post('{{ route('polls.pay.start', $poll->slug) }}', {
                        poll_option_id: chosen,
                        opened_at: {{ time() }}
                    }).then(function (r) { return r.orderID; })
                      .catch(function (e) { showErr(e.message); throw e; });
                },

                onApprove: function (data) {
                    return post('{{ route('polls.pay.capture', $poll->slug) }}', { orderID: data.orderID })
                        .then(function (r) { window.location.href = r.redirect_to; })
                        .catch(function (e) { showErr(e.message); });
                },

                onError: function () {
                    showErr('PayPal rankontre yon pwoblèm. Tanpri eseye ankò.');
                }
            }).render('#sx-paypal');
        });
    });
})();
</script>
@endif
@endpush
