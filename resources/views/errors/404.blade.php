@extends('front.layouts.app')

@section('title', 'Page introuvable — Lambi News')

@section(
    'meta_description',
    'La page demandée est introuvable sur Lambi News.'
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

        .error-actions {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .error-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 21px;
            border-radius: 999px;
            font-weight: 800;
        }

        .error-button-primary {
            color: var(--black);
            background: var(--primary);
        }

        .error-button-primary:hover {
            color: white;
            background: var(--primary-dark);
        }

        .error-button-secondary {
            color: var(--text);
            border: 1px solid var(--border);
            background: white;
        }

        .error-button-secondary:hover {
            border-color: var(--primary);
        }
   </style>
@endpush

@section('content')
    <section class="error-page">
        <div class="error-card">
            <div class="error-code">
                404
            </div>

            <h1 class="error-title">
                Cette page est introuvable
            </h1>

            <p class="error-description">
                Le contenu recherché a peut-être été déplacé,
                supprimé ou son adresse n’est plus valide.
            </p>

            <div class="error-actions">
                <a
                    href="{{ route('home') }}"
                    class="error-button error-button-primary"
                >
                    Retour à l’accueil
                </a>

                <a
                    href="{{ route('search') }}"
                    class="error-button error-button-secondary"
                >
                    Rechercher un article
                </a>
            </div>
        </div>
    </section>
@endsection