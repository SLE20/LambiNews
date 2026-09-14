@extends(backpack_view('blank'))

@php
    $deviceTranslations = [
        'mobile' => 'Mobile',
        'tablet' => 'Tablette',
        'desktop' => 'Ordinateur',
        'inconnu' => 'Inconnu',
    ];

    $deviceLabelsForChart = $deviceStatistics
        ->pluck('device')
        ->map(function ($device) use ($deviceTranslations) {
            return $deviceTranslations[$device] ?? 'Inconnu';
        })
        ->values()
        ->all();

    $deviceTotalsForChart = $deviceStatistics
        ->pluck('total')
        ->map(function ($total) {
            return (int) $total;
        })
        ->values()
        ->all();
@endphp

@section('header')
    <section class="container-fluid">
        <div class="statistics-heading">
            <div>
                <h1 class="mb-1">Statistiques avancées</h1>

                <p class="text-muted mb-0">
                    Analyse des performances de Lambi News
                </p>
            </div>

            <a
                href="{{ url('/admin/statistiques') }}"
                class="btn btn-outline-secondary"
            >
                <i class="la la-redo-alt me-1"></i>
                Réinitialiser
            </a>
        </div>
    </section>
@endsection

@section('content')
    <div class="container-fluid statistics-page">

        {{-- Filtres de dates --}}
        <div class="card statistics-filter-card mb-4">
            <div class="card-body">
                <form
                    action="{{ url('/admin/statistiques') }}"
                    method="GET"
                    class="row align-items-end g-3"
                >
                    <div class="col-12 col-md-4">
                        <label for="start_date" class="form-label">
                            Date de début
                        </label>

                        <input
                            type="date"
                            id="start_date"
                            name="start_date"
                            class="form-control"
                            value="{{ $startDate->format('Y-m-d') }}"
                            max="{{ now()->format('Y-m-d') }}"
                        >
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="end_date" class="form-label">
                            Date de fin
                        </label>

                        <input
                            type="date"
                            id="end_date"
                            name="end_date"
                            class="form-control"
                            value="{{ $endDate->format('Y-m-d') }}"
                            max="{{ now()->format('Y-m-d') }}"
                        >
                    </div>

                    <div class="col-12 col-md-4">
                        <button
                            type="submit"
                            class="btn btn-gold w-100"
                        >
                            <i class="la la-filter me-1"></i>
                            Appliquer le filtre
                        </button>
                    </div>
                </form>

                <div class="statistics-period mt-3">
                    Période analysée :

                    <strong>
                        {{ $startDate->format('d/m/Y') }}
                    </strong>

                    au

                    <strong>
                        {{ $endDate->format('d/m/Y') }}
                    </strong>
                </div>
            </div>
        </div>

        {{-- Statistiques principales --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-card-icon stat-icon-gold">
                        <i class="la la-eye"></i>
                    </div>

                    <div class="stat-card-content">
                        <span class="stat-label">
                            Consultations
                        </span>

                        <strong class="stat-value">
                            {{ number_format($totalViews, 0, ',', ' ') }}
                        </strong>

                        <small class="stat-help">
                            Pendant la période
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-card-icon stat-icon-blue">
                        <i class="la la-users"></i>
                    </div>

                    <div class="stat-card-content">
                        <span class="stat-label">
                            Visiteurs uniques
                        </span>

                        <strong class="stat-value">
                            {{ number_format($uniqueVisitors, 0, ',', ' ') }}
                        </strong>

                        <small class="stat-help">
                            Estimation sans doublons
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-card-icon stat-icon-green">
                        <i class="la la-newspaper"></i>
                    </div>

                    <div class="stat-card-content">
                        <span class="stat-label">
                            Articles publiés
                        </span>

                        <strong class="stat-value">
                            {{ number_format($articlesPublishedDuringPeriod, 0, ',', ' ') }}
                        </strong>

                        <small class="stat-help">
                            {{ number_format($publishedArticles, 0, ',', ' ') }}
                            publiés au total
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-card-icon stat-icon-purple">
                        <i class="la la-chart-bar"></i>
                    </div>

                    <div class="stat-card-content">
                        <span class="stat-label">
                            Moyenne de vues
                        </span>

                        <strong class="stat-value">
                            {{ number_format($averageViewsPerArticle, 1, ',', ' ') }}
                        </strong>

                        <small class="stat-help">
                            Par article publié
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistiques secondaires --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="small-stat-card">
                    <div>
                        <span>Articles enregistrés</span>

                        <strong>
                            {{ number_format($totalArticles, 0, ',', ' ') }}
                        </strong>
                    </div>

                    <i class="la la-file-alt"></i>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="small-stat-card">
                    <div>
                        <span>Nouveaux abonnés</span>

                        <strong>
                            {{ number_format($newSubscribers, 0, ',', ' ') }}
                        </strong>
                    </div>

                    <i class="la la-envelope"></i>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="small-stat-card">
                    <div>
                        <span>Nouveaux commentaires</span>

                        <strong>
                            {{ number_format($newComments, 0, ',', ' ') }}
                        </strong>
                    </div>

                    <i class="la la-comments"></i>
                </div>
            </div>
        </div>

        {{-- Graphique des consultations --}}
        <div class="card statistics-card mb-4">
            <div class="card-header">
                <div>
                    <h2 class="card-title mb-1">
                        Évolution des consultations
                    </h2>

                    <p class="text-muted mb-0">
                        Nombre de consultations enregistrées chaque jour
                    </p>
                </div>
            </div>

            <div class="card-body">
                @if($totalViews > 0)
                    <div class="main-chart-container">
                        <canvas id="viewsChart"></canvas>
                    </div>
                @else
                    <div class="empty-statistics">
                        <i class="la la-chart-line"></i>

                        <p>
                            Aucune consultation enregistrée pendant cette période.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Articles les plus consultés --}}
        <div class="card statistics-card mb-4">
            <div class="card-header">
                <div>
                    <h2 class="card-title mb-1">
                        Articles les plus consultés
                    </h2>

                    <p class="text-muted mb-0">
                        Classement pour la période sélectionnée
                    </p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th class="ranking-column">#</th>
                            <th>Article</th>
                            <th>Catégorie</th>
                            <th class="text-end">Vues</th>
                            <th class="text-end">Visiteurs</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($topArticles as $topArticle)
                            <tr>
                                <td>
                                    <span class="ranking-number">
                                        {{ $loop->iteration }}
                                    </span>
                                </td>

                                <td>
                                    <strong class="article-title">
                                        {{ $topArticle->title }}
                                    </strong>
                                </td>

                                <td>
                                    <span class="category-badge">
                                        {{ $topArticle->category_name ?? 'Sans catégorie' }}
                                    </span>
                                </td>

                                <td class="text-end">
                                    <strong>
                                        {{ number_format($topArticle->total_views, 0, ',', ' ') }}
                                    </strong>
                                </td>

                                <td class="text-end">
                                    {{ number_format($topArticle->unique_visitors, 0, ',', ' ') }}
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ url('/articles/'.$topArticle->slug) }}"
                                        class="btn btn-sm btn-outline-secondary"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        Voir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-statistics">
                                        <i class="la la-chart-line"></i>

                                        <p>
                                            Aucun article consulté pendant cette période.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Catégories et auteurs --}}
        <div class="row g-4 mb-4">
            <div class="col-12 col-xl-6">
                <div class="card statistics-card h-100">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title mb-1">
                                Catégories performantes
                            </h2>

                            <p class="text-muted mb-0">
                                Consultations par catégorie
                            </p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Catégorie</th>
                                    <th class="text-end">Articles</th>
                                    <th class="text-end">Vues</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($topCategories as $category)
                                    <tr>
                                        <td>
                                            <strong>
                                                {{ $category->name }}
                                            </strong>
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($category->articles_count, 0, ',', ' ') }}
                                        </td>

                                        <td class="text-end">
                                            <strong class="gold-text">
                                                {{ number_format($category->total_views, 0, ',', ' ') }}
                                            </strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td
                                            colspan="3"
                                            class="text-center text-muted py-4"
                                        >
                                            Aucune donnée disponible.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="card statistics-card h-100">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title mb-1">
                                Auteurs performants
                            </h2>

                            <p class="text-muted mb-0">
                                Consultations par journaliste
                            </p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Auteur</th>
                                    <th class="text-end">Articles</th>
                                    <th class="text-end">Vues</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($topAuthors as $author)
                                    <tr>
                                        <td>
                                            <div class="author-cell">
                                                <span class="author-avatar">
                                                    {{ mb_strtoupper(mb_substr($author->name, 0, 1)) }}
                                                </span>

                                                <strong>
                                                    {{ $author->name }}
                                                </strong>
                                            </div>
                                        </td>

                                        <td class="text-end">
                                            {{ number_format($author->articles_count, 0, ',', ' ') }}
                                        </td>

                                        <td class="text-end">
                                            <strong class="gold-text">
                                                {{ number_format($author->total_views, 0, ',', ' ') }}
                                            </strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td
                                            colspan="3"
                                            class="text-center text-muted py-4"
                                        >
                                            Aucune donnée disponible.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Appareils et navigateurs --}}
        <div class="row g-4">
            <div class="col-12 col-xl-5">
                <div class="card statistics-card h-100">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title mb-1">
                                Types d’appareils
                            </h2>

                            <p class="text-muted mb-0">
                                Ordinateurs, mobiles et tablettes
                            </p>
                        </div>
                    </div>

                    <div class="card-body">
                        @if($deviceStatistics->isNotEmpty())
                            <div class="device-chart-container">
                                <canvas id="devicesChart"></canvas>
                            </div>
                        @else
                            <div class="empty-statistics">
                                <i class="la la-mobile"></i>

                                <p>Aucune donnée disponible.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-7">
                <div class="card statistics-card h-100">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title mb-1">
                                Navigateurs utilisés
                            </h2>

                            <p class="text-muted mb-0">
                                Origine technique des consultations
                            </p>
                        </div>
                    </div>

                    <div class="card-body">
                        @forelse($browserStatistics as $browser)
                            @php
                                $browserPercentage = $totalViews > 0
                                    ? round(($browser->total / $totalViews) * 100, 1)
                                    : 0;
                            @endphp

                            <div class="browser-row">
                                <div class="browser-row-header">
                                    <strong>
                                        {{ $browser->browser_name }}
                                    </strong>

                                    <span>
                                        {{ number_format($browser->total, 0, ',', ' ') }}
                                        vues ·
                                        {{ number_format($browserPercentage, 1, ',', ' ') }} %
                                    </span>
                                </div>

                                <div class="browser-progress">
                                    <span
                                        style="width: {{ min($browserPercentage, 100) }}%"
                                    ></span>
                                </div>
                            </div>
                        @empty
                            <div class="empty-statistics">
                                <i class="la la-globe"></i>

                                <p>Aucune donnée disponible.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after_styles')
    <style>
        :root {
            --stats-gold: #d5a51f;
            --stats-gold-dark: #9f7508;
            --stats-dark: #171717;
            --stats-soft: #f7f4ec;
            --stats-border: #e7e3d9;
        }

        .statistics-page {
            padding-bottom: 2rem;
        }

        .statistics-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .statistics-filter-card,
        .statistics-card,
        .stat-card,
        .small-stat-card {
            border: 1px solid var(--stats-border);
            border-radius: 14px;
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.04);
        }

        .statistics-filter-card {
            background: linear-gradient(
                135deg,
                rgba(213, 165, 31, 0.12),
                rgba(255, 255, 255, 0.96)
            );
        }

        .statistics-period {
            color: #6d6658;
            font-size: 0.9rem;
        }

        .btn-gold {
            color: #171717;
            background: var(--stats-gold);
            border-color: var(--stats-gold);
            font-weight: 700;
        }

        .btn-gold:hover,
        .btn-gold:focus {
            color: #fff;
            background: var(--stats-gold-dark);
            border-color: var(--stats-gold-dark);
        }

        .stat-card {
            min-height: 135px;
            height: 100%;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            background: #fff;
        }

        .stat-card-content {
            min-width: 0;
        }

        .stat-card-icon {
            width: 54px;
            height: 54px;
            flex: 0 0 54px;
            display: grid;
            place-items: center;
            border-radius: 15px;
            font-size: 1.6rem;
        }

        .stat-icon-gold {
            color: #8c6500;
            background: rgba(213, 165, 31, 0.18);
        }

        .stat-icon-blue {
            color: #1769aa;
            background: rgba(33, 150, 243, 0.13);
        }

        .stat-icon-green {
            color: #177245;
            background: rgba(42, 157, 98, 0.14);
        }

        .stat-icon-purple {
            color: #7048a8;
            background: rgba(126, 87, 194, 0.14);
        }

        .stat-label,
        .stat-value,
        .stat-help {
            display: block;
        }

        .stat-label {
            overflow: hidden;
            color: #736f68;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-overflow: ellipsis;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .stat-value {
            margin: 0.2rem 0;
            color: var(--stats-dark);
            font-size: clamp(1.5rem, 2.5vw, 2rem);
            line-height: 1.1;
        }

        .stat-help {
            color: #88837b;
            font-size: 0.8rem;
        }

        .small-stat-card {
            padding: 1rem 1.2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--stats-dark);
            color: #fff;
        }

        .small-stat-card span,
        .small-stat-card strong {
            display: block;
        }

        .small-stat-card span {
            color: #c8c8c8;
            font-size: 0.85rem;
        }

        .small-stat-card strong {
            margin-top: 0.15rem;
            color: var(--stats-gold);
            font-size: 1.5rem;
        }

        .small-stat-card i {
            color: rgba(213, 165, 31, 0.7);
            font-size: 2rem;
        }

        .statistics-card {
            overflow: hidden;
        }

        .statistics-card .card-header {
            min-height: 76px;
            padding: 1rem 1.25rem;
            background: #fff;
            border-bottom: 1px solid var(--stats-border);
        }

        .statistics-card .card-title {
            color: var(--stats-dark);
            font-size: 1.05rem;
            font-weight: 800;
        }

        .main-chart-container {
            position: relative;
            min-height: 330px;
            height: 42vh;
            max-height: 430px;
        }

        .device-chart-container {
            position: relative;
            height: 310px;
        }

        .ranking-column {
            width: 60px;
        }

        .ranking-number {
            width: 30px;
            height: 30px;
            display: inline-grid;
            place-items: center;
            border-radius: 50%;
            color: #6d5000;
            background: rgba(213, 165, 31, 0.17);
            font-weight: 800;
        }

        .article-title {
            display: block;
            min-width: 220px;
            max-width: 460px;
            white-space: normal;
            line-height: 1.35;
        }

        .category-badge {
            display: inline-block;
            padding: 0.28rem 0.55rem;
            border-radius: 999px;
            color: #6b520e;
            background: #f8edcb;
            font-size: 0.78rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .gold-text {
            color: var(--stats-gold-dark);
        }

        .author-cell {
            display: flex;
            align-items: center;
            gap: 0.7rem;
        }

        .author-avatar {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            color: #fff;
            background: var(--stats-dark);
            font-size: 0.8rem;
            font-weight: 800;
        }

        .browser-row + .browser-row {
            margin-top: 1.2rem;
        }

        .browser-row-header {
            margin-bottom: 0.45rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .browser-row-header span {
            color: #777;
            font-size: 0.82rem;
            text-align: right;
        }

        .browser-progress {
            height: 8px;
            overflow: hidden;
            border-radius: 999px;
            background: #ece9e1;
        }

        .browser-progress span {
            height: 100%;
            display: block;
            border-radius: inherit;
            background: linear-gradient(
                90deg,
                var(--stats-gold-dark),
                var(--stats-gold)
            );
        }

        .empty-statistics {
            min-height: 160px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #89847b;
            text-align: center;
        }

        .empty-statistics i {
            margin-bottom: 0.6rem;
            color: #c5b991;
            font-size: 2.2rem;
        }

        .empty-statistics p {
            margin: 0;
        }

        @media (max-width: 767.98px) {
            .statistics-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .statistics-page {
                padding-right: 0.75rem;
                padding-left: 0.75rem;
            }

            .main-chart-container {
                min-height: 260px;
                height: 300px;
            }

            .device-chart-container {
                height: 270px;
            }

            .statistics-card .card-header {
                min-height: auto;
            }

            .stat-card {
                min-height: 115px;
            }

            .browser-row-header {
                align-items: flex-start;
                flex-direction: column;
                gap: 0.2rem;
            }

            .browser-row-header span {
                text-align: left;
            }
        }
    </style>
@endpush

@push('after_scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartLabels =
                {{ Illuminate\Support\Js::from($chartLabels) }};

            const chartViews =
                {{ Illuminate\Support\Js::from($chartViews) }};

            const deviceLabels =
                {{ Illuminate\Support\Js::from($deviceLabelsForChart) }};

            const deviceTotals =
                {{ Illuminate\Support\Js::from($deviceTotalsForChart) }};

            const viewsCanvas =
                document.getElementById('viewsChart');

            if (
                viewsCanvas &&
                typeof Chart !== 'undefined'
            ) {
                new Chart(viewsCanvas, {
                    type: 'line',

                    data: {
                        labels: chartLabels,

                        datasets: [
                            {
                                label: 'Consultations',
                                data: chartViews,
                                borderColor: '#b98a0d',
                                backgroundColor: 'rgba(213, 165, 31, 0.15)',
                                borderWidth: 3,
                                pointRadius: chartLabels.length > 60 ? 0 : 3,
                                pointHoverRadius: 6,
                                pointBackgroundColor: '#d5a51f',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                tension: 0.35,
                                fill: true
                            }
                        ]
                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false,

                        interaction: {
                            mode: 'index',
                            intersect: false
                        },

                        plugins: {
                            legend: {
                                display: false
                            },

                            tooltip: {
                                backgroundColor: '#171717',
                                titleColor: '#ffffff',
                                bodyColor: '#ffffff',
                                padding: 12,
                                displayColors: false
                            }
                        },

                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },

                                ticks: {
                                    maxTicksLimit: 15
                                }
                            },

                            y: {
                                beginAtZero: true,

                                ticks: {
                                    precision: 0
                                },

                                grid: {
                                    color: 'rgba(0, 0, 0, 0.06)'
                                }
                            }
                        }
                    }
                });
            }

            const devicesCanvas =
                document.getElementById('devicesChart');

            if (
                devicesCanvas &&
                typeof Chart !== 'undefined'
            ) {
                new Chart(devicesCanvas, {
                    type: 'doughnut',

                    data: {
                        labels: deviceLabels,

                        datasets: [
                            {
                                data: deviceTotals,

                                backgroundColor: [
                                    '#d5a51f',
                                    '#171717',
                                    '#3c82d4',
                                    '#8b69b3'
                                ],

                                borderColor: '#ffffff',
                                borderWidth: 4,
                                hoverOffset: 6
                            }
                        ]
                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',

                        plugins: {
                            legend: {
                                position: 'bottom',

                                labels: {
                                    usePointStyle: true,
                                    padding: 20
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush