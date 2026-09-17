{{--
    Bandeau « revenus » de l'accueil de l'administration : le total du
    mois, sa tendance, et la part de chaque source. Le détail est sur
    la page Revenus.
--}}
@php
    $usd = fn ($v) => '$'.number_format((float) $v, 2, ',', ' ');
    $g = $revenue['growth'];
    $sparkline = collect($revenue['daily']['series'])
        ->reduce(function ($carry, $values) {
            foreach ($values as $i => $v) { $carry[$i] = ($carry[$i] ?? 0) + $v; }
            return $carry;
        }, []);
@endphp

<a href="{{ backpack_url('revenus') }}" class="lnr">
    <div class="lnr__main">
        <span class="lnr__kicker"><i class="la la-coins"></i> Revenus · 30 derniers jours</span>
        <strong class="lnr__total">{{ $usd($revenue['total']) }}</strong>
        <span class="lnr__growth">
            @if($g === null)
                premiers revenus de la période
            @else
                <b class="{{ $g >= 0 ? 'is-up' : 'is-down' }}">{{ $g >= 0 ? '▲' : '▼' }} {{ number_format(abs($g), 1, ',', ' ') }} %</b>
                vs les 30 jours précédents
            @endif
        </span>
        <div class="lnr__spark"><canvas id="lnrSpark" height="46"></canvas></div>
    </div>

    <div class="lnr__streams">
        @foreach($revenue['streams'] as $s)
            <div class="lnr__stream" style="--c: {{ $s['color'] }}">
                <span><i class="la {{ $s['icon'] }}"></i> {{ $s['label'] }}</span>
                <strong>{{ $usd($s['usd']) }}</strong>
                <small>{{ $s['count'] }} paiement(s){{ $s['pending'] ? ' · '.$s['pending'].' en attente' : '' }}</small>
            </div>
        @endforeach
    </div>

    <span class="lnr__cta">Voir tous les revenus <i class="la la-arrow-right"></i></span>
</a>

@push('after_styles')
<style>
    .lnr {
        display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 2fr); gap: 1.2rem;
        margin-bottom: 1.5rem; padding: 1.3rem 1.4rem; border-radius: 16px;
        background: #fff; border: 1px solid #e5e1d8; box-shadow: 0 6px 20px rgba(0,0,0,.045);
        color: inherit; text-decoration: none; position: relative;
    }
    .lnr:hover { color: inherit; box-shadow: 0 12px 28px rgba(0,0,0,.08); }
    .lnr__main { display: flex; flex-direction: column; gap: .2rem; padding: 1rem 1.1rem; border-radius: 12px;
        background: linear-gradient(135deg, #d5a51f, #b8860b); color: #171717; }
    .lnr__kicker { font-size: .72rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; opacity: .8; }
    .lnr__total { font-size: 2rem; font-weight: 800; line-height: 1.15; }
    .lnr__growth { font-size: .8rem; }
    .lnr__growth b { padding: .05rem .4rem; border-radius: 999px; background: rgba(255,255,255,.55); }
    .lnr__growth b.is-down { color: #991b1b; }
    .lnr__growth b.is-up { color: #166534; }
    .lnr__spark { margin-top: .4rem; height: 46px; }
    .lnr__streams { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .8rem; align-content: center; }
    .lnr__stream { padding: .8rem .9rem; border-radius: 12px; background: #faf8f3; border-left: 4px solid var(--c); min-width: 0; }
    .lnr__stream span { display: block; font-size: .78rem; font-weight: 700; color: #79746c; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .lnr__stream i { color: var(--c); }
    .lnr__stream strong { display: block; font-size: 1.25rem; margin: .15rem 0; }
    .lnr__stream small { color: #79746c; font-size: .72rem; }
    .lnr__cta { position: absolute; right: 1.4rem; bottom: .55rem; font-size: .78rem; font-weight: 700; color: #9c7207; }
    @media (max-width: 1199px) {
        .lnr { grid-template-columns: minmax(0, 1fr); padding-bottom: 2.2rem; }
        .lnr__streams { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
</style>
@endpush

@push('after_scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('lnrSpark');
    if (!el || typeof Chart === 'undefined') { return; }
    new Chart(el, {
        type: 'line',
        data: {
            labels: {{ Illuminate\Support\Js::from($revenue['daily']['labels']) }},
            datasets: [{ data: {{ Illuminate\Support\Js::from(array_values($sparkline)) }},
                borderColor: '#171717', borderWidth: 2, pointRadius: 0, tension: .35, fill: false }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
            scales: { x: { display: false }, y: { display: false, beginAtZero: true } }
        }
    });
});
</script>
@endpush
