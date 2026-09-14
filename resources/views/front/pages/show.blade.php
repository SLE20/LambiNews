@extends('front.layouts.app')

@section('title', $page->title.' - Lambi News')

@section('content')
    <main class="ln-page">
        <div class="container">

            {{-- Fil d’Ariane --}}
            <nav class="ln-page-breadcrumb" aria-label="Fil d’Ariane">
                <a href="{{ url('/') }}">
                    Accueil
                </a>

                <span aria-hidden="true">/</span>

                <span>{{ $page->title }}</span>
            </nav>

            {{-- En-tête --}}
            <header class="ln-page-header">
                <div class="ln-page-header-decoration">
                    <span></span>
                    <span></span>
                </div>

                <div class="ln-page-header-content">
                    <span class="ln-page-kicker">
                        Lambi News
                    </span>

                    <h1>{{ $page->title }}</h1>

                    <p class="ln-page-introduction">
                        Information, transparence et engagement au service
                        des citoyens.
                    </p>

                    @if($page->updated_at)
                        <div class="ln-page-update">
                            <span class="ln-page-update-icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        d="M12 8v4l3 2M21 12a9 9 0 1 1-3-6.7M21 4v6h-6"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </span>

                            Mise à jour le
                            {{ $page->updated_at->translatedFormat('d F Y') }}
                        </div>
                    @endif
                </div>
            </header>

            {{-- Contenu principal --}}
            <div class="ln-page-layout">
                <article class="ln-page-card">
                    <div class="ln-page-card-accent"></div>

                    <div class="ln-page-content">
                        {!! html_entity_decode(
                            $page->content,
                            ENT_QUOTES | ENT_HTML5,
                            'UTF-8'
                        ) !!}
                    </div>
                </article>

                {{-- Colonne latérale --}}
                <aside class="ln-page-sidebar">
                    <div class="ln-sidebar-card">
                        <img
                            src="{{ asset('images/lambinews-embleme.jpg') }}"
                            alt="Lambi News"
                            class="ln-sidebar-logo"
                            width="110"
                            height="110"
                            loading="lazy"
                            decoding="async"
                        >

                        <h2>Lambi News</h2>

                        <p>
                            Le citoyen au cœur de l’information.
                        </p>

                        <a
                            href="{{ url('/contact') }}"
                            class="ln-sidebar-button"
                        >
                            Nous contacter
                        </a>
                    </div>

                    <div class="ln-sidebar-card ln-social-card">
                        <span class="ln-sidebar-label">
                            Suivez-nous
                        </span>

                        <div class="ln-social-links">
                            <a
                                href="https://www.facebook.com/lambinews/"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="Facebook"
                            >
                                Facebook
                            </a>

                            <a
                                href="https://www.instagram.com/info.lambinews/"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="Instagram"
                            >
                                Instagram
                            </a>

                            <a
                                href="https://www.tiktok.com/@lambinews"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="TikTok"
                            >
                                TikTok
                            </a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <style>
        .ln-page {
            --ln-gold: #d4a51f;
            --ln-gold-dark: #9d7306;
            --ln-black: #181714;
            --ln-text: #3d3931;
            --ln-muted: #777064;
            --ln-border: #e4ded1;
            --ln-cream: #f8f4e9;

            position: relative;
            padding: 2rem 0 5.5rem;
            overflow: hidden;
            background:
                radial-gradient(
                    circle at 92% 10%,
                    rgba(212, 165, 31, 0.12),
                    transparent 25rem
                ),
                linear-gradient(
                    180deg,
                    #fbfaf7 0%,
                    #f7f3eb 100%
                );
        }

        .ln-page::before {
            position: absolute;
            top: 210px;
            left: -170px;
            width: 340px;
            height: 340px;
            border: 1px solid rgba(212, 165, 31, 0.18);
            border-radius: 50%;
            content: "";
            pointer-events: none;
        }

        .ln-page .container {
            position: relative;
            z-index: 1;
        }

        .ln-page-breadcrumb {
            max-width: 1180px;
            margin: 0 auto 2rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            color: var(--ln-muted);
            font-size: 0.86rem;
        }

        .ln-page-breadcrumb a {
            color: var(--ln-gold-dark);
            font-weight: 700;
            text-decoration: none;
        }

        .ln-page-breadcrumb a:hover {
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .ln-page-header {
            position: relative;
            max-width: 1180px;
            margin: 0 auto 2.5rem;
            padding: clamp(2rem, 6vw, 5rem);
            overflow: hidden;
            border-radius: 26px;
            color: #fff;
            background:
                linear-gradient(
                    115deg,
                    rgba(20, 19, 17, 0.99),
                    rgba(36, 32, 24, 0.94)
                );
            box-shadow: 0 25px 70px rgba(25, 22, 15, 0.16);
        }

        .ln-page-header::after {
            position: absolute;
            right: -70px;
            bottom: -115px;
            width: 330px;
            height: 330px;
            border: 58px solid rgba(212, 165, 31, 0.12);
            border-radius: 50%;
            content: "";
        }

        .ln-page-header-content {
            position: relative;
            z-index: 2;
            max-width: 850px;
        }

        .ln-page-header-decoration {
            position: absolute;
            top: 0;
            left: clamp(2rem, 6vw, 5rem);
            display: flex;
            gap: 6px;
        }

        .ln-page-header-decoration span:first-child {
            width: 82px;
            height: 5px;
            background: var(--ln-gold);
        }

        .ln-page-header-decoration span:last-child {
            width: 20px;
            height: 5px;
            background: rgba(255, 255, 255, 0.55);
        }

        .ln-page-kicker {
            display: block;
            margin-bottom: 1rem;
            color: var(--ln-gold);
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .ln-page-header h1 {
            max-width: 900px;
            margin: 0;
            color: #fff;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(2.6rem, 7vw, 5.6rem);
            font-weight: 700;
            letter-spacing: -0.04em;
            line-height: 1;
        }

        .ln-page-introduction {
            max-width: 650px;
            margin: 1.4rem 0 0;
            color: rgba(255, 255, 255, 0.74);
            font-size: clamp(1rem, 2vw, 1.15rem);
            line-height: 1.7;
        }

        .ln-page-update {
            margin-top: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.55rem;
            color: rgba(255, 255, 255, 0.62);
            font-size: 0.82rem;
        }

        .ln-page-update-icon {
            width: 27px;
            height: 27px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(212, 165, 31, 0.45);
            border-radius: 50%;
            color: var(--ln-gold);
        }

        .ln-page-update-icon svg {
            width: 14px;
            height: 14px;
        }

        .ln-page-layout {
            max-width: 1180px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(0, 1fr) 280px;
            align-items: start;
            gap: 2rem;
        }

        .ln-page-card {
            position: relative;
            overflow: hidden;
            border: 1px solid var(--ln-border);
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 16px 45px rgba(35, 30, 20, 0.07);
        }

        .ln-page-card-accent {
            width: 100%;
            height: 5px;
            background:
                linear-gradient(
                    90deg,
                    var(--ln-gold),
                    #f1cf65,
                    var(--ln-gold-dark)
                );
        }

        .ln-page-content {
            padding: clamp(1.7rem, 5vw, 4rem);
            color: var(--ln-text);
            font-size: 1.08rem;
            line-height: 1.9;
        }

        .ln-page-content > *:first-child {
            margin-top: 0;
        }

        .ln-page-content > *:last-child {
            margin-bottom: 0;
        }

        .ln-page-content h2 {
            position: relative;
            margin: 2.8rem 0 1rem;
            padding-bottom: 0.75rem;
            color: var(--ln-black);
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(1.6rem, 4vw, 2.25rem);
            line-height: 1.25;
        }

        .ln-page-content h2::after {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 48px;
            height: 3px;
            border-radius: 10px;
            background: var(--ln-gold);
            content: "";
        }

        .ln-page-content h3 {
            margin: 2rem 0 0.8rem;
            color: var(--ln-black);
            font-size: 1.35rem;
            line-height: 1.35;
        }

        .ln-page-content p {
            margin: 0 0 1.35rem;
        }

        .ln-page-content strong {
            color: var(--ln-black);
        }

        .ln-page-content ul,
        .ln-page-content ol {
            margin: 1.3rem 0 1.7rem;
            padding: 1.3rem 1.5rem 1.3rem 3.2rem;
            border-radius: 12px;
            background: var(--ln-cream);
        }

        .ln-page-content li {
            margin-bottom: 0.65rem;
            padding-left: 0.3rem;
        }

        .ln-page-content li:last-child {
            margin-bottom: 0;
        }

        .ln-page-content li::marker {
            color: var(--ln-gold-dark);
            font-weight: 800;
        }

        .ln-page-content a {
            color: var(--ln-gold-dark);
            font-weight: 700;
            text-decoration: underline;
            text-decoration-color: rgba(157, 115, 6, 0.4);
            text-underline-offset: 4px;
        }

        .ln-page-content a:hover {
            color: var(--ln-black);
            text-decoration-color: var(--ln-black);
        }

        .ln-page-content img {
            max-width: 100%;
            height: auto;
            margin: 1.5rem 0;
            border-radius: 14px;
        }

        .ln-page-content blockquote {
            position: relative;
            margin: 2rem 0;
            padding: 1.5rem 1.6rem 1.5rem 2rem;
            border: 0;
            border-left: 4px solid var(--ln-gold);
            border-radius: 0 12px 12px 0;
            color: #49443b;
            background: var(--ln-cream);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 1.15rem;
            font-style: italic;
        }

        .ln-page-sidebar {
            position: sticky;
            top: 100px;
            display: grid;
            gap: 1rem;
        }

        .ln-sidebar-card {
            padding: 1.7rem;
            border: 1px solid var(--ln-border);
            border-radius: 18px;
            background: #fff;
            text-align: center;
            box-shadow: 0 12px 35px rgba(35, 30, 20, 0.06);
        }

        .ln-sidebar-logo {
            width: 92px;
            height: 92px;
            margin: 0 auto 1rem;
            border: 4px solid #f3ead1;
            border-radius: 50%;
            object-fit: cover;
        }

        .ln-sidebar-card h2 {
            margin: 0 0 0.5rem;
            color: var(--ln-black);
            font-family: Georgia, "Times New Roman", serif;
            font-size: 1.45rem;
        }

        .ln-sidebar-card p {
            margin: 0 0 1.25rem;
            color: var(--ln-muted);
            font-size: 0.92rem;
            line-height: 1.6;
        }

        .ln-sidebar-button {
            width: 100%;
            min-height: 44px;
            padding: 0.7rem 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            color: var(--ln-black);
            background: var(--ln-gold);
            font-size: 0.9rem;
            font-weight: 800;
            text-decoration: none;
            transition:
                color 0.2s ease,
                background 0.2s ease,
                transform 0.2s ease;
        }

        .ln-sidebar-button:hover {
            color: #fff;
            background: var(--ln-black);
            transform: translateY(-2px);
        }

        .ln-social-card {
            text-align: left;
        }

        .ln-sidebar-label {
            display: block;
            margin-bottom: 0.8rem;
            color: var(--ln-black);
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .ln-social-links {
            display: grid;
            gap: 0.55rem;
        }

        .ln-social-links a {
            padding: 0.65rem 0.8rem;
            border-radius: 8px;
            color: var(--ln-text);
            background: #f6f3ec;
            font-size: 0.88rem;
            font-weight: 700;
            text-decoration: none;
            transition:
                color 0.2s ease,
                background 0.2s ease;
        }

        .ln-social-links a:hover {
            color: var(--ln-black);
            background: #efe2b9;
        }

        @media (max-width: 991px) {
            .ln-page-layout {
                grid-template-columns: 1fr;
            }

            .ln-page-sidebar {
                position: static;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 767px) {
            .ln-page {
                padding: 1.4rem 0 3.5rem;
            }

            .ln-page-breadcrumb {
                margin-bottom: 1.3rem;
                overflow: hidden;
                white-space: nowrap;
            }

            .ln-page-breadcrumb span:last-child {
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .ln-page-header {
                margin-bottom: 1.4rem;
                padding: 2.4rem 1.4rem;
                border-radius: 17px;
            }

            .ln-page-header h1 {
                font-size: clamp(2.35rem, 13vw, 4rem);
            }

            .ln-page-layout {
                gap: 1.3rem;
            }

            .ln-page-card {
                border-radius: 14px;
            }

            .ln-page-content {
                font-size: 1rem;
                line-height: 1.75;
            }

            .ln-page-content ul,
            .ln-page-content ol {
                padding-left: 2.5rem;
            }

            .ln-page-sidebar {
                grid-template-columns: 1fr;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .ln-page *,
            .ln-page *::before,
            .ln-page *::after {
                scroll-behavior: auto !important;
                transition: none !important;
            }
        }
    </style>
@endsection