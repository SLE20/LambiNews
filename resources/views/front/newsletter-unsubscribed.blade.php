@extends('front.layouts.app')

@section('title', 'Désabonnement — Lambi News')
@section('meta_description', 'Vous êtes désabonné de l’infolettre de Lambi News.')

@section('content')
<section style="padding:64px 16px 88px">
    <div style="max-width:560px;margin:0 auto;text-align:center;background:var(--surface);
                border:1px solid var(--border);border-radius:var(--radius);padding:38px 28px">
        <h1 style="font-family:'Playfair Display',Georgia,serif;font-size:1.8rem;margin:0 0 12px">
            Vous êtes désabonné
        </h1>

        <p style="color:var(--muted)">
            Nous n’enverrons plus l’infolettre à
            <strong>{{ $subscriber->email }}</strong>.
        </p>

        <p style="color:var(--muted);font-size:.9rem">
            S’il s’agit d’une erreur, vous pouvez vous réinscrire à tout moment en bas de page.
        </p>

        <p style="margin-top:24px">
            <a href="{{ route('home') }}"
               style="display:inline-block;padding:13px 26px;border-radius:999px;
                      background:var(--black);color:#fff;font-weight:600">
                Retour au site
            </a>
        </p>
    </div>
</section>
@endsection
