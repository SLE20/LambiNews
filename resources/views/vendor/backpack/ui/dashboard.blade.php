@extends(backpack_view('blank'))

@php
    $statistics = $statistics ?? [
        'articles' => 0,
        'published' => 0,
        'drafts' => 0,
        'review' => 0,
        'scheduled' => 0,
        'archived' => 0,
        'total_views' => 0,
        'views_today' => 0,
        'views_30_days' => 0,
        'unique_visitors_30_days' => 0,
        'pending_comments' => 0,
        'unread_messages' => 0,
        'subscribers' => 0,
    ];

    $recentArticles = $recentArticles ?? collect();
    $topArticles = $topArticles ?? collect();
    $chartLabels = $chartLabels ?? [];
    $chartViews = $chartViews ?? [];

    $dashboardPermissions = $dashboardPermissions ?? [
        'is_author' => false,
        'can_manage_editorial' => false,
        'is_admin' => false,
    ];
@endphp

@section('content')
    <div class="container-fluid ln-dashboard">

        {{-- En-tête --}}
        <div class="ln-dashboard-header">
            <div>
                <span class="ln-dashboard-kicker">
                    Lambi News
                </span>

                <h1>Tableau de bord</h1>

                <p>
                    Bienvenue, {{ backpack_user()->name }}.
                    Voici les données actuelles de votre journal.
                </p>
            </div>

            <div class="ln-dashboard-actions">
                <a
                    href="{{ backpack_url('article/create') }}"
                    class="btn ln-btn-gold"
                >
                    <i class="la la-plus"></i>
                    Nouvel article
                </a>

                <a
                    href="{{ url('/') }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn btn-outline-secondary"
                >
                    <i class="la la-external-link-alt"></i>
                    Voir le site
                </a>
            </div>
        </div>

        {{-- Cartes principales --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <a
                    href="{{ backpack_url('article') }}"
                    class="ln-stat-link"
                >
                    <div class="ln-stat-card">
                        <div class="ln-stat-icon ln-icon-dark">
                            <i class="la la-newspaper"></i>
                        </div>

                        <div>
                            <span class="ln-stat-label">
                                Tous les articles
                            </span>

                            <strong class="ln-stat-value">
                                {{ number_format(
                                    $statistics['articles'],
                                    0,
                                    ',',
                                    ' '
                                ) }}
                            </strong>

                            <small>
                                Dans la base de données
                            </small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="ln-stat-card">
                    <div class="ln-stat-icon ln-icon-green">
                        <i class="la la-check-circle"></i>
                    </div>

                    <div>
                        <span class="ln-stat-label">
                            Articles publiés
                        </span>

                        <strong class="ln-stat-value">
                            {{ number_format(
                                $statistics['published'],
                                0,
                                ',',
                                ' '
                            ) }}
                        </strong>

                        <small>
                            Accessibles au public
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <a
                    href="{{ backpack_url('statistiques') }}"
                    class="ln-stat-link"
                >
                    <div class="ln-stat-card">
                        <div class="ln-stat-icon ln-icon-gold">
                            <i class="la la-eye"></i>
                        </div>

                        <div>
                            <span class="ln-stat-label">
                                Vues sur 30 jours
                            </span>

                            <strong class="ln-stat-value">
                                {{ number_format(
                                    $statistics['views_30_days'],
                                    0,
                                    ',',
                                    ' '
                                ) }}
                            </strong>

                            <small>
                                {{ number_format(
                                    $statistics['views_today'],
                                    0,
                                    ',',
                                    ' '
                                ) }}
                                aujourd’hui
                            </small>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <a
                    href="{{ backpack_url('statistiques') }}"
                    class="ln-stat-link"
                >
                    <div class="ln-stat-card">
                        <div class="ln-stat-icon ln-icon-blue">
                            <i class="la la-users"></i>
                        </div>

                        <div>
                            <span class="ln-stat-label">
                                Visiteurs uniques
                            </span>

                            <strong class="ln-stat-value">
                                {{ number_format(
                                    $statistics['unique_visitors_30_days'],
                                    0,
                                    ',',
                                    ' '
                                ) }}
                            </strong>

                            <small>
                                Sur les 30 derniers jours
                            </small>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- Revenus (administrateurs) --}}
        @if(! empty($revenue))
            @include('admin.partials.dashboard-revenue', ['revenue' => $revenue])
        @endif

        {{-- États éditoriaux --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl">
                <div class="ln-small-card">
                    <span>Brouillons</span>

                    <strong>
                        {{ number_format(
                            $statistics['drafts'],
                            0,
                            ',',
                            ' '
                        ) }}
                    </strong>

                    <i class="la la-file-alt"></i>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl">
                <div class="ln-small-card">
                    <span>En révision</span>

                    <strong>
                        {{ number_format(
                            $statistics['review'],
                            0,
                            ',',
                            ' '
                        ) }}
                    </strong>

                    <i class="la la-search"></i>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl">
                <div class="ln-small-card">
                    <span>Programmés</span>

                    <strong>
                        {{ number_format(
                            $statistics['scheduled'],
                            0,
                            ',',
                            ' '
                        ) }}
                    </strong>

                    <i class="la la-clock"></i>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl">
                <div class="ln-small-card">
                    <span>Archivés</span>

                    <strong>
                        {{ number_format(
                            $statistics['archived'],
                            0,
                            ',',
                            ' '
                        ) }}
                    </strong>

                    <i class="la la-archive"></i>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl">
                <div class="ln-small-card">
                    <span>Vues totales</span>

                    <strong>
                        {{ number_format(
                            $statistics['total_views'],
                            0,
                            ',',
                            ' '
                        ) }}
                    </strong>

                    <i class="la la-chart-line"></i>
                </div>
            </div>
        </div>

        {{-- Éléments à traiter --}}
        @if(
            $dashboardPermissions['can_manage_editorial']
            || $dashboardPermissions['is_admin']
        )
            <div class="row g-3 mb-4">
                @if($dashboardPermissions['can_manage_editorial'])
                    <div class="col-12 col-md-6 col-xl-4">
                        <a
                            href="{{ backpack_url('comment') }}"
                            class="ln-stat-link"
                        >
                            <div class="ln-alert-card">
                                <div class="ln-alert-icon">
                                    <i class="la la-comments"></i>
                                </div>

                                <div>
                                    <span>
                                        Commentaires à valider
                                    </span>

                                    <strong>
                                        {{ number_format(
                                            $statistics['pending_comments'],
                                            0,
                                            ',',
                                            ' '
                                        ) }}
                                    </strong>
                                </div>

                                <i class="la la-angle-right ln-arrow"></i>
                            </div>
                        </a>
                    </div>

                    <div class="col-12 col-md-6 col-xl-4">
                        <a
                            href="{{ backpack_url('contact-message') }}"
                            class="ln-stat-link"
                        >
                            <div class="ln-alert-card">
                                <div class="ln-alert-icon">
                                    <i class="la la-envelope"></i>
                                </div>

                                <div>
                                    <span>
                                        Messages non lus
                                    </span>

                                    <strong>
                                        {{ number_format(
                                            $statistics['unread_messages'],
                                            0,
                                            ',',
                                            ' '
                                        ) }}
                                    </strong>
                                </div>

                                <i class="la la-angle-right ln-arrow"></i>
                            </div>
                        </a>
                    </div>
                @endif

                @if($dashboardPermissions['is_admin'])
                    <div class="col-12 col-md-6 col-xl-4">
                        <a
                            href="{{ backpack_url('subscriber') }}"
                            class="ln-stat-link"
                        >
                            <div class="ln-alert-card">
                                <div class="ln-alert-icon">
                                    <i class="la la-user-check"></i>
                                </div>

                                <div>
                                    <span>
                                        Abonnés actifs
                                    </span>

                                    <strong>
                                        {{ number_format(
                                            $statistics['subscribers'],
                                            0,
                                            ',',
                                            ' '
                                        ) }}
                                    </strong>
                                </div>

                                <i class="la la-angle-right ln-arrow"></i>
                            </div>
                        </a>
                    </div>
                @endif
            </div>
        @endif

        {{-- Graphique et classement --}}
        <div class="row g-4 mb-4">
            <div class="col-12 col-xl-8">
                <div class="card ln-dashboard-card h-100">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title mb-1">
                                Consultations
                            </h2>

                            <p class="text-muted mb-0">
                                Évolution sur les 30 derniers jours
                            </p>
                        </div>

                        <a
                            href="{{ backpack_url('statistiques') }}"
                            class="ln-card-link"
                        >
                            Voir les statistiques
                        </a>
                    </div>

                    <div class="card-body">
                        @if($statistics['views_30_days'] > 0)
                            <div class="ln-chart-container">
                                <canvas id="dashboardViewsChart"></canvas>
                            </div>
                        @else
                            <div class="ln-empty">
                                <i class="la la-chart-area"></i>

                                <p>
                                    Aucune consultation enregistrée
                                    pendant les 30 derniers jours.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card ln-dashboard-card h-100">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title mb-1">
                                Articles populaires
                            </h2>

                            <p class="text-muted mb-0">
                                Sur les 30 derniers jours
                            </p>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="ln-popular-list">
                            @forelse($topArticles as $topArticle)
                                <a
                                    href="{{ backpack_url(
                                        'article/'.$topArticle->id.'/edit'
                                    ) }}"
                                    class="ln-popular-item"
                                >
                                    <span class="ln-popular-number">
                                        {{ $loop->iteration }}
                                    </span>

                                    <div class="ln-popular-content">
                                        <strong>
                                            {{ $topArticle->title }}
                                        </strong>

                                        <small>
                                            {{ $topArticle->category?->name
                                                ?? 'Sans catégorie' }}

                                            ·

                                            {{ number_format(
                                                $topArticle->period_views_count,
                                                0,
                                                ',',
                                                ' '
                                            ) }}
                                            vues
                                        </small>
                                    </div>

                                    <i class="la la-angle-right"></i>
                                </a>
                            @empty
                                <div class="ln-empty ln-empty-small">
                                    <i class="la la-newspaper"></i>

                                    <p>
                                        Aucune donnée disponible.
                                    </p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Articles récents --}}
        <div class="card ln-dashboard-card">
            <div class="card-header">
                <div>
                    <h2 class="card-title mb-1">
                        Articles récents
                    </h2>

                    <p class="text-muted mb-0">
                        Derniers contenus enregistrés
                    </p>
                </div>

                <a
                    href="{{ backpack_url('article') }}"
                    class="ln-card-link"
                >
                    Voir tous les articles
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Catégorie</th>
                            <th>Auteur</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($recentArticles as $article)
                            <tr>
                                <td>
                                    <strong class="ln-article-title">
                                        {{ $article->title }}
                                    </strong>
                                </td>

                                <td>
                                    {{ $article->category?->name
                                        ?? 'Sans catégorie' }}
                                </td>

                                <td>
                                    {{ $article->author?->name
                                        ?? 'Auteur inconnu' }}
                                </td>

                                <td>
                                    @switch($article->status)
                                        @case('published')
                                            <span class="badge bg-success">
                                                Publié
                                            </span>
                                            @break

                                        @case('review')
                                            <span class="badge bg-warning text-dark">
                                                En révision
                                            </span>
                                            @break

                                        @case('scheduled')
                                            <span class="badge bg-info text-dark">
                                                Programmé
                                            </span>
                                            @break

                                        @case('archived')
                                            <span class="badge bg-dark">
                                                Archivé
                                            </span>
                                            @break

                                        @default
                                            <span class="badge bg-secondary">
                                                Brouillon
                                            </span>
                                    @endswitch
                                </td>

                                <td>
                                    {{ $article->created_at
                                        ?->translatedFormat('d M Y') }}
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ backpack_url(
                                            'article/'.$article->id.'/edit'
                                        ) }}"
                                        class="btn btn-sm btn-outline-secondary"
                                    >
                                        Modifier
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="ln-empty">
                                        <i class="la la-folder-open"></i>

                                        <p>
                                            Aucun article pour le moment.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('after_styles')
    <style>
        .ln-dashboard {
            --ln-gold: #d5a51f;
            --ln-gold-dark: #9c7207;
            --ln-dark: #171717;
            --ln-border: #e5e1d8;
            --ln-muted: #79746c;

            padding-top: 1rem;
            padding-bottom: 2.5rem;
        }

        .ln-dashboard-header {
            margin-bottom: 1.6rem;
            padding: 1.5rem 1.7rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            border: 1px solid var(--ln-border);
            border-radius: 16px;
            background:
                linear-gradient(
                    120deg,
                    #171717 0%,
                    #2a261d 100%
                );
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.1);
        }

        .ln-dashboard-kicker {
            display: block;
            margin-bottom: 0.35rem;
            color: var(--ln-gold);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .ln-dashboard-header h1 {
            margin: 0;
            color: #fff;
            font-size: clamp(1.65rem, 4vw, 2.3rem);
            line-height: 1.15;
        }

        .ln-dashboard-header p {
            margin: 0.45rem 0 0;
            color: rgba(255, 255, 255, 0.65);
        }

        .ln-dashboard-actions {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            flex-wrap: wrap;
        }

        .ln-btn-gold {
            color: #171717;
            background: var(--ln-gold);
            border-color: var(--ln-gold);
            font-weight: 700;
        }

        .ln-btn-gold:hover {
            color: #fff;
            background: var(--ln-gold-dark);
            border-color: var(--ln-gold-dark);
        }

        .ln-stat-link {
            height: 100%;
            display: block;
            color: inherit;
            text-decoration: none;
        }

        .ln-stat-card {
            min-height: 128px;
            height: 100%;
            padding: 1.15rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border: 1px solid var(--ln-border);
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.045);
            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }

        .ln-stat-link:hover .ln-stat-card {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
        }

        .ln-stat-icon {
            width: 52px;
            height: 52px;
            flex: 0 0 52px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            font-size: 1.55rem;
        }

        .ln-icon-dark {
            color: #fff;
            background: var(--ln-dark);
        }

        .ln-icon-green {
            color: #147c48;
            background: rgba(36, 171, 93, 0.14);
        }

        .ln-icon-gold {
            color: #8c6500;
            background: rgba(213, 165, 31, 0.2);
        }

        .ln-icon-blue {
            color: #1769aa;
            background: rgba(33, 150, 243, 0.14);
        }

        .ln-stat-label,
        .ln-stat-value,
        .ln-stat-card small {
            display: block;
        }

        .ln-stat-label {
            color: var(--ln-muted);
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .ln-stat-value {
            margin: 0.2rem 0;
            color: var(--ln-dark);
            font-size: 1.8rem;
            line-height: 1;
        }

        .ln-stat-card small {
            color: #8d8982;
            font-size: 0.78rem;
        }

        .ln-small-card {
            position: relative;
            min-height: 100px;
            height: 100%;
            padding: 1rem;
            overflow: hidden;
            border-radius: 13px;
            color: #fff;
            background: var(--ln-dark);
        }

        .ln-small-card span,
        .ln-small-card strong {
            position: relative;
            z-index: 2;
            display: block;
        }

        .ln-small-card span {
            color: rgba(255, 255, 255, 0.65);
            font-size: 0.78rem;
        }

        .ln-small-card strong {
            margin-top: 0.25rem;
            color: var(--ln-gold);
            font-size: 1.55rem;
        }

        .ln-small-card > i {
            position: absolute;
            right: 0.7rem;
            bottom: 0.3rem;
            color: rgba(213, 165, 31, 0.15);
            font-size: 3.2rem;
        }

        .ln-alert-card {
            min-height: 92px;
            height: 100%;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.9rem;
            border: 1px solid #eadfbd;
            border-radius: 13px;
            background: #fffaf0;
        }

        .ln-alert-icon {
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            color: #7a5800;
            background: #f2dfa2;
            font-size: 1.3rem;
        }

        .ln-alert-card span,
        .ln-alert-card strong {
            display: block;
        }

        .ln-alert-card span {
            color: #655f53;
            font-size: 0.82rem;
        }

        .ln-alert-card strong {
            color: var(--ln-dark);
            font-size: 1.5rem;
        }

        .ln-arrow {
            margin-left: auto;
            color: #9b927f;
        }

        .ln-dashboard-card {
            overflow: hidden;
            border: 1px solid var(--ln-border);
            border-radius: 14px;
            box-shadow: 0 6px 22px rgba(0, 0, 0, 0.045);
        }

        .ln-dashboard-card .card-header {
            min-height: 74px;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            background: #fff;
            border-bottom: 1px solid var(--ln-border);
        }

        .ln-dashboard-card .card-title {
            color: var(--ln-dark);
            font-size: 1.05rem;
            font-weight: 800;
        }

        .ln-card-link {
            color: var(--ln-gold-dark);
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
        }

        .ln-card-link:hover {
            color: var(--ln-dark);
            text-decoration: underline;
        }

        .ln-chart-container {
            position: relative;
            height: 320px;
        }

        .ln-popular-item {
            min-height: 78px;
            padding: 0.85rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: inherit;
            text-decoration: none;
            border-bottom: 1px solid #eeeae1;
        }

        .ln-popular-item:last-child {
            border-bottom: 0;
        }

        .ln-popular-item:hover {
            color: inherit;
            background: #faf8f2;
        }

        .ln-popular-number {
            width: 31px;
            height: 31px;
            flex: 0 0 31px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            color: #715200;
            background: #f4e6b9;
            font-size: 0.78rem;
            font-weight: 800;
        }

        .ln-popular-content {
            min-width: 0;
            flex: 1;
        }

        .ln-popular-content strong,
        .ln-popular-content small {
            display: block;
        }

        .ln-popular-content strong {
            overflow: hidden;
            color: var(--ln-dark);
            font-size: 0.87rem;
            line-height: 1.3;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .ln-popular-content small {
            margin-top: 0.25rem;
            color: #817b71;
            font-size: 0.75rem;
        }

        .ln-article-title {
            min-width: 220px;
            display: block;
            white-space: normal;
            line-height: 1.35;
        }

        .ln-empty {
            min-height: 180px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #89847b;
            text-align: center;
        }

        .ln-empty-small {
            min-height: 260px;
            padding: 1rem;
        }

        .ln-empty i {
            margin-bottom: 0.6rem;
            color: #c5b991;
            font-size: 2.2rem;
        }

        .ln-empty p {
            max-width: 380px;
            margin: 0;
        }

        @media (max-width: 767.98px) {
            .ln-dashboard {
                padding-right: 0.75rem;
                padding-left: 0.75rem;
            }

            .ln-dashboard-header {
                align-items: flex-start;
                flex-direction: column;
                padding: 1.3rem;
            }

            .ln-dashboard-actions {
                width: 100%;
            }

            .ln-dashboard-actions .btn {
                flex: 1;
            }

            .ln-chart-container {
                height: 270px;
            }

            .ln-dashboard-card .card-header {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (max-width: 479.98px) {
            .ln-dashboard-actions .btn {
                width: 100%;
                flex: auto;
            }

            .ln-stat-card {
                min-height: 112px;
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

            const chartCanvas =
                document.getElementById('dashboardViewsChart');

            if (
                !chartCanvas
                || typeof Chart === 'undefined'
            ) {
                return;
            }

            new Chart(chartCanvas, {
                type: 'line',

                data: {
                    labels: chartLabels,

                    datasets: [
                        {
                            label: 'Consultations',
                            data: chartViews,
                            borderColor: '#b98a0d',
                            backgroundColor: 'rgba(213, 165, 31, 0.14)',
                            borderWidth: 3,
                            pointRadius: 2,
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
                                maxTicksLimit: 12
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
        });
    </script>
@endpush