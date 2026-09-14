@extends('front.layouts.app')

@section('title', 'Contactez Lambi News')

@section(
    'meta_description',
    'Contactez la rédaction de Lambi News.'
)

@push('styles')
    <style>
        .contact-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 360px;
            gap: 35px;
            align-items: start;
        }

        .contact-card,
        .contact-information {
            padding: clamp(24px, 5vw, 38px);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: white;
            box-shadow: var(--shadow);
        }

        .contact-title {
            margin: 0 0 10px;
            font-family: "Playfair Display", Georgia, serif;
            font-size: clamp(34px, 5vw, 52px);
            line-height: 1.1;
        }

        .contact-introduction {
            margin-bottom: 28px;
            color: var(--muted);
        }

        .contact-form {
            display: grid;
            gap: 18px;
        }

        .contact-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .contact-field {
            display: grid;
            gap: 7px;
        }

        .contact-field label {
            font-size: 14px;
            font-weight: 700;
        }

        .contact-field input,
        .contact-field textarea {
            width: 100%;
            padding: 12px 14px;
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 9px;
            outline: 0;
            background: #fdfcf9;
        }

        .contact-field input:focus,
        .contact-field textarea:focus {
            border-color: var(--primary);
            box-shadow:
                0 0 0 3px rgba(216, 169, 34, 0.15);
        }

        .contact-field textarea {
            min-height: 180px;
            resize: vertical;
        }

        .contact-submit {
            width: fit-content;
            padding: 13px 24px;
            color: var(--black);
            border: 0;
            border-radius: 999px;
            background: var(--primary);
            cursor: pointer;
            font-weight: 800;
        }

        .contact-submit:hover {
            color: white;
            background: var(--primary-dark);
        }

        .contact-success {
            margin-bottom: 22px;
            padding: 14px 16px;
            color: #14532d;
            border: 1px solid #86efac;
            border-radius: 9px;
            background: #dcfce7;
        }

        .contact-error {
            color: #b91c1c;
            font-size: 13px;
        }

        .contact-honeypot {
            position: absolute;
            left: -9999px;
        }

        .contact-information h2 {
            margin-top: 0;
            font-family: "Playfair Display", Georgia, serif;
        }

        .contact-list {
            display: grid;
            gap: 18px;
        }

        .contact-item {
            padding-bottom: 18px;
            border-bottom: 1px solid var(--border);
        }

        .contact-item:last-child {
            padding-bottom: 0;
            border-bottom: 0;
        }

        .contact-item strong {
            display: block;
            margin-bottom: 5px;
        }

        .contact-item a {
            color: var(--primary-dark);
            font-weight: 700;
        }

        @media (max-width: 850px) {
            .contact-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 580px) {
            .contact-row {
                grid-template-columns: 1fr;
            }

            .contact-submit {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="contact-layout">
        <section class="contact-card">
            <h1 class="contact-title">
                Contactez-nous
            </h1>

            <p class="contact-introduction">
                Une information à partager, une correction ou une
                proposition de collaboration ? Écrivez à la rédaction.
            </p>

            @if(session('contact_success'))
                <div
                    class="contact-success"
                    role="status"
                >
                    {{ session('contact_success') }}
                </div>
            @endif

            <form
                action="{{ route('contact.store') }}"
                method="POST"
                class="contact-form"
            >
                @csrf

                <div class="contact-row">
                    <div class="contact-field">
                        <label for="contact-name">
                            Nom
                        </label>

                        <input
                            id="contact-name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            maxlength="100"
                            required
                        >

                        @error('name', 'contact')
                            <span class="contact-error">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <div class="contact-field">
                        <label for="contact-email">
                            Adresse courriel
                        </label>

                        <input
                            id="contact-email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            maxlength="255"
                            required
                        >

                        @error('email', 'contact')
                            <span class="contact-error">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="contact-field">
                    <label for="contact-phone">
                        Téléphone
                    </label>

                    <input
                        id="contact-phone"
                        name="phone"
                        type="tel"
                        value="{{ old('phone') }}"
                        maxlength="30"
                    >

                    @error('phone', 'contact')
                        <span class="contact-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="contact-field">
                    <label for="contact-subject">
                        Sujet
                    </label>

                    <input
                        id="contact-subject"
                        name="subject"
                        type="text"
                        value="{{ old('subject') }}"
                        maxlength="255"
                        required
                    >

                    @error('subject', 'contact')
                        <span class="contact-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="contact-field">
                    <label for="contact-message">
                        Message
                    </label>

                    <textarea
                        id="contact-message"
                        name="message"
                        minlength="10"
                        maxlength="5000"
                        required
                    >{{ old('message') }}</textarea>

                    @error('message', 'contact')
                        <span class="contact-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div
                    class="contact-honeypot"
                    aria-hidden="true"
                >
                    <label for="contact-website">
                        Ne remplissez pas ce champ
                    </label>

                    <input
                        id="contact-website"
                        name="website"
                        type="text"
                        tabindex="-1"
                        autocomplete="off"
                    >
                </div>

                <button
                    type="submit"
                    class="contact-submit"
                >
                    Envoyer le message
                </button>
            </form>
        </section>

        <aside class="contact-information">
            <h2>
                La rédaction
            </h2>

            <div class="contact-list">
                <div class="contact-item">
                    <strong>Courriel</strong>

                    <a href="mailto:info.lambinews@gmail.com">
                        info.lambinews@gmail.com
                    </a>
                </div>

                <div class="contact-item">
                    <strong>Facebook</strong>

                    <a
                        href="https://www.facebook.com/lambinews/"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Lambi News
                    </a>
                </div>

                <div class="contact-item">
                    <strong>Instagram</strong>

                    <a
                        href="https://www.instagram.com/info.lambinews/"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        @info.lambinews
                    </a>
                </div>

                <div class="contact-item">
                    <strong>TikTok</strong>

                    <a
                        href="https://www.tiktok.com/@lambinews"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        @lambinews
                    </a>
                </div>
            </div>
        </aside>
    </div>
@endsection