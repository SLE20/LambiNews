@extends(backpack_view('blank'))

@php
    $usd = fn ($v) => '$'.number_format((float) $v, 2, ',', ' ');
    $htg = fn ($v) => number_format((float) $v, 0, ',', ' ').' HTG';
    $int = fn ($v) => number_format((int) $v, 0, ',', ' ');
    $money = fn ($v, $cur) => number_format((float) $v, 2, ',', ' ').' '.strtoupper($cur);

    $providerLabels = ['paypal' => 'PayPal', 'moncash' => 'MonCash'];
    $providerColors = ['paypal' => '#003087', 'moncash' => '#e2001a'];

    $streamLinks = [
        'donations'     => backpack_url('donation'),
        'announcements' => backpack_url('announcement'),
        'votes'         => backpack_url('poll'),
        'fundraisers'   => backpack_url('fundraiser-contribution'),
    ];

    $moncashShare = $total > 0 ? round(($providers['moncash']['usd'] ?? 0) / $total * 100) : 0;
    $average = $payments > 0 ? $total / $payments : 0;

    $announcementTypes = \App\Models\Announcement::catalogue();
    $adPositions = \App\Models\Ad::positions();

    $growthBadge = function (?float $g) {
        if ($g === null) {
            return '<span class="rv-growth rv-growth--new">nouveau</span>';
        }
        $cls = $g > 0 ? 'up' : ($g < 0 ? 'down' : 'flat');
        $arrow = $g > 0 ? '▲' : ($g < 0 ? '▼' : '•');
        return '<span class="rv-growth rv-growth--'.$cls.'">'.$arrow.' '.number_format(abs($g), 1, ',', ' ').' %</span>';
    };
@endphp

@section('content')
<div class="container-fluid rv">

    {{-- ================= En-tête et période ================= --}}
    <header class="rv-head">
        <div>
            <span class="rv-kicker">Monétisation</span>
            <h1>Revenus</h1>
            <p>
                Du {{ $start->translatedFormat('d M Y') }} au {{ $end->translatedFormat('d M Y') }}
                · comparé aux {{ $start->diffInDays($end) + 1 }} jours précédents
            </p>
        </div>

        <form method="GET" action="{{ backpack_url('revenus') }}" class="rv-period">
            <div class="rv-presets">
                @foreach($presets as $value => $label)
                    <a href="{{ backpack_url('revenus') }}?days={{ $value }}"
                       class="rv-preset {{ $days === $value ? 'is-active' : '' }}">{{ $label }}</a>
                @endforeach
            </div>
            <div class="rv-range">
                <input type="date" name="start_date" value="{{ $start->toDateString() }}" aria-label="Début">
                <span>→</span>
                <input type="date" name="end_date" value="{{ $end->toDateString() }}" aria-label="Fin">
                <button type="submit" class="btn btn-sm rv-btn">Appliquer</button>
            </div>
        </form>
    </header>

    @if($stale > 0)
        <div class="rv-alert">
            <i class="la la-exclamation-triangle"></i>
            <span>
                <strong>{{ $stale }} paiement(s) lancé(s) il y a plus d’une heure sans confirmation</strong>
                (30 derniers jours). Un taux élevé peut signaler un souci côté PayPal/MonCash ou webhook.
            </span>
        </div>
    @endif

    {{-- ================= Indicateurs clés ================= --}}
    <div class="rv-kpis">
        <div class="rv-kpi rv-kpi--main">
            <span class="rv-kpi__label">Revenu total</span>
            <strong class="rv-kpi__value">{{ $usd($total) }}</strong>
            <span class="rv-kpi__foot">{!! $growthBadge($growth) !!} vs période précédente</span>
        </div>

        <div class="rv-kpi">
            <span class="rv-kpi__label">Paiements confirmés</span>
            <strong class="rv-kpi__value">{{ $int($payments) }}</strong>
            <span class="rv-kpi__foot">panier moyen {{ $usd($average) }}</span>
        </div>

        <div class="rv-kpi">
            <span class="rv-kpi__label">Part MonCash</span>
            <strong class="rv-kpi__value">{{ $moncashShare }} %</strong>
            <span class="rv-kpi__foot">
                {{ $usd($providers['moncash']['usd'] ?? 0) }} MonCash ·
                {{ $usd($providers['paypal']['usd'] ?? 0) }} PayPal
            </span>
        </div>

        <div class="rv-kpi">
            <span class="rv-kpi__label">Encaissé en gourdes</span>
            <strong class="rv-kpi__value">{{ $htg(array_sum(array_column($streams, 'htg'))) }}</strong>
            <span class="rv-kpi__foot">taux appliqué : 1 USD = {{ rtrim(rtrim(number_format($rate, 2, ',', ' '), '0'), ',') }} HTG</span>
        </div>
    </div>

    {{-- ================= Flux de revenus ================= --}}
    <div class="rv-streams">
        @foreach($streams as $key => $s)
            <a href="{{ $streamLinks[$key] }}" class="rv-stream" style="--c: {{ $s['color'] }}">
                <span class="rv-stream__head">
                    <span class="rv-stream__icon"><i class="la {{ $s['icon'] }}"></i></span>
                    <span class="rv-stream__name">{{ $s['label'] }}</span>
                    {!! $growthBadge($s['growth']) !!}
                </span>

                <strong class="rv-stream__value">{{ $usd($s['usd']) }}</strong>

                <span class="rv-stream__share">
                    <span style="width: {{ $total > 0 ? round($s['usd'] / $total * 100) : 0 }}%"></span>
                </span>

                <dl class="rv-stream__facts">
                    <div><dt>Paiements</dt><dd>{{ $int($s['count']) }}</dd></div>
                    <div><dt>Moyenne</dt><dd>{{ $usd($s['average']) }}</dd></div>
                    <div><dt>Réussite</dt><dd>{{ $s['success'] === null ? '—' : number_format($s['success'], 0).' %' }}</dd></div>
                    <div><dt>En attente</dt><dd>{{ $int($s['pending']) }}</dd></div>
                </dl>

                @if($s['htg'] > 0)
                    <span class="rv-stream__note">dont {{ $htg($s['htg']) }} via MonCash</span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- ================= Graphiques ================= --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-8">
            <div class="card rv-card h-100">
                <div class="card-header">
                    <h2 class="card-title mb-0">Revenus par jour</h2>
                    <span class="text-muted small">en dollars, empilés par source</span>
                </div>
                <div class="card-body">
                    @if($total > 0)
                        <div class="rv-chart"><canvas id="rvDaily"></canvas></div>
                    @else
                        <div class="rv-empty"><i class="la la-chart-bar"></i><p>Aucun paiement confirmé sur cette période.</p></div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card rv-card h-100">
                <div class="card-header">
                    <h2 class="card-title mb-0">Moyens de paiement</h2>
                </div>
                <div class="card-body">
                    @if($total > 0)
                        <div class="rv-chart rv-chart--small"><canvas id="rvProviders"></canvas></div>
                        <ul class="rv-legend">
                            @foreach($providers as $name => $p)
                                <li>
                                    <span style="background: {{ $providerColors[$name] ?? '#999' }}"></span>
                                    {{ $providerLabels[$name] ?? ucfirst($name) }}
                                    <b>{{ $usd($p['usd']) }}</b>
                                    <small>{{ $int($p['count']) }} paiements</small>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="rv-empty"><i class="la la-wallet"></i><p>Pas encore de données.</p></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ================= Transactions et campagnes ================= --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-7">
            <div class="card rv-card h-100">
                <div class="card-header">
                    <h2 class="card-title mb-0">Derniers paiements</h2>
                    <span class="text-muted small">toutes sources</span>
                </div>
                <div class="table-responsive">
                    <table class="table rv-table mb-0">
                        <thead>
                            <tr><th>Date</th><th>Source</th><th>Détail</th><th>Moyen</th><th class="text-end">Montant</th></tr>
                        </thead>
                        <tbody>
                            @forelse($recent as $tx)
                                @php($meta = \App\Services\RevenueStats::STREAMS[$tx->stream])
                                <tr>
                                    <td class="text-nowrap">{{ \Carbon\Carbon::parse($tx->paid_at)->format('d/m H:i') }}</td>
                                    <td><span class="rv-tag" style="--c: {{ $meta['color'] }}">{{ $meta['label'] }}</span></td>
                                    <td class="rv-ellipsis">{{ \Illuminate\Support\Str::limit($tx->who, 42) }}</td>
                                    <td><span class="rv-provider rv-provider--{{ $tx->provider ?: 'paypal' }}">{{ $providerLabels[$tx->provider] ?? ucfirst((string) $tx->provider) }}</span></td>
                                    <td class="text-end text-nowrap fw-bold">{{ $money($tx->amount, $tx->currency) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><div class="rv-empty rv-empty--sm"><p>Aucun paiement confirmé pour l’instant.</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card rv-card h-100">
                <div class="card-header">
                    <h2 class="card-title mb-0">Campagnes de financement</h2>
                    <a href="{{ backpack_url('fundraiser') }}" class="rv-link">Gérer</a>
                </div>
                <div class="card-body">
                    @forelse($fundraisers as $f)
                        @php($pct = $f->goal_amount > 0 ? min(100, round($f->raised_amount / $f->goal_amount * 100)) : 0)
                        <div class="rv-goal">
                            <div class="rv-goal__top">
                                <a href="{{ route('fundraisers.show', $f->slug) }}" target="_blank" rel="noopener">{{ $f->title }}</a>
                                <span class="rv-status rv-status--{{ $f->status }}">{{ $f->status === 'active' ? 'En cours' : 'Clôturée' }}</span>
                            </div>
                            <div class="rv-bar"><span style="width: {{ $pct }}%"></span></div>
                            <div class="rv-goal__bottom">
                                <b>{{ $money($f->raised_amount, $f->currency) }}</b>
                                <span>/ {{ $money($f->goal_amount, $f->currency) }} · {{ $pct }} % · {{ $int($f->contributions_count) }} contrib.</span>
                            </div>
                        </div>
                    @empty
                        <div class="rv-empty rv-empty--sm"><p>Aucune campagne publiée.</p></div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ================= Sondages payants et annonces ================= --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card rv-card h-100">
                <div class="card-header">
                    <h2 class="card-title mb-0">Sondages payants</h2>
                    <span class="text-muted small">cumul depuis l’ouverture</span>
                </div>
                <div class="table-responsive">
                    <table class="table rv-table mb-0">
                        <thead><tr><th>Sondage</th><th class="text-end">Prix</th><th class="text-end">Votes payés</th><th class="text-end">Recette</th></tr></thead>
                        <tbody>
                            @forelse($paidPolls as $p)
                                <tr>
                                    <td class="rv-ellipsis">{{ \Illuminate\Support\Str::limit($p->question, 48) }}</td>
                                    <td class="text-end text-nowrap">{{ $money($p->vote_price, $p->currency) }}</td>
                                    <td class="text-end">{{ $int($p->paid_votes) }}</td>
                                    <td class="text-end text-nowrap fw-bold">{{ $money($p->revenue, $p->currency) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><div class="rv-empty rv-empty--sm"><p>Aucun sondage payant.</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card rv-card h-100">
                <div class="card-header">
                    <h2 class="card-title mb-0">Annonces payantes</h2>
                    <a href="{{ backpack_url('announcement') }}" class="rv-link">Modérer</a>
                </div>
                <div class="card-body">
                    <div class="rv-minis">
                        <div class="rv-mini {{ $announcements['to_review'] > 0 ? 'rv-mini--warn' : '' }}">
                            <strong>{{ $int($announcements['to_review']) }}</strong><span>payées, à relire</span>
                        </div>
                        <div class="rv-mini">
                            <strong>{{ $int($announcements['live']) }}</strong><span>en ligne</span>
                        </div>
                    </div>

                    @forelse($announcements['by_type'] as $t)
                        <div class="rv-row">
                            <span>{{ $announcementTypes[$t->type]['label'] ?? $t->type }}</span>
                            <span class="text-muted">{{ $int($t->n) }}</span>
                            <b>{{ $usd($t->usd) }}</b>
                        </div>
                    @empty
                        <div class="rv-empty rv-empty--sm"><p>Aucune annonce payée sur la période.</p></div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ================= Audience monétisable ================= --}}
    <h2 class="rv-section">Audience vendue aux annonceurs</h2>

    <div class="rv-kpis">
        <div class="rv-kpi">
            <span class="rv-kpi__label">Affichages publicitaires</span>
            <strong class="rv-kpi__value">{{ $int($ads['impressions']) }}</strong>
            <span class="rv-kpi__foot">{{ $int($ads['active']) }} publicité(s) active(s)</span>
        </div>
        <div class="rv-kpi">
            <span class="rv-kpi__label">Clics publicitaires</span>
            <strong class="rv-kpi__value">{{ $int($ads['clicks']) }}</strong>
            <span class="rv-kpi__foot">taux de clic {{ number_format($ads['ctr'], 2, ',', ' ') }} %</span>
        </div>
        <div class="rv-kpi">
            <span class="rv-kpi__label">Articles sponsorisés</span>
            <strong class="rv-kpi__value">{{ $int($sponsored['count']) }}</strong>
            <span class="rv-kpi__foot">{{ $int($sponsored['views']) }} lectures sur la période</span>
        </div>
        <div class="rv-kpi">
            <span class="rv-kpi__label">Abonnés infolettre</span>
            <strong class="rv-kpi__value">{{ $int($newsletter['active']) }}</strong>
            <span class="rv-kpi__foot">+{{ $int($newsletter['joined']) }} / −{{ $int($newsletter['left']) }} sur la période</span>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card rv-card h-100">
                <div class="card-header">
                    <h2 class="card-title mb-0">Publicités les plus vues</h2>
                    <a href="{{ backpack_url('ad') }}" class="rv-link">Gérer</a>
                </div>
                <div class="table-responsive">
                    <table class="table rv-table mb-0">
                        <thead><tr><th>Publicité</th><th>Emplacement</th><th class="text-end">Affichages</th><th class="text-end">Clics</th><th class="text-end">CTR</th></tr></thead>
                        <tbody>
                            @forelse($ads['top'] as $ad)
                                <tr>
                                    <td class="rv-ellipsis">
                                        {{ $ad->name }}
                                        @if($ad->client_name)<small class="d-block text-muted">{{ $ad->client_name }}</small>@endif
                                    </td>
                                    <td class="small">{{ $adPositions[$ad->position] ?? $ad->position }}</td>
                                    <td class="text-end">{{ $int($ad->impressions) }}</td>
                                    <td class="text-end">{{ $int($ad->clicks) }}</td>
                                    <td class="text-end">{{ $ad->impressions > 0 ? number_format($ad->clicks / $ad->impressions * 100, 2, ',', ' ') : '0' }} %</td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><div class="rv-empty rv-empty--sm"><p>Aucun affichage enregistré.</p></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card rv-card h-100">
                <div class="card-header">
                    <h2 class="card-title mb-0">Articles sponsorisés</h2>
                    <span class="text-muted small">lectures sur la période</span>
                </div>
                <div class="card-body">
                    @forelse($sponsored['articles'] as $a)
                        <div class="rv-row">
                            <span class="rv-ellipsis">
                                {{ \Illuminate\Support\Str::limit($a->title, 55) }}
                                @if($a->sponsor_name)<small class="d-block text-muted">par {{ $a->sponsor_name }}</small>@endif
                            </span>
                            <b>{{ $int($a->views) }}</b>
                        </div>
                    @empty
                        <div class="rv-empty rv-empty--sm"><p>Aucun article sponsorisé. Cochez « Article sponsorisé » dans un article.</p></div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-md-6">
            <div class="card rv-card h-100">
                <div class="card-header"><h2 class="card-title mb-0">Participation aux sondages</h2></div>
                <div class="card-body">
                    <div class="rv-minis rv-minis--4">
                        <div class="rv-mini"><strong>{{ $int($polls['open']) }}</strong><span>sondages actifs</span></div>
                        <div class="rv-mini"><strong>{{ $int($polls['votes']) }}</strong><span>votes</span></div>
                        <div class="rv-mini"><strong>{{ $int($polls['voters']) }}</strong><span>votants uniques</span></div>
                        <div class="rv-mini"><strong>{{ $int($polls['paid']) }}</strong><span>votes payés</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card rv-card h-100">
                <div class="card-header"><h2 class="card-title mb-0">Infolettre</h2></div>
                <div class="card-body">
                    <div class="rv-minis rv-minis--4">
                        <div class="rv-mini"><strong>{{ $int($newsletter['campaigns']) }}</strong><span>envois</span></div>
                        <div class="rv-mini"><strong>{{ $int($newsletter['sent']) }}</strong><span>e-mails partis</span></div>
                        <div class="rv-mini {{ $newsletter['failed'] > 0 ? 'rv-mini--warn' : '' }}"><strong>{{ $int($newsletter['failed']) }}</strong><span>échecs</span></div>
                        <div class="rv-mini"><strong>{{ $int($newsletter['joined']) }}</strong><span>nouveaux abonnés</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('after_styles')
<style>
    .rv { --gold: #d5a51f; --gold-dark: #9c7207; --dark: #171717; --line: #e5e1d8; --muted: #79746c;
          padding-top: 1rem; padding-bottom: 2.5rem; }

    .rv-head {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1.2rem;
        margin-bottom: 1.4rem; padding: 1.4rem 1.6rem; border-radius: 16px;
        background: linear-gradient(120deg, #171717, #2a261d); color: #fff;
    }
    .rv-kicker { display: block; color: var(--gold); font-size: .72rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; }
    .rv-head h1 { margin: .2rem 0 0; color: #fff; font-size: clamp(1.6rem, 4vw, 2.2rem); }
    .rv-head p { margin: .35rem 0 0; color: rgba(255,255,255,.65); }

    .rv-period { display: grid; gap: .6rem; justify-items: end; }
    .rv-presets { display: flex; flex-wrap: wrap; gap: .35rem; }
    .rv-preset {
        padding: .35rem .8rem; border-radius: 999px; font-size: .82rem; font-weight: 700;
        color: rgba(255,255,255,.8); border: 1px solid rgba(255,255,255,.2); text-decoration: none;
    }
    .rv-preset:hover { color: #fff; border-color: var(--gold); }
    .rv-preset.is-active { background: var(--gold); border-color: var(--gold); color: var(--dark); }
    .rv-range { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem; color: rgba(255,255,255,.6); }
    .rv-range input { padding: .3rem .5rem; border-radius: 8px; border: 1px solid rgba(255,255,255,.2); background: rgba(255,255,255,.08); color: #fff; color-scheme: dark; }
    .rv-btn { background: var(--gold); color: var(--dark); font-weight: 700; }
    .rv-btn:hover { background: var(--gold-dark); color: #fff; }

    .rv-alert {
        display: flex; gap: .7rem; align-items: flex-start; margin-bottom: 1.2rem;
        padding: .9rem 1.1rem; border-radius: 12px;
        background: #fff7e6; border: 1px solid #f3d38a; color: #7a5200;
    }
    .rv-alert i { font-size: 1.4rem; }

    .rv-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.4rem; }
    .rv-kpi {
        padding: 1.1rem 1.2rem; border-radius: 14px; background: #fff; border: 1px solid var(--line);
        box-shadow: 0 6px 20px rgba(0,0,0,.04); display: flex; flex-direction: column; gap: .25rem; min-width: 0;
    }
    .rv-kpi--main { background: linear-gradient(135deg, #d5a51f, #b8860b); border-color: transparent; color: #171717; }
    .rv-kpi__label { font-size: .74rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--muted); }
    .rv-kpi--main .rv-kpi__label { color: rgba(23,23,23,.7); }
    .rv-kpi__value { font-size: clamp(1.4rem, 2.4vw, 2rem); line-height: 1.15; font-weight: 800; overflow-wrap: anywhere; }
    .rv-kpi__foot { font-size: .8rem; color: var(--muted); }
    .rv-kpi--main .rv-kpi__foot { color: rgba(23,23,23,.75); }

    .rv-growth { display: inline-block; padding: .08rem .45rem; border-radius: 999px; font-size: .72rem; font-weight: 800; }
    .rv-growth--up { background: #dcfce7; color: #166534; }
    .rv-growth--down { background: #fee2e2; color: #991b1b; }
    .rv-growth--flat { background: #f1f5f9; color: #475569; }
    .rv-growth--new { background: #e0e7ff; color: #3730a3; }

    .rv-streams { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.6rem; }
    .rv-stream {
        display: flex; flex-direction: column; gap: .55rem; padding: 1.1rem 1.2rem; min-width: 0;
        border-radius: 14px; background: #fff; border: 1px solid var(--line); border-top: 4px solid var(--c);
        color: inherit; text-decoration: none; transition: transform .15s, box-shadow .15s;
    }
    .rv-stream:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(0,0,0,.08); color: inherit; }
    .rv-stream__head { display: flex; align-items: center; gap: .55rem; }
    .rv-stream__icon {
        width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center;
        background: color-mix(in srgb, var(--c) 15%, #fff); color: var(--c); font-size: 1.15rem;
    }
    .rv-stream__name { font-weight: 800; flex: 1; }
    .rv-stream__value { font-size: 1.6rem; font-weight: 800; }
    .rv-stream__share { display: block; height: 6px; border-radius: 999px; background: #f1ede4; overflow: hidden; }
    .rv-stream__share span { display: block; height: 100%; background: var(--c); }
    .rv-stream__facts { display: grid; grid-template-columns: 1fr 1fr; gap: .4rem .8rem; margin: 0; }
    .rv-stream__facts dt { font-size: .7rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; }
    .rv-stream__facts dd { margin: 0; font-weight: 700; }
    .rv-stream__note { font-size: .78rem; color: var(--muted); }

    .rv-card { border: 1px solid var(--line); border-radius: 14px; box-shadow: 0 6px 20px rgba(0,0,0,.04); overflow: hidden; }
    .rv-card .card-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; background: #fff; }
    .rv-card .card-title { font-size: 1.02rem; font-weight: 800; }
    .rv-link { font-weight: 700; color: var(--gold-dark); text-decoration: none; }

    .rv-chart { position: relative; height: 320px; }
    .rv-chart--small { height: 200px; }
    .rv-legend { list-style: none; padding: 0; margin: 1rem 0 0; display: grid; gap: .5rem; }
    .rv-legend li { display: grid; grid-template-columns: 12px 1fr auto; align-items: center; gap: .1rem .6rem; }
    .rv-legend li span { width: 12px; height: 12px; border-radius: 3px; }
    .rv-legend small { grid-column: 2 / 4; color: var(--muted); }

    .rv-table th { font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); background: #faf8f3; white-space: nowrap; }
    .rv-table td { vertical-align: middle; }
    .rv-ellipsis { max-width: 260px; overflow: hidden; text-overflow: ellipsis; }
    .rv-tag {
        display: inline-block; padding: .12rem .5rem; border-radius: 999px; font-size: .72rem; font-weight: 700; white-space: nowrap;
        background: color-mix(in srgb, var(--c) 14%, #fff); color: color-mix(in srgb, var(--c) 80%, #000);
    }
    .rv-provider { font-size: .74rem; font-weight: 800; }
    .rv-provider--moncash { color: #e2001a; }
    .rv-provider--paypal { color: #003087; }

    .rv-goal { padding: .7rem 0; border-bottom: 1px solid #f1ede4; }
    .rv-goal:last-child { border-bottom: 0; }
    .rv-goal__top { display: flex; justify-content: space-between; gap: .6rem; font-weight: 700; }
    .rv-goal__top a { color: inherit; }
    .rv-goal__bottom { font-size: .8rem; color: var(--muted); margin-top: .3rem; }
    .rv-goal__bottom b { color: var(--gold-dark); }
    .rv-bar { height: 8px; margin-top: .45rem; border-radius: 999px; background: #f1ede4; overflow: hidden; }
    .rv-bar span { display: block; height: 100%; background: #16a34a; border-radius: 999px; }
    .rv-status { font-size: .68rem; padding: .1rem .5rem; border-radius: 999px; white-space: nowrap; }
    .rv-status--active { background: #dcfce7; color: #166534; }
    .rv-status--closed { background: #f1f5f9; color: #64748b; }

    .rv-row { display: flex; align-items: center; gap: .8rem; padding: .55rem 0; border-bottom: 1px solid #f1ede4; }
    .rv-row:last-child { border-bottom: 0; }
    .rv-row > span:first-child { flex: 1; min-width: 0; }

    .rv-minis { display: grid; grid-template-columns: 1fr 1fr; gap: .7rem; margin-bottom: 1rem; }
    .rv-minis--4 { grid-template-columns: repeat(4, minmax(0, 1fr)); margin-bottom: 0; }
    .rv-mini { padding: .8rem; border-radius: 12px; background: #faf8f3; text-align: center; }
    .rv-mini strong { display: block; font-size: 1.4rem; }
    .rv-mini span { font-size: .74rem; color: var(--muted); }
    .rv-mini--warn { background: #fff7e6; }
    .rv-mini--warn strong { color: #b45309; }

    .rv-section { margin: 2rem 0 1rem; font-size: 1.15rem; font-weight: 800; }

    .rv-empty { text-align: center; padding: 2.2rem 1rem; color: var(--muted); }
    .rv-empty i { font-size: 2.4rem; opacity: .5; }
    .rv-empty p { margin: .5rem 0 0; }
    .rv-empty--sm { padding: 1rem; }

    @media (max-width: 1199px) {
        .rv-kpis, .rv-streams { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 575px) {
        .rv-kpis, .rv-streams { grid-template-columns: minmax(0, 1fr); }
        .rv-minis--4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .rv-period { justify-items: start; }
    }
</style>
@endpush

@push('after_scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') { return; }

    var money = function (v) {
        return '$' + Number(v).toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    var daily = document.getElementById('rvDaily');
    if (daily) {
        var streams = {{ Illuminate\Support\Js::from(collect($streams)->map(fn ($s) => ['label' => $s['label'], 'color' => $s['color']])) }};
        var series  = {{ Illuminate\Support\Js::from($daily['series']) }};

        new Chart(daily, {
            type: 'bar',
            data: {
                labels: {{ Illuminate\Support\Js::from($daily['labels']) }},
                datasets: Object.keys(streams).map(function (key) {
                    return { label: streams[key].label, data: series[key], backgroundColor: streams[key].color, borderRadius: 3 };
                })
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12 } },
                    tooltip: { callbacks: { label: function (c) { return c.dataset.label + ' : ' + money(c.parsed.y); } } }
                },
                scales: {
                    x: { stacked: true, grid: { display: false }, ticks: { maxTicksLimit: 15 } },
                    y: { stacked: true, beginAtZero: true, ticks: { callback: money } }
                }
            }
        });
    }

    var prov = document.getElementById('rvProviders');
    if (prov) {
        var providers = {{ Illuminate\Support\Js::from($providers) }};
        var names  = {{ Illuminate\Support\Js::from($providerLabels) }};
        var colors = {{ Illuminate\Support\Js::from($providerColors) }};
        var keys = Object.keys(providers);

        new Chart(prov, {
            type: 'doughnut',
            data: {
                labels: keys.map(function (k) { return names[k] || k; }),
                datasets: [{
                    data: keys.map(function (k) { return providers[k].usd; }),
                    backgroundColor: keys.map(function (k) { return colors[k] || '#999'; }),
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function (c) { return c.label + ' : ' + money(c.parsed); } } }
                }
            }
        });
    }
});
</script>
@endpush
