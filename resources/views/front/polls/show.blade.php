@extends('front.layouts.app')

@section('title', $poll->question.' — Sondage — Lambi News')
@section('meta_description', \Illuminate\Support\Str::limit($poll->description ?: $poll->question, 160))

@push('styles')
    @include('front.polls._styles')
    <style>
        .polls { padding: 44px 16px 80px; }
        .polls__wrap { max-width: 680px; margin: 0 auto; }
    </style>
@endpush

@section('content')
<section class="polls">
    <div class="polls__wrap">

        @if(session('poll_status'))
            <p class="poll__flash">{{ session('poll_status') }}</p>
        @endif

        @include('front.polls._poll', ['poll' => $poll, 'recorder' => $recorder])

        <p style="margin-top:22px">
            <a href="{{ route('polls.index') }}">← Tous les sondages</a>
        </p>
    </div>
</section>
@endsection
