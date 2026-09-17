@extends('front.layouts.app')

@section('title', 'Où voter ? Centres d’inscription et de vote — Lambi News')
@section('meta_description', 'Trouvez votre centre d’inscription et de vote (CIV) par département, commune, section communale ou adresse, d’après la liste du CEP.')

@push('styles')
    @include('front.elections._styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
    <style>
        .elw__layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.25fr); gap: 18px; align-items: start; margin-top: 24px; }
        .elw__map { height: 560px; border-radius: var(--radius); border: 1px solid var(--border); z-index: 1; position: sticky; top: 90px; }
        .elw__filters { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 10px; }
        .elw__filters label { display: grid; gap: 5px; font-size: .78rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); }
        .elw__filters select, .elw__filters input {
            width: 100%; min-width: 0; padding: 11px 12px; border-radius: 10px; border: 1px solid var(--border);
            font: inherit; font-size: .95rem; background: #fff; color: var(--text); text-transform: none; letter-spacing: 0;
        }
        .elw__filters select:disabled { background: var(--background); color: var(--muted); }
        .elw__search { grid-column: 1 / -1; }
        .elw__meta { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin: 16px 0 10px; font-size: .88rem; }
        .elw__reset { border: 0; background: none; color: var(--primary-dark); font-weight: 700; cursor: pointer; font: inherit; }
        .elw__list { display: grid; gap: 10px; }
        .elw__item { padding: 14px 16px; border-radius: 12px; background: var(--surface); border: 1px solid var(--border); }
        .elw__item strong { display: block; font-size: .98rem; }
        .elw__where { display: block; margin-top: 3px; font-size: .78rem; font-weight: 800; letter-spacing: .04em; color: var(--primary-dark); text-transform: uppercase; }
        .elw__addr { display: block; margin-top: 5px; font-size: .88rem; color: var(--muted); }
        .elw__pager { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-top: 14px; }
        .elw__pager button {
            padding: 9px 16px; border-radius: 999px; border: 1px solid var(--border); background: var(--surface);
            font: inherit; font-weight: 700; cursor: pointer;
        }
        .elw__pager button:disabled { opacity: .4; cursor: default; }
        .elw__marker {
            display: grid; place-items: center; border-radius: 50%; font-weight: 800; font-size: .8rem;
            background: var(--black); color: var(--primary); border: 3px solid var(--primary);
            box-shadow: 0 4px 12px rgba(0,0,0,.3);
        }
        .elw__marker.is-active { background: var(--primary); color: var(--black); border-color: var(--black); }
        @media (max-width: 900px) {
            .elw__layout { grid-template-columns: minmax(0,1fr); }
            .elw__map { position: relative; top: 0; height: 340px; }
        }
        @media (max-width: 520px) { .elw__filters { grid-template-columns: minmax(0,1fr); } }
    </style>
@endpush

@section('content')
<section class="el">
    <div class="el__wrap">
        @include('front.elections._nav')

        <p class="el__eyebrow">Élections 2026</p>
        <h1 class="el__title">Où s’inscrire et voter ?</h1>
        <p class="el__lead">
            {{ number_format($total, 0, ',', ' ') }} centres d’inscription et de vote (CIV) dans les dix départements.
            Cherchez par lieu ou par nom : aucune localisation ne vous est demandée.
        </p>

        @if($total === 0)
            <div class="el__empty" style="margin-top:24px">
                Le répertoire des centres est en cours de chargement par la rédaction.
                Consultez en attendant <a href="https://cephaiti.ht/centres-dinscription-et-de-vote-civ/" target="_blank" rel="noopener">la page du CEP</a>.
            </div>
        @else
            <div class="elw__layout">
                <div>
                    <div id="elw-map" class="elw__map" role="region" aria-label="Carte des centres par département"></div>
                    <p class="el__source">
                        Les repères sont placés sur les chefs-lieux et indiquent le nombre de centres par
                        département : la liste du CEP ne donne pas l’emplacement exact des bâtiments.
                    </p>
                </div>

                <div>
                    <form class="elw__filters" id="elw-form" onsubmit="return false">
                        <label>Département
                            <select name="departement" id="elw-dept">
                                <option value="">Tous les départements</option>
                                @foreach($departments as $key => $d)
                                    <option value="{{ $key }}" @selected(request('departement') === $key)>{{ $d['label'] }} · {{ $d['count'] }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Commune
                            <select name="commune" id="elw-commune" disabled>
                                <option value="">Choisissez un département</option>
                            </select>
                        </label>
                        <label>Section communale
                            <select name="section" id="elw-section" disabled>
                                <option value="">Choisissez une commune</option>
                            </select>
                        </label>
                        <label>Centre ou adresse
                            <input type="search" name="q" id="elw-q" placeholder="Ex. lycée, route de…" autocomplete="off" maxlength="80">
                        </label>
                    </form>

                    <div class="elw__meta">
                        <span id="elw-count" aria-live="polite">Chargement…</span>
                        <button type="button" class="elw__reset" id="elw-reset">Réinitialiser</button>
                    </div>

                    <div class="elw__list" id="elw-list"></div>

                    <div class="elw__pager">
                        <button type="button" id="elw-prev">← Précédents</button>
                        <span id="elw-page" style="font-size:.86rem;color:var(--muted)"></span>
                        <button type="button" id="elw-next">Suivants →</button>
                    </div>

                    <p class="el__source">
                        Source : Conseil électoral provisoire (CEP), liste des centres d’inscription et de vote.
                        Vérifiez toujours votre centre auprès du CEP avant de vous déplacer.
                    </p>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection

@if($total > 0)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
<script>
(function () {
    var endpoint = @json(route('elections.centers'));
    var depts = @json($departments);
    var state = { departement: @json(request('departement', '')), commune: '', section: '', q: '', page: 1 };

    var $ = function (id) { return document.getElementById(id); };
    var dept = $('elw-dept'), commune = $('elw-commune'), section = $('elw-section'), q = $('elw-q');
    var list = $('elw-list'), count = $('elw-count'), pageEl = $('elw-page');
    var markers = {};
    var map = null;

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function fill(select, values, placeholder, current) {
        select.innerHTML = '<option value="">' + esc(placeholder) + '</option>' + values.map(function (v) {
            return '<option' + (v === current ? ' selected' : '') + '>' + esc(v) + '</option>';
        }).join('');
        select.disabled = values.length === 0;
    }

    var timer = null, seq = 0;
    function load() {
        var my = ++seq;
        var params = new URLSearchParams();
        Object.keys(state).forEach(function (k) { if (state[k]) { params.set(k, state[k]); } });
        count.textContent = 'Recherche…';

        fetch(endpoint + '?' + params.toString(), { headers: { Accept: 'application/json' } })
            .then(function (r) { if (!r.ok) { throw new Error(); } return r.json(); })
            .then(function (d) {
                if (my !== seq) { return; }
                if (state.departement) {
                    fill(commune, d.communes, 'Toutes les communes', state.commune);
                    fill(section, d.sections, state.commune ? 'Toutes les sections' : 'Choisissez une commune', state.section);
                } else {
                    fill(commune, [], 'Choisissez un département', '');
                    fill(section, [], 'Choisissez une commune', '');
                }

                count.textContent = d.total.toLocaleString('fr-FR') + ' centre' + (d.total > 1 ? 's' : '') + ' trouvé' + (d.total > 1 ? 's' : '');
                pageEl.textContent = d.total ? 'Page ' + d.page + ' / ' + d.pages : '';
                $('elw-prev').disabled = d.page <= 1;
                $('elw-next').disabled = d.page >= d.pages;

                list.innerHTML = d.items.length ? d.items.map(function (c) {
                    var label = depts[c.department] ? depts[c.department].label : c.department;
                    return '<div class="elw__item"><strong>' + esc(c.name) + '</strong>' +
                        '<span class="elw__where">' + esc(label) + ' · ' + esc(c.commune) + (c.section ? ' · ' + esc(c.section) : '') + '</span>' +
                        (c.address ? '<span class="elw__addr">📍 ' + esc(c.address) + '</span>' : '') + '</div>';
                }).join('') : '<div class="el__empty">Aucun centre ne correspond à cette recherche.</div>';

                highlight();
            })
            .catch(function () {
                if (my === seq) { count.textContent = 'Le répertoire n’a pas pu être chargé. Réessayez.'; }
            });
    }

    function highlight() {
        Object.keys(markers).forEach(function (key) {
            var el = markers[key].getElement();
            if (el) { el.firstChild.classList.toggle('is-active', key === state.departement); }
        });
        if (map && state.departement && depts[state.departement]) {
            map.flyTo([depts[state.departement].lat, depts[state.departement].lng], 9, { duration: .6 });
        } else if (map) {
            map.flyTo([18.95, -72.7], 7, { duration: .6 });
        }
    }

    dept.addEventListener('change', function () { state.departement = dept.value; state.commune = ''; state.section = ''; state.page = 1; load(); });
    commune.addEventListener('change', function () { state.commune = commune.value; state.section = ''; state.page = 1; load(); });
    section.addEventListener('change', function () { state.section = section.value; state.page = 1; load(); });
    q.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () { state.q = q.value.trim(); state.page = 1; load(); }, 300);
    });
    $('elw-prev').addEventListener('click', function () { state.page--; load(); list.scrollIntoView({ block: 'nearest' }); });
    $('elw-next').addEventListener('click', function () { state.page++; load(); list.scrollIntoView({ block: 'nearest' }); });
    $('elw-reset').addEventListener('click', function () {
        state = { departement: '', commune: '', section: '', q: '', page: 1 };
        dept.value = ''; q.value = '';
        load();
    });

    if (window.L) {
        map = L.map('elw-map', { center: [18.95, -72.7], zoom: 7, minZoom: 6, maxZoom: 12, scrollWheelZoom: false });
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        Object.keys(depts).forEach(function (key) {
            var d = depts[key];
            if (!d.count) { return; }
            var size = Math.round(30 + Math.min(24, d.count / 10));
            var icon = L.divIcon({
                className: '',
                html: '<div class="elw__marker" style="width:' + size + 'px;height:' + size + 'px">' + d.count + '</div>',
                iconSize: [size, size], iconAnchor: [size / 2, size / 2]
            });
            markers[key] = L.marker([d.lat, d.lng], { icon: icon, title: d.label + ' — ' + d.count + ' centres' })
                .addTo(map)
                .bindTooltip(d.label + ' (' + d.city + ') · ' + d.count + ' centres')
                .on('click', function () { dept.value = key; dept.dispatchEvent(new Event('change')); });
        });
    }

    load();
})();
</script>
@endpush
@endif
