<footer class="site-footer">
    <div class="container">
        <section
            class="footer-newsletter"
            aria-labelledby="newsletter-heading"
        >
            <div class="newsletter-heading">
                <span class="newsletter-eyebrow">
                    Restez informé
                </span>

                <h2 id="newsletter-heading">
                    L’actualité directement dans votre boîte courriel
                </h2>

                <p>
                    Recevez gratuitement les nouvelles importantes
                    et les grandes enquêtes de Lambi News.
                </p>
            </div>

            <form
                action="{{ route('newsletter.store') }}"
                method="POST"
                class="footer-newsletter-form"
            >
                @csrf

                <label
                    for="footer-newsletter-email"
                    class="sr-only"
                >
                    Votre adresse courriel
                </label>

                <input
                    id="footer-newsletter-email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    placeholder="Votre adresse courriel"
                    maxlength="255"
                    autocomplete="email"
                    required
                >

                <button type="submit">
                    S’inscrire
                </button>
            </form>

            @if(session('newsletter_success'))
                <p class="newsletter-feedback success">
                    {{ session('newsletter_success') }}
                </p>
            @endif

            @error('email', 'newsletter')
                <p class="newsletter-feedback error">
                    {{ $message }}
                </p>
            @enderror
        </section>

        <div class="footer-main">
            <div class="footer-brand-column">
                <img
                    src="{{ asset('images/lambinews-embleme.jpg') }}"
                    alt="Lambi News"
                    class="footer-brand-logo"
                >

                <p class="footer-description">
                    Lambi News est un média d’information engagé
                    au service des citoyens. Nous racontons les faits,
                    expliquons les enjeux et donnons la parole à la
                    communauté.
                </p>

                <a
                    href="mailto:info.lambinews@gmail.com"
                    class="footer-email"
                >
                    <svg
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M4 4h16v16H4z"/>
                        <path d="m4 6 8 6 8-6"/>
                    </svg>

                    <span>
                        info.lambinews@gmail.com
                    </span>
                </a>
            </div>

            <div>
                <h2 class="footer-column-title">
                    Rubriques
                </h2>

                <nav
                    class="footer-navigation"
                    aria-label="Rubriques du pied de page"
                >
                    @forelse(
                        collect($navigationCategories ?? [])->take(6)
                        as $footerCategory
                    )
                        <a
                            href="{{ route(
                                'categories.show',
                                $footerCategory->slug
                            ) }}"
                        >
                            {{ $footerCategory->name }}
                        </a>
                    @empty
                        <a href="{{ route('home') }}">
                            Actualités
                        </a>
                    @endforelse
                </nav>
            </div>

            <div>
                <h2 class="footer-column-title">
                    Lambi News
                </h2>

                <nav
                    class="footer-navigation"
                    aria-label="Pages institutionnelles"
                >
                    @foreach($footerPages ?? [] as $footerPage)
                        <a
                            href="{{ route(
                                'pages.show',
                                $footerPage->slug
                            ) }}"
                        >
                            {{ $footerPage->title }}
                        </a>
                    @endforeach

                    <a href="{{ route('contact.create') }}">
                        Contactez-nous
                    </a>

                    <a href="{{ route('announcements.index') }}">
                        Annonces et avis
                    </a>

                    <a href="{{ route('polls.index') }}">
                        Sondages
                    </a>

                    <a href="{{ route('donations.create') }}">
                        Soutenir Lambi News
                    </a>

                    <a href="{{ route('fundraisers.index') }}">
                        Kanpay finansman
                    </a>

                    <a href="{{ route('media-kit') }}">
                        Annoncer chez nous
                    </a>

                    <a href="{{ route('feed') }}">
                        Flux RSS
                    </a>
                </nav>
            </div>

            <div class="footer-social-column">
                <h2 class="footer-column-title">
                    Suivez-nous
                </h2>

                <div class="footer-socials">
                    <a
                        href="https://www.facebook.com/lambinews/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="footer-social-link"
                    >
                        <span class="footer-social-icon">
                            <svg
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    d="M14 8h3V4h-3c-3 0-5 2-5 5v3H6v4h3v8h4v-8h3.5l.5-4h-4V9c0-.7.3-1 1-1Z"
                                />
                            </svg>
                        </span>

                        <span>Facebook</span>
                    </a>

                    <a
                        href="https://www.instagram.com/info.lambinews/"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="footer-social-link"
                    >
                        <span class="footer-social-icon">
                            <svg
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <rect
                                    x="3"
                                    y="3"
                                    width="18"
                                    height="18"
                                    rx="5"
                                />
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="4"
                                />
                                <circle
                                    cx="17.5"
                                    cy="6.5"
                                    r="1"
                                    fill="currentColor"
                                    stroke="none"
                                />
                            </svg>
                        </span>

                        <span>Instagram</span>
                    </a>

                    <a
                        href="https://www.tiktok.com/@lambinews"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="footer-social-link"
                    >
                        <span class="footer-social-icon">
                            <svg
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    d="M16 3c.4 2.3 1.7 3.7 4 4v4c-1.5 0-2.8-.4-4-1.1V16a6 6 0 1 1-6-6c.3 0 .7 0 1 .1v4a2 2 0 1 0 1 1.9V3h4Z"
                                />
                            </svg>
                        </span>

                        <span>TikTok</span>
                    </a>

                    <a
                        href="mailto:info.lambinews@gmail.com"
                        class="footer-social-link"
                    >
                        <span class="footer-social-icon">
                            <svg
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M4 5h16v14H4z"/>
                                <path d="m4 7 8 6 8-6"/>
                            </svg>
                        </span>

                        <span>Nous écrire</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div>
                © {{ date('Y') }} Lambi News.
                Tous droits réservés.

                <span class="footer-signature">
                    Le citoyen au cœur de l’information.
                </span>
            </div>

            <nav
                class="footer-bottom-links"
                aria-label="Navigation secondaire"
            >
                <a href="{{ route('contact.create') }}">
                    Contact
                </a>
            </nav>
        </div>
    </div>
</footer>