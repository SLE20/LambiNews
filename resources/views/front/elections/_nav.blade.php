{{-- Bandeau commun à toutes les pages de l'espace Élections. --}}
<div class="el__notice" role="note">
    <span>ℹ️ Lambi News n’organise aucune élection et ne recommande aucun candidat. Le CEP organise le scrutin.</span>
    <a href="https://cephaiti.ht/" target="_blank" rel="noopener">Site officiel du CEP ↗</a>
</div>

<nav class="el__nav" aria-label="Espace élections">
    @foreach([
        'elections.index'    => 'Vue d’ensemble',
        'elections.calendar' => 'Calendrier',
        'elections.where'    => 'Où voter ?',
        'elections.parties'  => 'Partis et positions',
        'elections.cycle'    => 'Cycle électoral',
        'polls.index'        => 'Sondages',
    ] as $name => $label)
        <a href="{{ route($name) }}"
           class="{{ request()->routeIs($name) || ($name === 'elections.cycle' && request()->routeIs('elections.actor')) || ($name === 'elections.parties' && request()->routeIs('elections.party')) ? 'is-active' : '' }}">{{ $label }}</a>
    @endforeach
</nav>
