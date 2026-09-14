@extends('front.layouts.app')

@section('title', 'Maintenance — Lambi News')

@section(
    'meta_description',
    'Lambi News est temporairement en maintenance.'
)

@push('styles')
    <style>
        .maintenance-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 55vh;
            text-align: center;
        }

        .maintenance-card {
            width: min(650px, 100%);
            padding: clamp(35px, 8vw, 70px);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: white;
            box-shadow: var(--shadow);
        }

        .maintenance-logo {
            width: 120px;
            height: 120px;
            margin: 0 auto 25px;
            border: 3px solid var(--primary);
            border-radius: 50%;
            object-fit: cover;
        }

        .maintenance-title {
            margin: 0 0 14px;
            font-family: "Playfair Display", Georgia, serif;
            font-size: clamp(30px, 5vw, 45px);
            line-height: 1.15;
        }

        .maintenance-description {
            max-width: 480px;
            margin: 0 auto;
            color: var(--muted);
        }
    </style>
@endpush

@section('content')
    <section class="maintenance-page">
        <div class="maintenance-card">
            <img
                src="{{ asset('images/lambinews-embleme.jpg') }}"
                alt="Lambi News"
                class="maintenance-logo"
            >

            <h1 class="maintenance-title">
                Nous revenons bientôt
            </h1>

            <p class="maintenance-description">
                Lambi News effectue actuellement une courte
                maintenance afin d’améliorer votre expérience.
            </p>
        </div>
    </section>
@endsection