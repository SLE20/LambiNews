@extends('front.layouts.app')

@section('title', 'Erreur temporaire — Lambi News')

@section(
    'meta_description',
    'Une erreur temporaire est survenue sur Lambi News.'
)

@push('styles')
    <style>
        .error-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 55vh;
            text-align: center;
        }

        .error-card {
            width: min(650px, 100%);
            padding: clamp(35px, 8vw, 70px);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: white;
            box-shadow: var(--shadow);
        }

        .error-code {
            margin: 0;
            color: var(--primary);
            font-family: "Playfair Display", Georgia, serif;
            font-size: clamp(90px, 20vw, 180px);
            font-weight: 800;
            line-height: 0.8;
            letter-spacing: -0.08em;
        }

        .error-title {
            margin: 28px 0 12px;
            font-family: "Playfair Display", Georgia, serif;
            font-size: clamp(28px, 5vw, 42px);
            line-height: 1.15;
        }

        .error-description {
            max-width: 480px;
            margin: 0 auto 28px;
            color: var(--muted);
        }

        .error-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 21px;
            color: var(--black);
            border-radius: 999px;
            background: var(--primary);
            font-weight: 800;
        }

        .error-button:hover {
            color: white;
            background: var(--primary-dark);
        }
    </style>
@endpush

@section('content')
    <section class="error-page">
        <div class="error-card">
            <div class="error-code">
                500
            </div>

            <h1 class="error-title">
                Une erreur temporaire est survenue
            </h1>

            <p class="error-description">
                Notre équipe a été informée du problème.
                Veuillez réessayer dans quelques instants.
            </p>

            <a
                href="{{ route('home') }}"
                class="error-button"
            >
                Retour à l’accueil
            </a>
        </div>
    </section>
@endsection