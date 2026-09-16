<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        @yield(
            'title',
            'Lambi News — Le citoyen au cœur de l’information'
        )
    </title>

    <meta
        name="description"
        content="@yield(
            'meta_description',
            'Lambi News vous informe sur l’actualité nationale et internationale.'
        )"
    >

    @include('front.partials.seo')


    {{-- ========================================================= --}}
    {{-- FAVICONS --}}
    {{-- ========================================================= --}}

    <link
        rel="icon"
        type="image/png"
        sizes="32x32"
        href="{{ asset('images/favicon-32x32.png') }}"
    >

    <link
        rel="icon"
        type="image/png"
        sizes="16x16"
        href="{{ asset('images/favicon-16x16.png') }}"
    >

    <link
        rel="shortcut icon"
        type="image/x-icon"
        href="{{ asset('images/favicon.ico') }}"
    >

    <link
        rel="apple-touch-icon"
        sizes="180x180"
        href="{{ asset('images/apple-touch-icon.png') }}"
    >


    {{-- ========================================================= --}}
    {{-- RÉGIE PUBLICITAIRE --}}
    {{-- ========================================================= --}}

    @if(config('services.adsense.publisher_id'))
        <script
            async
            src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ config('services.adsense.publisher_id') }}"
            crossorigin="anonymous"
        ></script>
    @endif


    {{-- ========================================================= --}}
    {{-- POLICES --}}
    {{-- ========================================================= --}}

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700;800&display=swap"
        rel="stylesheet"
    >


    {{-- ========================================================= --}}
    {{-- CSS --}}
    {{-- ========================================================= --}}

    <link
        rel="stylesheet"
        href="{{ url('/css/footer.css') }}"
    >

    <link
        rel="stylesheet"
        href="{{ url('/css/mobile.css') }}"
    >


    <style>

        :root {
            --primary: #d8a922;
            --primary-dark: #a87b12;
            --black: #080808;
            --black-light: #191919;
            --text: #1c1a17;
            --muted: #706b61;
            --background: #f7f5ef;
            --surface: #ffffff;
            --border: #e9e3d8;
            --radius: 14px;
            --shadow:
                0 14px 40px rgba(30, 24, 12, 0.1);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            overflow-x: hidden;
            color: var(--text);
            background: var(--background);
            font-family: "DM Sans", Arial, sans-serif;
            line-height: 1.65;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        img {
            display: block;
            max-width: 100%;
            height: auto;
        }

        /* ---------- En-tête : recherche + soutien ---------- */
        .masthead-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 0 1 auto;
        }

        /* ---------- Bouton de soutien ---------- */
        .nav-support {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex: none;
            padding: 11px 20px;
            border-radius: 999px;
            background: var(--primary);
            color: var(--black);
            font-weight: 700;
            font-size: .88rem;
            white-space: nowrap;
        }
        .nav-support:hover { background: var(--primary-dark); color: #fff; }

        /* ---------- Retour en haut ---------- */
        .back-to-top {
            position: fixed;
            right: 18px;
            bottom: 18px;
            z-index: 60;
            width: 46px;
            height: 46px;
            border: 0;
            border-radius: 50%;
            background: var(--black);
            color: var(--primary);
            font-size: 1.3rem;
            line-height: 1;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .28);
        }
        .back-to-top:hover { background: var(--black-light); }
        .back-to-top[hidden] { display: none; }

        @media (max-width: 680px) {
            .back-to-top { right: 12px; bottom: 12px; width: 42px; height: 42px; }
        }

        /* ---------- Emplacements publicitaires ---------- */
        .ad-slot {
            position: relative;
            margin: 24px auto;
            text-align: center;
            max-width: 100%;
            overflow: hidden;
        }
        .ad-slot img {
            margin: 0 auto;
            border-radius: 8px;
        }
        .ad-slot__label {
            display: block;
            margin-top: 4px;
            font-size: .68rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--muted);
            opacity: .75;
        }
        .ad-slot--in_article { margin: 32px auto; }
        .ad-slot--sidebar_top,
        .ad-slot--sidebar_bottom { margin: 0 0 24px; }

        button,
        input,
        textarea,
        select {
            font: inherit;
        }

        button {
            cursor: pointer;
        }

        .container {
            width: min(1220px, calc(100% - 40px));
            margin-inline: auto;
        }


        /*
        |--------------------------------------------------------------------------
        | Barre supérieure
        |--------------------------------------------------------------------------
        */

        .topbar {
            color: #e8dfcc;
            background: var(--black);
            border-bottom:
                1px solid rgba(255, 255, 255, 0.1);
        }

        .topbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 38px;
            gap: 20px;
            font-size: 13px;
        }

        .topbar-message {
            color: var(--primary);
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }


        /*
        |--------------------------------------------------------------------------
        | En-tête
        |--------------------------------------------------------------------------
        */

        .masthead {
            color: var(--text);
            background: white;
            border-bottom: 1px solid var(--border);
        }

        .masthead-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 132px;
            gap: 40px;
        }

        .brand-logo {
            display: inline-flex;
            flex-shrink: 0;
            align-items: center;
            width: min(390px, 100%);
        }

        .brand-logo img {
            width: 100%;
            max-height: 112px;
            object-fit: contain;
        }


        /*
        |--------------------------------------------------------------------------
        | Recherche
        |--------------------------------------------------------------------------
        */

        .search-form {
            display: flex;
            width: min(420px, 100%);
            padding: 5px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: #f8f6f1;
        }

        .search-form:focus-within {
            border-color: var(--primary);
            box-shadow:
                0 0 0 3px rgba(216, 169, 34, 0.15);
        }

        .search-form input {
            flex: 1;
            min-width: 0;
            padding: 10px 15px;
            color: var(--text);
            border: 0;
            outline: 0;
            background: transparent;
        }

        .search-form input::placeholder {
            color: var(--muted);
        }

        .search-form button {
            flex-shrink: 0;
            padding: 10px 20px;
            color: #17120a;
            border: 0;
            border-radius: 999px;
            background: var(--primary);
            font-weight: 700;
            transition:
                color 0.2s ease,
                background 0.2s ease,
                transform 0.2s ease;
        }

        .search-form button:hover {
            color: white;
            background: var(--primary-dark);
            transform: translateY(-1px);
        }


        /*
        |--------------------------------------------------------------------------
        | Navigation principale
        |--------------------------------------------------------------------------
        */

        .navigation {
            position: sticky;
            z-index: 100;
            top: 0;
            color: white;
            background: rgba(8, 8, 8, 0.98);
            box-shadow:
                0 8px 24px rgba(8, 8, 8, 0.14);
            backdrop-filter: blur(12px);
        }

        .navigation-inner {
            display: flex;
            align-items: center;
            min-height: 56px;
            gap: 4px;
        }


        /*
        |--------------------------------------------------------------------------
        | Accueil
        |--------------------------------------------------------------------------
        */

        /*
         * Tuile d'accueil : repère fixe à gauche de la barre, comme dans
         * la plupart des sites d'information.
         */
        .home-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 56px;
            min-height: 56px;
            background: var(--primary);
            color: var(--black);
        }

        .home-link:hover { background: var(--primary-dark); color: #fff; }

        .home-link svg { display: block; }


        /*
        |--------------------------------------------------------------------------
        | Conteneur des rubriques
        |--------------------------------------------------------------------------
        */

        .navigation-menu {
            display: flex;
            align-items: center;
            flex: 1;
            min-width: 0;
            min-height: 56px;
            gap: 2px;
        }


        /*
        |--------------------------------------------------------------------------
        | Rubrique principale
        |--------------------------------------------------------------------------
        */

        .nav-item {
            position: relative;
            display: flex;
            align-items: center;
            flex-shrink: 0;
            min-height: 56px;
        }

        .nav-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 56px;
            padding: 0 12px;
            color: white;
            font-size: 14px;
            font-weight: 700;
            white-space: nowrap;
            transition:
                color 0.2s ease,
                background 0.2s ease;
        }

        .nav-link::after {
            position: absolute;
            right: 12px;
            bottom: 0;
            left: 12px;
            height: 3px;
            background: var(--primary);
            content: "";
            transform: scaleX(0);
            transform-origin: center;
            transition: transform 0.2s ease;
        }

        .nav-item:hover > .nav-link,
        .nav-item:focus-within > .nav-link {
            color: var(--primary);
            background: rgba(255, 255, 255, 0.04);
        }

        .nav-item:hover > .nav-link::after,
        .nav-item:focus-within > .nav-link::after {
            transform: scaleX(1);
        }


        /*
        |--------------------------------------------------------------------------
        | Flèche
        |--------------------------------------------------------------------------
        */

        .nav-arrow {
            display: inline-block;
            color: var(--primary);
            font-size: 9px;
            line-height: 1;
            transition: transform 0.2s ease;
        }

        .nav-item:hover .nav-arrow,
        .nav-item:focus-within .nav-arrow {
            transform: rotate(180deg);
        }


        /*
        |--------------------------------------------------------------------------
        | Sous-menu
        |--------------------------------------------------------------------------
        */

        .nav-dropdown {
            position: absolute;
            z-index: 500;
            top: 100%;
            left: 0;
            width: max-content;
            min-width: 220px;
            max-width: 320px;
            padding: 7px 0;
            visibility: hidden;
            opacity: 0;
            border-top: 3px solid var(--primary);
            border-radius: 0 0 12px 12px;
            background: #111111;
            box-shadow:
                0 18px 45px rgba(0, 0, 0, 0.32);
            transform: translateY(8px);
            transition:
                visibility 0.18s ease,
                opacity 0.18s ease,
                transform 0.18s ease;
        }

        .nav-item:hover > .nav-dropdown,
        .nav-item:focus-within > .nav-dropdown {
            visibility: visible;
            opacity: 1;
            transform: translateY(0);
        }

        .nav-dropdown-link {
            display: flex;
            align-items: center;
            min-height: 42px;
            padding: 9px 17px;
            color: #eeeeee;
            border-bottom:
                1px solid rgba(255, 255, 255, 0.06);
            font-size: 13px;
            font-weight: 600;
            line-height: 1.3;
            white-space: nowrap;
            transition:
                color 0.2s ease,
                background 0.2s ease,
                padding-left 0.2s ease;
        }

        .nav-dropdown-link:last-child {
            border-bottom: 0;
        }

        .nav-dropdown-link:hover {
            padding-left: 22px;
            color: var(--primary);
            background:
                rgba(255, 255, 255, 0.06);
        }


        /*
        |--------------------------------------------------------------------------
        | Dernière minute
        |--------------------------------------------------------------------------
        */

        .breaking-news {
            color: white;
            background: var(--black-light);
            border-top:
                1px solid rgba(255, 255, 255, 0.08);
        }

        .breaking-news-inner {
            display: flex;
            align-items: center;
            min-height: 46px;
            overflow: hidden;
        }

        .breaking-label {
            align-self: stretch;
            display: inline-flex;
            align-items: center;
            flex-shrink: 0;
            padding: 0 18px;
            color: var(--black);
            background: var(--primary);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .breaking-content {
            display: flex;
            align-items: center;
            min-width: 0;
            padding: 0 18px;
            overflow: hidden;
        }

        .breaking-content a {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 14px;
            font-weight: 600;
        }

        .breaking-content a:hover {
            color: var(--primary);
        }

        .breaking-time {
            flex-shrink: 0;
            margin-right: 12px;
            color: var(--primary);
            font-size: 12px;
            font-weight: 700;
        }


        /*
        |--------------------------------------------------------------------------
        | Contenu général
        |--------------------------------------------------------------------------
        */

        main {
            min-height: 70vh;
            padding: 44px 0 76px;
        }

        .section-heading {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 25px;
        }

        .section-title {
            margin: 0;
            font-family:
                "Playfair Display",
                Georgia,
                serif;
            font-size: clamp(28px, 4vw, 39px);
            line-height: 1.15;
            letter-spacing: -0.025em;
        }

        .section-title::before {
            display: inline-block;
            width: 7px;
            height: 28px;
            margin-right: 12px;
            border-radius: 999px;
            background: var(--primary);
            content: "";
        }

        .section-link {
            color: var(--primary-dark);
            font-size: 14px;
            font-weight: 700;
        }

        .section-link:hover {
            color: var(--black);
        }


        /*
        |--------------------------------------------------------------------------
        | Grande une
        |--------------------------------------------------------------------------
        */

        .hero-grid {
            display: grid;
            grid-template-columns:
                minmax(0, 1.65fr)
                minmax(280px, 0.75fr);
            gap: 24px;
            margin-bottom: 64px;
        }

        .hero-card,
        .side-story,
        .article-card,
        .card {
            position: relative;
            overflow: hidden;
            border-radius: var(--radius);
            background: var(--surface);
        }

        .hero-card {
            min-height: 530px;
            box-shadow: var(--shadow);
        }

        .hero-card img,
        .hero-main-image {
            width: 100%;
            height: 530px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .hero-card:hover img {
            transform: scale(1.025);
        }

        .image-placeholder {
            width: 100%;
            height: 100%;
            min-height: 220px;
            background:
                radial-gradient(
                    circle at 75% 25%,
                    rgba(216, 169, 34, 0.75),
                    transparent 32%
                ),
                linear-gradient(
                    135deg,
                    #3a2a0b,
                    #080808 70%
                );
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: flex-end;
            padding: clamp(24px, 5vw, 46px);
            color: white;
            background:
                linear-gradient(
                    to top,
                    rgba(5, 5, 5, 0.97),
                    rgba(5, 5, 5, 0.08) 75%
                );
        }

        .hero-content {
            max-width: 780px;
        }

        .hero-title {
            margin: 12px 0;
            font-family:
                "Playfair Display",
                Georgia,
                serif;
            font-size: clamp(32px, 5vw, 54px);
            line-height: 1.06;
            letter-spacing: -0.035em;
        }

        .hero-title a:hover {
            color: #f4d77f;
        }

        .hero-excerpt {
            max-width: 680px;
            margin: 0 0 18px;
            color: #eee9df;
            font-size: 17px;
        }


        /*
        |--------------------------------------------------------------------------
        | Articles secondaires
        |--------------------------------------------------------------------------
        */

        .side-stories {
            display: grid;
            gap: 18px;
        }

        .side-story {
            display: grid;
            grid-template-columns:
                120px minmax(0, 1fr);
            min-height: 120px;
            border: 1px solid var(--border);
            box-shadow:
                0 7px 25px rgba(30, 24, 12, 0.06);
            transition: transform 0.2s ease;
        }

        .side-story:hover {
            transform: translateY(-3px);
        }

        .side-story img,
        .side-story .image-placeholder {
            width: 120px;
            height: 100%;
            min-height: 120px;
            object-fit: cover;
        }

        .side-story-content {
            padding: 15px;
        }

        .side-story h3 {
            margin: 6px 0 10px;
            font-family:
                "Playfair Display",
                Georgia,
                serif;
            font-size: 18px;
            line-height: 1.25;
        }

        .side-story h3 a:hover {
            color: var(--primary-dark);
        }


        /*
        |--------------------------------------------------------------------------
        | Grilles et cartes
        |--------------------------------------------------------------------------
        */

        .articles-grid,
        .grid {
            display: grid;
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
            gap: 27px;
        }

        .article-card,
        .card {
            border: 1px solid var(--border);
            box-shadow:
                0 8px 28px rgba(30, 24, 12, 0.05);
            transition:
                transform 0.25s ease,
                box-shadow 0.25s ease;
        }

        .article-card:hover,
        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow);
        }

        .article-card img,
        .card-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }

        .article-card-content,
        .card-content {
            padding: 21px;
        }

        .article-card h3,
        .card h3 {
            margin: 8px 0 12px;
            font-family:
                "Playfair Display",
                Georgia,
                serif;
            font-size: 22px;
            line-height: 1.24;
        }

        .article-card h3 a:hover,
        .card h3 a:hover {
            color: var(--primary-dark);
        }

        .article-card p,
        .card p {
            color: var(--muted);
        }

        .category {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: var(--primary-dark);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .category::before {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: currentColor;
            content: "";
        }

        .meta {
            color: var(--muted);
            font-size: 13px;
        }


        /*
        |--------------------------------------------------------------------------
        | Article
        |--------------------------------------------------------------------------
        */

        .article {
            max-width: 850px;
            margin-inline: auto;
        }

        .article h1 {
            margin: 14px 0 20px;
            font-family:
                "Playfair Display",
                Georgia,
                serif;
            font-size: clamp(38px, 6vw, 66px);
            line-height: 1.04;
            letter-spacing: -0.04em;
        }

        .article-excerpt {
            color: #575044;
            font-family:
                "Playfair Display",
                Georgia,
                serif;
            font-size: 22px;
            line-height: 1.5;
        }

        .article-image {
            width: 100%;
            max-height: 560px;
            margin-top: 32px;
            border-radius: var(--radius);
            object-fit: cover;
        }

        .article-body {
            margin-top: 38px;
            color: #35312b;
            font-family: Georgia, serif;
            font-size: 19px;
            line-height: 1.9;
            overflow-wrap: anywhere;
        }

        .article-body img {
            width: auto;
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .article-body a {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .article-body blockquote {
            margin: 32px 0;
            padding: 8px 0 8px 24px;
            color: #514728;
            border-left:
                5px solid var(--primary);
            font-size: 22px;
            font-style: italic;
        }

        .article-body iframe,
        .article-body video {
            max-width: 100%;
        }


        /*
        |--------------------------------------------------------------------------
        | État vide
        |--------------------------------------------------------------------------
        */

        .empty-state {
            padding: 65px 30px;
            text-align: center;
            border: 1px dashed #cfc5b0;
            border-radius: var(--radius);
            background: white;
        }

        .empty-state h2 {
            margin-top: 0;
            font-family:
                "Playfair Display",
                Georgia,
                serif;
        }


        /*
        |--------------------------------------------------------------------------
        | Accessibilité
        |--------------------------------------------------------------------------
        */

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }


        /*
        |--------------------------------------------------------------------------
        | Tablettes
        |--------------------------------------------------------------------------
        */

        @media (max-width: 940px) {

            .hero-grid {
                grid-template-columns: 1fr;
            }

            .side-stories {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }

            .articles-grid,
            .grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Navigation intermédiaire
        |--------------------------------------------------------------------------
        */

        @media (max-width: 1150px) {

            .navigation-inner {
                overflow-x: auto;
                overflow-y: hidden;
                scrollbar-width: none;
            }

            .navigation-inner::-webkit-scrollbar {
                display: none;
            }

            /*
             * Le menu déroulant est désactivé lorsque
             * la navigation devient horizontale.
             */

            .nav-dropdown {
                display: none;
            }

            .nav-arrow {
                display: none;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Mobile
        |--------------------------------------------------------------------------
        */

        @media (max-width: 680px) {

            .container {
                width: min(100% - 24px, 1220px);
            }

            .topbar-message {
                display: none;
            }

            .masthead-inner {
                align-items: stretch;
                flex-direction: column;
                min-height: auto;
                padding: 18px 0;
                gap: 16px;
            }

            .brand-logo {
                width: min(280px, 85%);
                margin-inline: auto;
            }

            /*
             * Sur mobile, la recherche et le bouton restent sur une même
             * ligne : le bouton ne doit pas manger toute la largeur.
             */
            .masthead-actions {
                gap: 8px;
            }

            .search-form {
                flex: 1;
                min-width: 0;
            }

            .nav-support {
                padding: 11px 14px;
                font-size: .82rem;
            }

            .brand-logo img {
                max-height: 86px;
            }

            .search-form {
                width: 100%;
            }

            .search-form input {
                min-height: 46px;
                font-size: 16px;
            }

            .search-form button {
                min-height: 46px;
            }

            .navigation {
                position: relative;
            }

            .navigation-inner {
                min-height: 50px;
                gap: 2px;
                overflow-x: auto;
                overflow-y: hidden;
            }

            .home-link {
                min-height: 50px;
                padding-inline: 10px;
            }

            .navigation-menu {
                width: max-content;
                min-height: 50px;
            }

            .nav-item {
                min-height: 50px;
            }

            .nav-link {
                min-height: 50px;
                padding-inline: 11px;
                font-size: 13px;
            }

            .nav-dropdown,
            .nav-arrow {
                display: none;
            }

            .breaking-label {
                padding-inline: 11px;
                font-size: 10px;
            }

            .breaking-content {
                padding-inline: 12px;
            }

            .breaking-time {
                display: none;
            }

            main {
                padding: 30px 0 52px;
            }

            .section-heading {
                align-items: flex-start;
                flex-direction: column;
                gap: 8px;
            }

            .hero-card,
            .hero-card img,
            .hero-main-image {
                height: 440px;
                min-height: 440px;
            }

            .hero-overlay {
                padding: 25px 20px;
            }

            .hero-excerpt {
                display: none;
            }

            .side-stories,
            .articles-grid,
            .grid {
                grid-template-columns: 1fr;
            }

            .article h1 {
                font-size: clamp(34px, 10vw, 48px);
            }

            .article-excerpt {
                font-size: 19px;
            }

            .article-image {
                width: calc(100% + 24px);
                max-width: none;
                margin-left: -12px;
                border-radius: 0;
            }

            .article-body {
                font-size: 18px;
                line-height: 1.8;
            }
        }


        @media (max-width: 390px) {

            .container {
                width: calc(100% - 18px);
            }

            .brand-logo {
                width: 245px;
            }

            .search-form button {
                padding-inline: 12px;
                font-size: 13px;
            }

            .hero-card,
            .hero-card img,
            .hero-main-image {
                height: 420px;
                min-height: 420px;
            }

            .hero-title {
                font-size: 29px;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Réduction des animations
        |--------------------------------------------------------------------------
        */

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
            }
        }

    </style>


    @stack('styles')

</head>


<body>


    {{-- ========================================================= --}}
    {{-- BARRE SUPÉRIEURE --}}
    {{-- ========================================================= --}}

    <div class="topbar">

        <div class="container topbar-inner">

            <span>
                {{ now('America/Port-au-Prince')
                    ->translatedFormat('l d F Y') }}
            </span>

            <span class="topbar-message">
                Le citoyen au cœur de l’information
            </span>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- EN-TÊTE --}}
    {{-- ========================================================= --}}

    <header class="masthead">

        <div class="container masthead-inner">

            <a
                href="{{ route('home') }}"
                class="brand-logo"
                aria-label="Accueil de Lambi News"
            >

                <img
                    src="{{ asset('images/lambinews-logo.jpg') }}"
                    alt="Lambi News — Le citoyen au cœur de l’information"
                    width="595"
                    height="336"
                    loading="eager"
                    decoding="async"
                    fetchpriority="high"
                >

            </a>


            {{-- ================================================= --}}
            {{-- RECHERCHE --}}
            {{-- ================================================= --}}

            <div class="masthead-actions">

            <form
                action="{{ route('search') }}"
                method="GET"
                class="search-form"
                role="search"
            >

                <label
                    for="site-search"
                    class="sr-only"
                >
                    Rechercher dans Lambi News
                </label>

                <input
                    id="site-search"
                    name="q"
                    type="search"
                    value="{{ request('q') }}"
                    placeholder="Rechercher dans l’actualité…"
                    maxlength="100"
                >

                <button type="submit">
                    Rechercher
                </button>

            </form>

            <a href="{{ route('donations.create') }}" class="nav-support">
                <span aria-hidden="true">♥</span> Soutenir
            </a>

            </div>

        </div>

    </header>


    {{-- ========================================================= --}}
    {{-- NAVIGATION --}}
    {{-- ========================================================= --}}

    <nav
        class="navigation"
        aria-label="Navigation principale"
    >

        <div class="container navigation-inner">


            {{-- ================================================= --}}
            {{-- ACCUEIL --}}
            {{-- ================================================= --}}

            <a
                href="{{ route('home') }}"
                class="home-link"
                aria-label="Accueil"
            >
                <svg viewBox="0 0 24 24" width="19" height="19" aria-hidden="true">
                    <path fill="currentColor"
                          d="M12 3.2 2.6 11h2.6v9.2h5.1v-5.6h3.4v5.6h5.1V11h2.6L12 3.2Z"/>
                </svg>
            </a>


            {{-- ================================================= --}}
            {{-- RUBRIQUES --}}
            {{-- ================================================= --}}

            <div class="navigation-menu">

                @php
                    /*
                     * Huit rubriques au plus dans la barre : au-delà elle
                     * déborde et devient illisible sur un portable. Le reste
                     * va sous « Plus », avec les pages de service.
                     */
                    $navPrimary = collect($navigationCategories ?? [])->take(8);
                    $navOverflow = collect($navigationCategories ?? [])->slice(8);
                @endphp

                @foreach($navPrimary as $navigationCategory)

                    <div class="nav-item">


                        {{-- ===================================== --}}
                        {{-- RUBRIQUE PRINCIPALE --}}
                        {{-- ===================================== --}}

                        <a
                            href="{{ route(
                                'categories.show',
                                $navigationCategory->slug
                            ) }}"
                            class="nav-link"
                        >

                            {{ $navigationCategory->name }}


                            {{-- Flèche uniquement si sous-rubriques --}}

                            @if(
                                $navigationCategory->children
                                && $navigationCategory->children->isNotEmpty()
                            )

                                <span
                                    class="nav-arrow"
                                    aria-hidden="true"
                                >
                                    ▼
                                </span>

                            @endif

                        </a>


                        {{-- ===================================== --}}
                        {{-- SOUS-RUBRIQUES --}}
                        {{-- ===================================== --}}

                        @if(
                            $navigationCategory->children
                            && $navigationCategory->children->isNotEmpty()
                        )

                            <div class="nav-dropdown">

                                @foreach(
                                    $navigationCategory->children
                                    as $child
                                )

                                    <a
                                        href="{{ route(
                                            'categories.show',
                                            $child->slug
                                        ) }}"
                                        class="nav-dropdown-link"
                                    >

                                        {{ $child->name }}

                                    </a>

                                @endforeach

                            </div>

                        @endif

                    </div>

                @endforeach

                {{-- Rubriques restantes et pages de service. --}}
                <div class="nav-item">
                    <a href="#" class="nav-link" aria-haspopup="true">
                        Plus
                        <span class="nav-arrow" aria-hidden="true">▾</span>
                    </a>

                    <div class="nav-dropdown">
                        @foreach($navOverflow as $extra)
                            <a href="{{ route('categories.show', $extra->slug) }}"
                               class="nav-dropdown-link">{{ $extra->name }}</a>
                        @endforeach

                        <a href="{{ route('announcements.index') }}" class="nav-dropdown-link">Annonces</a>
                        <a href="{{ route('polls.index') }}" class="nav-dropdown-link">Sondages</a>
                        <a href="{{ route('fundraisers.index') }}" class="nav-dropdown-link">Kanpay finansman</a>
                        <a href="{{ route('donations.create') }}" class="nav-dropdown-link">Soutenir</a>
                        <a href="{{ route('media-kit') }}" class="nav-dropdown-link">Annoncer chez nous</a>
                        <a href="{{ route('contact.create') }}" class="nav-dropdown-link">Contact</a>
                    </div>
                </div>

            </div>

        </div>

    </nav>


    {{-- ========================================================= --}}
    {{-- DERNIÈRE MINUTE --}}
    {{-- ========================================================= --}}

    @if(
        ($breakingArticles ?? collect())
            ->isNotEmpty()
    )

        @php

            $breakingArticle =
                $breakingArticles->first();

        @endphp

        <aside
            class="breaking-news"
            aria-label="Dernière minute"
        >

            <div class="container breaking-news-inner">

                <span class="breaking-label">
                    Dernière minute
                </span>


                <div class="breaking-content">


                    {{-- Heure Haïti --}}

                    <span class="breaking-time">

                        {{ $breakingArticle
                            ->published_at
                            ?->timezone('America/Port-au-Prince')
                            ->format('H:i')
                        }}

                    </span>


                    {{-- Article --}}

                    <a
                        href="{{ route(
                            'articles.show',
                            $breakingArticle->slug
                        ) }}"
                    >

                        {{ $breakingArticle->title }}

                    </a>

                </div>

            </div>

        </aside>

    @endif


    {{-- ========================================================= --}}
    {{-- CONTENU --}}
    {{-- ========================================================= --}}

    <main>

        <div class="container">

            <x-ad-slot position="header" />

            @yield('content')

            <x-ad-slot position="footer" />

        </div>

    </main>


    {{-- ========================================================= --}}
    {{-- FOOTER --}}
    {{-- ========================================================= --}}

    @include('front.partials.footer')


    {{-- ========================================================= --}}
    {{-- SCRIPTS --}}
    {{-- ========================================================= --}}

    {{-- ========================================================= --}}
    {{-- RETOUR EN HAUT --}}
    {{-- ========================================================= --}}

    <button
        type="button"
        id="back-to-top"
        class="back-to-top"
        aria-label="Retounen anlè paj la"
        hidden
    >
        <span aria-hidden="true">↑</span>
    </button>

    <script>
    (function () {
        var btn = document.getElementById('back-to-top');
        if (!btn) { return; }

        // Le bouton n'apparaît qu'une fois la page réellement parcourue.
        function toggle() {
            btn.hidden = window.scrollY < 480;
        }

        window.addEventListener('scroll', toggle, { passive: true });
        toggle();

        btn.addEventListener('click', function () {
            // Respecte le réglage « animations réduites » du système.
            var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
        });
    })();
    </script>

    @stack('scripts')

</body>

</html>