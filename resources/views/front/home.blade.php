@extends('front.layouts.app')

@section(
    'title',
    'Lambi News — Le citoyen au cœur de l’information'
)

@section(
    'meta_description',
    'Retrouvez toute l’actualité nationale et internationale sur Lambi News.'
)

@php
    /*
     * Le grand cadre est un carrousel : les articles en vedette y
     * défilent. Les trois suivants alimentent la colonne de droite,
     * sans doublon avec le carrousel.
     */
    $slides = $featuredArticles->take(5);
    $sideUp = $latestArticles
        ->reject(fn ($a) => $slides->contains('id', $a->id))
        ->take(3);

    // Huit rubriques au plus dans la grille du bas.
    $sections = $categorySections->take(8);

    // Icônes tracées en SVG : aucun fichier ni police à charger.
    $icons = [
        'fb' => 'M13.5 21v-8h2.7l.4-3.1h-3.1V7.9c0-.9.25-1.5 1.55-1.5h1.65V3.6A22 22 0 0 0 14.3 3.5c-2.4 0-4 1.45-4 4.1v2.3H7.6V13h2.7v8h3.2Z',
        'x'  => 'M17.7 3h3.3l-7.2 8.2L22 21h-6.4l-5-6.1L4.8 21H1.5l7.7-8.8L2 3h6.6l4.5 5.6L17.7 3Zm-1.2 16h1.8L7.6 4.9H5.7L16.5 19Z',
        'yt' => 'M21.6 7.2s-.2-1.4-.8-2c-.75-.8-1.6-.8-2-.85C16 4.2 12 4.2 12 4.2h-.02s-4 0-6.8.2c-.4.05-1.25.05-2 .85-.6.6-.8 2-.8 2S2.2 8.8 2.2 10.5v1.6c0 1.6.2 3.3.2 3.3s.2 1.4.8 2c.75.8 1.75.78 2.2.86 1.6.15 6.8.2 6.8.2s4 0 6.8-.22c.4-.05 1.25-.05 2-.85.6-.6.8-2 .8-2s.2-1.65.2-3.3v-1.6c0-1.65-.2-3.3-.2-3.3ZM10 14.2V8.6l5.15 2.82L10 14.2Z',
        'ig' => 'M12 2.2c3.2 0 3.6 0 4.85.07 1.17.05 1.8.25 2.23.42.56.22.96.48 1.38.9.42.42.68.82.9 1.38.17.42.37 1.06.42 2.23.06 1.26.07 1.64.07 4.83s0 3.57-.07 4.83c-.05 1.17-.25 1.8-.42 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.17-1.06.37-2.23.42-1.26.06-1.64.07-4.85.07s-3.6 0-4.85-.07c-1.17-.05-1.8-.25-2.23-.42-.56-.22-.96-.48-1.38-.9-.42-.42-.68-.82-.9-1.38-.17-.42-.37-1.06-.42-2.23C2.2 15.6 2.2 15.2 2.2 12s0-3.57.07-4.83c.05-1.17.25-1.8.42-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.17 1.06-.37 2.23-.42C8.4 2.2 8.8 2.2 12 2.2Zm0 5.16a4.64 4.64 0 1 0 0 9.28 4.64 4.64 0 0 0 0-9.28Zm0 7.65a3.01 3.01 0 1 1 0-6.02 3.01 3.01 0 0 1 0 6.02Zm5.9-7.83a1.08 1.08 0 1 1-2.17 0 1.08 1.08 0 0 1 2.17 0Z',
        'tg' => 'M21.7 4.3c-.3-.25-.75-.3-1.2-.13L2.9 11c-.5.2-.8.6-.78 1.05.03.45.36.83.87.97l4.4 1.25 1.7 5.2c.1.32.36.55.7.6h.14c.3 0 .58-.14.76-.38l2.5-3.3 4.4 3.24c.2.15.44.23.68.23.13 0 .26-.02.38-.07.36-.14.62-.45.7-.83l3-13.6c.1-.44-.05-.87-.4-1.13ZM9.9 13.7l-.55 3.1-1.02-3.1 8-5.3-6.43 5.3Z',
    ];

    $socials = collect([
        ['url' => \App\Models\SiteSetting::get('facebook_url'),  'label' => 'Facebook',  'class' => 'fb'],
        ['url' => \App\Models\SiteSetting::get('twitter_handle') ? 'https://x.com/'.ltrim(\App\Models\SiteSetting::get('twitter_handle'), '@') : '', 'label' => 'X', 'class' => 'x'],
        ['url' => \App\Models\SiteSetting::get('youtube_url'),   'label' => 'YouTube',   'class' => 'yt'],
        ['url' => \App\Models\SiteSetting::get('instagram_url'), 'label' => 'Instagram', 'class' => 'ig'],
        ['url' => \App\Models\SiteSetting::get('telegram_url'),  'label' => 'Telegram',  'class' => 'tg'],
    ])->map(fn ($s) => $s + ['path' => $icons[$s['class']]])->all();
@endphp

@push('styles')
<style>
    .hp { padding: 20px 0 60px; }

    /* ---------------- Flash info ---------------- */
    .hp__flash {
        display: flex; align-items: stretch; gap: 0;
        background: var(--surface); border: 1px solid var(--border);
        border-radius: 10px; overflow: hidden; margin-bottom: 20px;
    }
    .hp__flashtag {
        display: flex; align-items: center; gap: 7px;
        padding: 11px 18px 11px 15px;
        background: #e11d2e; color: #fff;
        font-size: .76rem; font-weight: 800; letter-spacing: .06em;
        text-transform: uppercase; white-space: nowrap;
        clip-path: polygon(0 0, 100% 0, calc(100% - 12px) 100%, 0 100%);
        padding-right: 26px;
    }
    .hp__flashitems {
        flex: 1; min-width: 0; display: flex; align-items: center;
        gap: 0; overflow: hidden;
    }
    .hp__flashitem {
        display: none; align-items: center; gap: 10px;
        padding: 0 16px; font-size: .88rem; white-space: nowrap;
        overflow: hidden; text-overflow: ellipsis;
    }
    .hp__flashitem.is-on { display: flex; }
    .hp__flashitem::after { content: "›"; color: var(--muted); }
    .hp__flashnav { display: flex; align-items: center; border-left: 1px solid var(--border); }
    .hp__flashbtn {
        width: 34px; height: 100%; min-height: 40px; border: 0; cursor: pointer;
        background: transparent; color: var(--muted); font-size: 1rem;
    }
    .hp__flashbtn:hover { color: var(--black); background: var(--background); }

    /* ---------------- Disposition ---------------- */
    .hp__grid {
        display: grid; grid-template-columns: minmax(0, 1fr) 306px; gap: 22px;
        align-items: start;
    }
    .hp__lead { display: grid; grid-template-columns: 1.22fr 1fr; gap: 16px; }

    /* ---------------- Article vedette ---------------- */
    .hp__hero {
        position: relative; border-radius: 12px;
        overflow: hidden; min-height: 430px; background: #12161f;
    }
    /* Chaque diapositive occupe tout le cadre ; une seule est visible. */
    .hp__slide {
        position: absolute; inset: 0; display: none;
        flex-direction: column; justify-content: flex-end; color: #fff;
    }
    .hp__slide.is-on { display: flex; }
    .hp__slide img {
        position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;
    }
    .hp__slide::after {
        content: ""; position: absolute; inset: 0;
        background: linear-gradient(to top, rgba(6,10,18,.94) 8%, rgba(6,10,18,.72) 42%, rgba(6,10,18,.12) 100%);
    }
    .hp__arrow {
        position: absolute; top: 50%; z-index: 4; transform: translateY(-50%);
        width: 38px; height: 38px; border: 0; border-radius: 50%; cursor: pointer;
        background: rgba(10,14,22,.55); color: #fff; font-size: 1.3rem; line-height: 1;
    }
    .hp__arrow:hover { background: rgba(10,14,22,.85); }
    .hp__arrow--prev { left: 12px; }
    .hp__arrow--next { right: 12px; }
    .hp__dots {
        position: absolute; right: 26px; bottom: 18px; z-index: 4;
        display: flex; gap: 7px;
    }
    .hp__dot {
        width: 9px; height: 9px; padding: 0; border: 0; border-radius: 50%;
        background: rgba(255,255,255,.42); cursor: pointer;
    }
    .hp__dot.is-on { background: var(--primary); width: 22px; border-radius: 999px; }
    .hp__herobody { display: block; position: relative; z-index: 2; padding: 26px; }
    .hp__tag {
        display: inline-block; vertical-align: top; padding: 4px 11px; border-radius: 4px;
        background: var(--primary); color: var(--black);
        font-size: .68rem; font-weight: 800; letter-spacing: .06em;
        text-transform: uppercase; margin-bottom: 11px;
    }
    .hp__herotitle {
        display: block;
        font-family: "Playfair Display", Georgia, serif;
        font-size: clamp(1.4rem, 2.7vw, 2rem); line-height: 1.22; margin: 0 0 10px;
    }
    .hp__heroexcerpt {
        display: block; margin: 0 0 14px; color: rgba(255,255,255,.82);
        font-size: .93rem; line-height: 1.6;
    }
    .hp__meta {
        display: flex; flex-wrap: wrap; gap: 16px;
        font-size: .78rem; color: rgba(255,255,255,.7);
    }

    /* ---------------- Cartes empilées ---------------- */
    .hp__stack { display: grid; grid-template-rows: repeat(3, 1fr); gap: 16px; }
    .hp__card {
        position: relative; display: flex; flex-direction: column;
        justify-content: flex-end; border-radius: 12px; overflow: hidden;
        min-height: 132px; color: #fff; background: #12161f;
    }
    .hp__card img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .hp__card::after {
        content: ""; position: absolute; inset: 0;
        background: linear-gradient(to top, rgba(6,10,18,.93) 12%, rgba(6,10,18,.35) 78%);
    }
    .hp__cardbody { display: block; position: relative; z-index: 2; padding: 14px; }
    .hp__cardtitle {
        display: block;
        font-size: .95rem; font-weight: 700; line-height: 1.32; margin: 0 0 7px;
    }

    /* ---------------- Colonne de droite ---------------- */
    .hp__panel {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: 12px; overflow: hidden; margin-bottom: 18px;
    }
    .hp__panel--dark { background: #0d1320; border-color: #0d1320; color: #fff; }
    .hp__paneltitle {
        display: flex; align-items: center; gap: 9px;
        margin: 0; padding: 15px 17px;
        font-size: 1rem; font-weight: 800;
        border-bottom: 1px solid var(--border);
    }
    .hp__panel--dark .hp__paneltitle { border-bottom-color: rgba(255,255,255,.12); }
    .hp__panelbody { padding: 15px 17px; }

    .hp__most { display: grid; gap: 13px; padding: 14px 17px; }
    .hp__mostitem { display: grid; grid-template-columns: 62px 1fr; gap: 11px; align-items: start; }
    .hp__mostnum {
        position: relative; border-radius: 8px; overflow: hidden;
        aspect-ratio: 1/1; background: var(--border);
    }
    .hp__mostnum img { width: 100%; height: 100%; object-fit: cover; }
    .hp__mostnum span {
        position: absolute; left: 0; bottom: 0; z-index: 2;
        padding: 1px 7px; background: var(--primary); color: var(--black);
        font-size: .72rem; font-weight: 800;
    }
    .hp__mosttitle { display: block; font-size: .84rem; font-weight: 600; line-height: 1.38; }
    .hp__mostviews { display: block; margin-top: 4px; font-size: .72rem; color: var(--muted); }

    .hp__news input[type=email] {
        width: 100%; padding: 11px 13px; border-radius: 8px; border: 0;
        font: inherit; margin-bottom: 9px;
    }
    .hp__news button {
        width: 100%; padding: 11px; border: 0; border-radius: 8px;
        background: var(--primary); color: var(--black);
        font: inherit; font-weight: 800; cursor: pointer;
    }
    .hp__news button:hover { background: var(--primary-dark); color: #fff; }
    .hp__news p { margin: 0 0 12px; font-size: .86rem; color: rgba(255,255,255,.75); line-height: 1.6; }

    .hp__socials { display: flex; flex-wrap: wrap; gap: 9px; }
    .hp__social {
        width: 38px; height: 38px; border-radius: 9px;
        display: grid; place-items: center; color: #fff;
        font-size: .74rem; font-weight: 800;
    }
    .hp__social.fb { background: #1877f2; }
    .hp__social.x  { background: #000; }
    .hp__social.yt { background: #ff0000; }
    .hp__social.ig { background: linear-gradient(45deg,#f09433,#dc2743,#bc1888); }
    .hp__social.tg { background: #229ed9; }

    .hp__promo {
        display: block; border-radius: 12px; overflow: hidden;
        background: linear-gradient(120deg, #0d1320, #1b3a6b);
        color: #fff; padding: 26px 22px; text-align: center;
    }
    .hp__promo strong {
        display: block; font-family: "Playfair Display", Georgia, serif;
        font-size: 1.25rem; line-height: 1.3; margin-bottom: 6px;
    }
    .hp__promo span { font-size: .86rem; color: rgba(255,255,255,.75); }

    /* ---------------- Grille des rubriques ---------------- */
    .hp__cats {
        display: grid; grid-template-columns: repeat(4, minmax(0,1fr));
        gap: 16px; margin-top: 22px;
    }
    .hp__cat {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: 12px; padding: 14px;
    }
    .hp__cathead {
        display: flex; align-items: center; gap: 8px;
        padding-bottom: 10px; margin-bottom: 12px;
        border-bottom: 2px solid var(--border);
        font-weight: 800; font-size: .95rem;
    }
    .hp__cathead::before {
        content: ""; width: 4px; height: 17px; border-radius: 2px;
        background: var(--primary);
    }
    .hp__catthumb {
        display: block; border-radius: 8px; overflow: hidden;
        aspect-ratio: 16/10; background: var(--border); margin-bottom: 10px;
    }
    .hp__catthumb img { width: 100%; height: 100%; object-fit: cover; }
    .hp__cattitle { display: block; font-size: .88rem; font-weight: 600; line-height: 1.4; }
    .hp__catdate { display: block; margin-top: 7px; font-size: .74rem; color: var(--muted); }

    @media (max-width: 1080px) {
        /*
         * minmax(0, 1fr) et non 1fr : une piste « 1fr » vaut
         * minmax(auto, 1fr) et refuse de descendre sous la largeur
         * minimale de son contenu, ce qui élargit toute la page.
         */
        .hp__grid { grid-template-columns: minmax(0, 1fr); }
        .hp__news--desktop { display: none; }
        .hp__cats { grid-template-columns: repeat(2, minmax(0,1fr)); }
    }
    @media (max-width: 680px) {
        .hp__lead { grid-template-columns: minmax(0, 1fr); }
        .hp__hero { min-height: 300px; }
        .hp__cats { grid-template-columns: repeat(2, minmax(0,1fr)); gap: 12px; }
        .hp__flashtag { padding: 9px 20px 9px 12px; font-size: .7rem; }
    }
</style>
@endpush

@section('content')
<div class="hp">

    {{--
        Pas de bandeau « flash » ici : le gabarit en affiche déjà un sous
        la navigation, sur toutes les pages. En remettre un sur l'accueil
        ferait doublon.
    --}}

    <x-ad-slot position="home_top" />

    <div class="hp__grid">

        <div>
            {{-- ---------------- La une ---------------- --}}
            <div class="hp__lead">

                <div class="hp__hero" id="hero">
                    @foreach($slides as $slide)
                        <a href="{{ route('articles.show', $slide->slug) }}"
                           class="hp__slide{{ $loop->first ? ' is-on' : '' }}">
                            @if($slide->featured_image)
                                <img src="{{ $slide->thumbUrl(800) }}"
                                     alt="{{ $slide->title }}"
                                     loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                     fetchpriority="{{ $loop->first ? 'high' : 'auto' }}"
                                     width="900" height="560" decoding="async">
                            @endif

                            <span class="hp__herobody">
                                @if($slide->category)
                                    <span class="hp__tag">{{ $slide->category->name }}</span>
                                @endif

                                <span class="hp__herotitle">{{ $slide->title }}</span>

                                @if($slide->excerpt)
                                    <span class="hp__heroexcerpt">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($slide->excerpt), 165) }}
                                    </span>
                                @endif

                                <span class="hp__meta">
                                    <span>🗓 {{ $slide->published_at?->translatedFormat('d F Y') }}</span>
                                    @if($slide->views_count)
                                        <span>👁 {{ number_format($slide->views_count, 0, ',', ' ') }} vues</span>
                                    @endif
                                </span>
                            </span>
                        </a>
                    @endforeach

                    @if($slides->count() > 1)
                        <button type="button" class="hp__arrow hp__arrow--prev"
                                data-slide="-1" aria-label="Article précédent">‹</button>
                        <button type="button" class="hp__arrow hp__arrow--next"
                                data-slide="1" aria-label="Article suivant">›</button>

                        <div class="hp__dots">
                            @foreach($slides as $slide)
                                <button type="button"
                                        class="hp__dot{{ $loop->first ? ' is-on' : '' }}"
                                        data-go="{{ $loop->index }}"
                                        aria-label="Article {{ $loop->iteration }}"></button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="hp__stack">
                    @foreach($sideUp as $article)
                        <a href="{{ route('articles.show', $article->slug) }}" class="hp__card">
                            @if($article->featured_image)
                                <img src="{{ $article->thumbUrl(400) }}" alt="{{ $article->title }}"
                                     loading="lazy" width="400" height="250" decoding="async">
                            @endif

                            <span class="hp__cardbody">
                                @if($article->category)
                                    <span class="hp__tag">{{ $article->category->name }}</span>
                                @endif

                                <span class="hp__cardtitle">{{ $article->title }}</span>

                                <span class="hp__meta">
                                    <span>🗓 {{ $article->published_at?->translatedFormat('d M Y') }}</span>
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- ---------------- Rubriques ---------------- --}}
            <div class="hp__cats">
                @foreach($sections as $section)
                    @php($top = $section->articles->first())
                    <div class="hp__cat">
                        <a href="{{ route('categories.show', $section->slug) }}" class="hp__cathead">
                            {{ $section->name }}
                        </a>

                        @if($top)
                            <a href="{{ route('articles.show', $top->slug) }}">
                                @if($top->featured_image)
                                    <span class="hp__catthumb">
                                        <img src="{{ $top->thumbUrl(400) }}" alt="{{ $top->title }}"
                                             loading="lazy" width="400" height="250" decoding="async">
                                    </span>
                                @endif

                                <span class="hp__cattitle">
                                    {{ \Illuminate\Support\Str::limit($top->title, 78) }}
                                </span>

                                <span class="hp__catdate">
                                    🗓 {{ $top->published_at?->translatedFormat('d F Y') }}
                                </span>
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ---------------- Colonne de droite ---------------- --}}
        <aside>

            @if($popularArticles->isNotEmpty())
                <div class="hp__panel">
                    <h2 class="hp__paneltitle">🔥 Les plus lus</h2>

                    <div class="hp__most">
                        @foreach($popularArticles as $popular)
                            <a href="{{ route('articles.show', $popular->slug) }}" class="hp__mostitem">
                                <span class="hp__mostnum">
                                    @if($popular->featured_image)
                                        <img src="{{ $popular->thumbUrl(400) }}" alt=""
                                             loading="lazy" width="120" height="120" decoding="async">
                                    @endif
                                    <span>{{ $loop->iteration }}</span>
                                </span>

                                <span>
                                    <span class="hp__mosttitle">
                                        {{ \Illuminate\Support\Str::limit($popular->title, 72) }}
                                    </span>
                                    <span class="hp__mostviews">
                                        {{ number_format($popular->recent_views_count ?? $popular->views_count ?? 0, 0, ',', ' ') }} vues
                                    </span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{--
                Infolettre : masquée sur téléphone. L'inscription y est
                pénible, et le formulaire du pied de page suffit.
            --}}
            <div class="hp__panel hp__panel--dark hp__news hp__news--desktop">
                <h2 class="hp__paneltitle">✉️ Newsletter</h2>

                <div class="hp__panelbody">
                    <p>
                        Restez informé en temps réel ! Recevez les dernières
                        actualités directement dans votre boîte mail.
                    </p>

                    @if(session('newsletter_success'))
                        <p style="color:var(--primary)">{{ session('newsletter_success') }}</p>
                    @endif

                    <form method="POST" action="{{ route('newsletter.store') }}">
                        @csrf
                        <label class="sr-only" for="hp-news">Votre adresse e-mail</label>
                        <input id="hp-news" type="email" name="email"
                               placeholder="Votre adresse e-mail" required>
                        <button type="submit">S’inscrire</button>
                    </form>
                </div>
            </div>

            {{--
                La campagne en cours occupe cette place, plus utile ici
                qu'un rappel des réseaux : le pied de page les liste déjà.
            --}}
            <div style="margin-bottom:18px">
                <x-featured-fundraiser />
            </div>

            {{-- Emplacement vendable ; à défaut, une invitation à soutenir. --}}
            <x-ad-slot position="sidebar_top" />

            <a href="{{ route('donations.create') }}" class="hp__promo">
                <strong>Ensemble pour une information plus proche de vous</strong>
                <span>Soutenez le journalisme indépendant en Haïti</span>
            </a>

            <x-ad-slot position="sidebar_bottom" />
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var hero = document.getElementById('hero');
    if (!hero) { return; }

    var slides = hero.querySelectorAll('.hp__slide');
    var dots   = hero.querySelectorAll('.hp__dot');
    if (slides.length < 2) { return; }

    var index = 0;
    var timer = null;

    /*
     * Une diapositive masquée ne charge pas son image : au premier
     * défilement le cadre resterait vide. On les précharge une fois la
     * page installée, pour ne pas concurrencer l'affichage initial.
     */
    setTimeout(function () {
        slides.forEach(function (slide, i) {
            if (i === 0) { return; }

            var img = slide.querySelector('img');
            if (!img) { return; }

            img.loading = 'eager';

            // new Image() déclenche réellement la requête réseau.
            var pre = new Image();
            pre.src = img.currentSrc || img.src;
        });
    }, 1200);

    function go(next) {
        slides[index].classList.remove('is-on');
        if (dots[index]) { dots[index].classList.remove('is-on'); }

        index = (next + slides.length) % slides.length;

        slides[index].classList.add('is-on');
        if (dots[index]) { dots[index].classList.add('is-on'); }
    }

    function restart() {
        clearInterval(timer);
        timer = setInterval(function () { go(index + 1); }, 7000);
    }

    hero.querySelectorAll('[data-slide]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            // Le bouton est posé sur un lien : ne pas ouvrir l'article.
            e.preventDefault();
            go(index + parseInt(btn.dataset.slide, 10));
            restart();
        });
    });

    dots.forEach(function (dot) {
        dot.addEventListener('click', function (e) {
            e.preventDefault();
            go(parseInt(dot.dataset.go, 10));
            restart();
        });
    });

    // On suspend pendant la lecture, et quand l'onglet passe à l'arrière-plan.
    hero.addEventListener('mouseenter', function () { clearInterval(timer); });
    hero.addEventListener('mouseleave', restart);

    document.addEventListener('visibilitychange', function () {
        document.hidden ? clearInterval(timer) : restart();
    });

    restart();
})();
</script>
@endpush
