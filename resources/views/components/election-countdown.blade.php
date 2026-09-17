@php
    $days = (int) now()->startOfDay()->diffInDays($event->starts_on, false);
    $untilEnd = $event->ends_on ? (int) now()->startOfDay()->diffInDays($event->ends_on, false) : null;
@endphp

<div class="elcd">
    <span class="elcd__label">🗳 Élections 2026 · prochaine échéance</span>
    <a class="elcd__title" href="{{ route('elections.calendar') }}#echeance-{{ $event->id }}">{{ $event->title }}</a>
    <span class="elcd__row">
        @if($days > 0)
            <b>{{ $days }}</b><span>jour{{ $days > 1 ? 's' : '' }}<br>avant le {{ $event->starts_on->translatedFormat('j F') }}</span>
        @elseif($untilEnd !== null && $untilEnd >= 0)
            <b>{{ $untilEnd }}</b><span>jour{{ $untilEnd > 1 ? 's' : '' }} avant la fin<br>le {{ $event->ends_on->translatedFormat('j F') }}</span>
        @else
            <b>J</b><span>aujourd’hui</span>
        @endif
    </span>
    <span class="elcd__links">
        <a href="{{ route('elections.calendar') }}">Calendrier →</a>
        <a href="{{ route('elections.where') }}">Où voter ?</a>
    </span>
</div>

@once
@push('styles')
<style>
    .elcd {
        display: block; margin-bottom: 18px; padding: 18px; border-radius: 12px; color: #fff;
        background: radial-gradient(circle at 90% 10%, rgba(216,169,34,.35), transparent 50%), #0d0d0d;
        border: 1px solid #0d0d0d; transition: transform .15s, box-shadow .15s;
    }
    .elcd__title:hover { color: var(--primary); }
    .elcd__label { display: block; font-size: .68rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: var(--primary); }
    .elcd__title { display: block; margin: 8px 0 12px; font-family: "Playfair Display", Georgia, serif; font-size: 1.1rem; line-height: 1.3; color: #fff; }
    .elcd__row { display: flex; align-items: center; gap: 12px; }
    .elcd__row b { font-size: 2.6rem; line-height: 1; color: var(--primary); }
    .elcd__row span { font-size: .8rem; color: rgba(255,255,255,.7); line-height: 1.35; }
    .elcd__links { display: flex; justify-content: space-between; margin-top: 14px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,.12); font-size: .82rem; font-weight: 700; }
    .elcd__links a { color: var(--primary); }
</style>
@endpush
@endonce
