@extends('front.layouts.app')

@section('title', 'Où je vote ? Centres d’inscription et de vote — Lambi News')
@section('meta_description', 'Trouvez le centre d’inscription et de vote (CIV) le plus proche : recherche par département, commune, section communale, centre ou adresse, sans partager votre position.')

@push('styles')
    @include('front.elections._styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
    <style>
        .civ { margin-top: 22px; border: 1px solid var(--border); border-radius: 18px; overflow: hidden; background: var(--surface); }

        /* Filtres */
        .civ__filters { padding: 18px 20px 14px; border-bottom: 1px solid var(--border); }
        .civ__row { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .civ__field { display: grid; gap: 6px; min-width: 0; }
        .civ__field span { font-size: .84rem; font-weight: 800; color: var(--text); }
        .civ__field select, .civ__field input {
            width: 100%; min-width: 0; height: 46px; padding: 0 12px; border-radius: 10px;
            border: 1px solid #cfc8ba; background: #fff; color: var(--text); font: inherit; font-size: .95rem;
        }
        .civ__field select:disabled { background: var(--background); color: var(--muted); }
        .civ__field select:focus, .civ__field input:focus { outline: 2px solid var(--primary); outline-offset: 1px; border-color: var(--primary); }
        .civ__hint { max-width: 520px; margin: 12px 0 0; font-size: .8rem; line-height: 1.5; color: var(--muted); }

        /* Carte + résultats */
        .civ__body { display: grid; grid-template-columns: minmax(0, 1.12fr) minmax(0, 1fr); }
        .civ__mapwrap { position: relative; min-height: 640px; border-right: 1px solid var(--border); }
        .civ__map { position: absolute; inset: 0; z-index: 1; }
        .civ__badge {
            position: absolute; top: 12px; right: 12px; z-index: 500; pointer-events: none;
            padding: 7px 14px; border-radius: 999px; font-size: .8rem; font-weight: 800;
            background: var(--black); color: #fff; box-shadow: 0 4px 14px rgba(0,0,0,.25);
        }
        .civ__badge b { color: var(--primary); }
        .civ__results { display: flex; flex-direction: column; padding: 20px; min-width: 0; }
        .civ__count { margin: 0; font-size: 1.12rem; font-weight: 800; }
        .civ__range { margin: 6px 0 12px; font-size: .8rem; color: var(--muted); }
        .civ__list { flex: 1; }
        .civ__item { padding: 14px 0; border-top: 1px solid var(--border); }
        .civ__item h3 { margin: 0 0 4px; font-size: 1.02rem; line-height: 1.3; text-transform: uppercase; }
        .civ__where { display: block; font-size: .8rem; font-weight: 600; letter-spacing: .02em; color: var(--muted); text-transform: uppercase; }
        .civ__addr { display: block; margin-top: 5px; font-size: .86rem; color: var(--text); opacity: .8; line-height: 1.45; }
        .civ__addr small { color: var(--muted); }
        .civ__empty { padding: 30px 10px; text-align: center; color: var(--muted); border-top: 1px solid var(--border); }
        .civ__pager { display: grid; grid-template-columns: auto 1fr auto; align-items: center; gap: 10px; margin-top: 16px; }
        .civ__pager span { text-align: center; font-size: .86rem; font-weight: 700; }
        .civ__btn {
            min-width: 120px; height: 46px; padding: 0 16px; border-radius: 10px; cursor: pointer;
            border: 1.5px solid var(--black); background: #fff; color: var(--black); font: inherit; font-weight: 800;
        }
        .civ__btn:hover:not(:disabled) { background: var(--black); color: var(--primary); }
        .civ__btn:disabled { border-color: var(--border); color: #b3ada2; cursor: default; }
        .civ__loading { padding: 40px 10px; text-align: center; color: var(--muted); }
        .civ__foot { margin: 14px 2px 0; font-size: .86rem; }
        .civ__foot strong { display: block; margin-top: 4px; }

        /* Bulle Leaflet */
        .civ-popup strong { display: block; margin-bottom: 6px; }
        .civ-popup ol { margin: 0; padding-left: 18px; font-size: .82rem; }
        .civ-popup li { margin-bottom: 5px; }
        .civ-popup p { margin: 6px 0 0; font-size: .8rem; color: #555; }
        .leaflet-container { font-family: inherit; }

        @media (max-width: 980px) {
            .civ__row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .civ__body { grid-template-columns: minmax(0, 1fr); }
            .civ__mapwrap { min-height: 340px; border-right: 0; border-bottom: 1px solid var(--border); }
        }
        @media (max-width: 560px) {
            .civ__row { grid-template-columns: minmax(0, 1fr); }
            .civ__filters, .civ__results { padding: 16px; }
            .civ__mapwrap { min-height: 300px; }
            .civ__badge { top: 10px; right: 10px; font-size: .74rem; padding: 6px 11px; }
            .civ__btn { min-width: 0; }
        }
    </style>
@endpush

@section('content')
<section class="el">
    <div class="el__wrap">
        @include('front.elections._nav')

        <p class="el__eyebrow">Élections 2026</p>
        <h1 class="el__title">Où je vote ?</h1>
        <p class="el__lead">
            Trouvez le centre d’inscription et de vote (CIV) le plus proche de chez vous.
            Recherchez par département, commune, centre ou adresse, sans partager votre position.
        </p>

        @if($total === 0)
            <div class="el__empty" style="margin-top:24px">
                Le répertoire des centres est en cours de chargement par la rédaction.
                Consultez en attendant <a href="https://cephaiti.ht/centres-dinscription-et-de-vote-civ/" target="_blank" rel="noopener">la page du CEP</a>.
            </div>
        @else
            <div class="civ" id="civ">
                <div class="civ__filters">
                    <div class="civ__row">
                        <label class="civ__field">
                            <span>Département</span>
                            <select id="civ-dept">
                                <option value="">Tous les départements</option>
                                @foreach($departments as $key => $d)
                                    @if($d['count'])
                                        <option value="{{ $key }}">{{ $key }} · {{ $d['count'] }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </label>
                        <label class="civ__field">
                            <span>Commune</span>
                            <select id="civ-commune" disabled>
                                <option value="">Choisissez d’abord un département</option>
                            </select>
                        </label>
                        <label class="civ__field">
                            <span>Section communale</span>
                            <select id="civ-section" disabled>
                                <option value="">Choisissez d’abord une commune</option>
                            </select>
                        </label>
                        <label class="civ__field">
                            <span>Centre ou adresse</span>
                            <input type="search" id="civ-q" placeholder="Ex. lycée, route de Frères" autocomplete="off" maxlength="80">
                        </label>
                    </div>
                    <p class="civ__hint">
                        Les repères sont placés sur les chefs-lieux et montrent le nombre de CIV par
                        département. Ils ne localisent pas les bâtiments.
                    </p>
                </div>

                <div class="civ__body">
                    <div class="civ__mapwrap">
                        <div id="civ-map" class="civ__map" role="region" aria-label="Carte des centres par département"></div>
                        <span class="civ__badge" id="civ-visible">Départements visibles sur la carte : <b>{{ collect($departments)->where('count', '>', 0)->count() }}</b></span>
                    </div>

                    <div class="civ__results" aria-live="polite">
                        <p class="civ__count" id="civ-count">{{ number_format($total, 0, ',', ' ') }} centres trouvés</p>
                        <p class="civ__range" id="civ-range"></p>
                        <div class="civ__list" id="civ-list"><div class="civ__loading">Chargement du répertoire des CIV…</div></div>
                        <div class="civ__pager" id="civ-pager" aria-label="Pagination des résultats">
                            <button type="button" class="civ__btn" id="civ-prev" disabled>Précédents</button>
                            <span id="civ-page">1 / 1</span>
                            <button type="button" class="civ__btn" id="civ-next" disabled>Suivants</button>
                        </div>
                    </div>
                </div>
            </div>

            <p class="civ__foot">
                Source : <a href="https://cephaiti.ht/centres-dinscription-et-de-vote-civ/" target="_blank" rel="noopener">Conseil électoral provisoire (CEP)</a>,
                liste nationale des centres de vote, transcrite par <a href="https://votpaw.org/fr/kote-pou-m-vote" target="_blank" rel="noopener">Votpaw</a>.
                Vérifiez votre centre auprès du CEP avant de vous déplacer.
                <strong>{{ number_format($total, 0, ',', ' ') }} centres · {{ collect($departments)->where('count', '>', 0)->count() }} départements</strong>
            </p>
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
    var PER_PAGE = 6;
    var endpoint = @json(route('elections.centers'));
    var places = @json(collect($departments)->map(fn ($d) => [$d['lat'], $d['lng']]));
    var labels = @json(collect($departments)->map(fn ($d) => $d['label'].' ('.$d['city'].')'));

    var $ = function (id) { return document.getElementById(id); };
    var dept = $('civ-dept'), commune = $('civ-commune'), section = $('civ-section'), q = $('civ-q');
    var list = $('civ-list'), count = $('civ-count'), range = $('civ-range'), pageEl = $('civ-page');
    var prev = $('civ-prev'), next = $('civ-next'), visible = $('civ-visible');

    var all = [];
    var state = { d: '', c: '', s: '', q: '', page: 0 };
    var map = null, markers = {};

    // Recherche insensible aux accents et à la casse.
    function norm(v) {
        return String(v || '').normalize('NFD').replace(/[̀-ͯ]/g, '')
            .toLocaleLowerCase('fr').replace(/[^a-z0-9]+/g, ' ').trim();
    }
    function esc(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function (ch) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
        });
    }
    function fmt(n) { return n.toLocaleString('fr-FR'); }

    function filtered() {
        var needle = norm(state.q);
        return all.filter(function (r) {
            return (!state.d || r.d === state.d)
                && (!state.c || r.c === state.c)
                && (!state.s || r.s === state.s)
                && (!needle || r._k.indexOf(needle) !== -1);
        });
    }

    // Liste « valeur · nombre » pour un sélecteur.
    function options(select, rows, key, placeholder, current) {
        var counts = {};
        rows.forEach(function (r) { if (r[key]) { counts[r[key]] = (counts[r[key]] || 0) + 1; } });
        var names = Object.keys(counts).sort(function (a, b) { return a.localeCompare(b, 'fr'); });
        select.innerHTML = '<option value="">' + esc(placeholder) + '</option>' + names.map(function (n) {
            return '<option value="' + esc(n) + '"' + (n === current ? ' selected' : '') + '>' + esc(n) + ' · ' + counts[n] + '</option>';
        }).join('');
        select.disabled = names.length === 0;
    }

    function render() {
        var rows = filtered();
        var total = rows.length;
        var pages = Math.max(1, Math.ceil(total / PER_PAGE));
        state.page = Math.min(state.page, pages - 1);
        var start = state.page * PER_PAGE;
        var slice = rows.slice(start, start + PER_PAGE);

        count.textContent = fmt(total) + ' centre' + (total > 1 ? 's' : '') + ' trouvé' + (total > 1 ? 's' : '');
        range.textContent = total ? 'Centres ' + fmt(start + 1) + ' à ' + fmt(start + slice.length) + ' sur ' + fmt(total) : '';
        pageEl.textContent = (total ? state.page + 1 : 0) + ' / ' + (total ? pages : 0);
        prev.disabled = state.page === 0;
        next.disabled = state.page >= pages - 1;

        list.innerHTML = slice.length ? slice.map(function (r) {
            return '<article class="civ__item"><h3>' + esc(r.n) + '</h3>'
                + '<span class="civ__where">' + esc(r.c) + (r.s ? ' · ' + esc(r.s) : '') + '</span>'
                + '<span class="civ__addr">' + (r.a ? esc(r.a) : '<em>Adresse non précisée</em>')
                + (r.p ? ' <small>· page source ' + esc(r.p) + '</small>' : '') + '</span></article>';
        }).join('') : '<div class="civ__empty">Aucun centre ne correspond à cette recherche.</div>';

        updateMap(rows);
    }

    function updateMap(rows) {
        if (!map) { return; }
        var byDept = {};
        rows.forEach(function (r) { (byDept[r.d] = byDept[r.d] || []).push(r); });

        var points = [];
        Object.keys(markers).forEach(function (key) {
            var m = markers[key], items = byDept[key] || [], has = items.length > 0, chosen = key === state.d;
            if (has && !map.hasLayer(m)) { m.addTo(map); }
            if (!has && map.hasLayer(m)) { m.removeFrom(map); }
            if (!has) { return; }

            points.push(places[key]);
            m.setStyle({ fillColor: chosen ? '#d8a922' : '#111111', fillOpacity: state.d && !chosen ? .32 : .94 });
            m.setRadius(8 + Math.min(items.length, 220) / 20);

            var first = items.slice(0, 4).map(function (r) {
                return '<li><strong>' + esc(r.n) + '</strong><br>' + esc(r.a || 'Adresse non précisée') + '</li>';
            }).join('');
            var more = items.length - 4;
            m.unbindTooltip().bindTooltip('<strong>' + esc(key) + '</strong><br>' + items.length + ' centres');
            m.unbindPopup().bindPopup('<div class="civ-popup"><strong>' + esc(key) + ' · ' + items.length + ' centres</strong><ol>'
                + first + '</ol>' + (more > 0 ? '<p>+ ' + more + ' autres centres</p>' : '') + '</div>', { maxWidth: 330 });
        });

        visible.innerHTML = 'Départements visibles sur la carte : <b>' + points.length + '</b>';

        if (points.length === 1) {
            map.setView(points[0], 9, { animate: true });
        } else if (points.length > 1) {
            map.fitBounds(points, { padding: [32, 32], maxZoom: 8, animate: true });
        }
    }

    function setDept(value) {
        state.d = value; state.c = ''; state.s = ''; state.page = 0;
        dept.value = value;
        options(commune, value ? all.filter(function (r) { return r.d === value; }) : [], 'c', value ? 'Toutes les communes' : 'Choisissez d’abord un département', '');
        options(section, [], 's', 'Choisissez d’abord une commune', '');
        render();
    }

    dept.addEventListener('change', function () { setDept(dept.value); });
    commune.addEventListener('change', function () {
        state.c = commune.value; state.s = ''; state.page = 0;
        options(section, state.c ? all.filter(function (r) { return r.d === state.d && r.c === state.c; }) : [], 's',
            state.c ? 'Toutes les sections' : 'Choisissez d’abord une commune', '');
        render();
    });
    section.addEventListener('change', function () { state.s = section.value; state.page = 0; render(); });

    var timer = null;
    q.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () { state.q = q.value; state.page = 0; render(); }, 150);
    });

    function go(delta) {
        state.page += delta;
        render();
        var top = list.getBoundingClientRect().top;
        if (top < 80) { window.scrollBy({ top: top - 120, behavior: 'smooth' }); }
    }
    prev.addEventListener('click', function () { go(-1); });
    next.addEventListener('click', function () { go(1); });

    fetch(endpoint, { headers: { Accept: 'application/json' } })
        .then(function (r) { if (!r.ok) { throw new Error(); } return r.json(); })
        .then(function (data) {
            all = data.centres.map(function (r) {
                r._k = norm([r.d, r.c, r.s, r.n, r.a].join(' '));
                return r;
            });

            if (window.L) {
                map = L.map('civ-map', { center: [18.95, -72.7], zoom: 7, minZoom: 6, maxZoom: 12, scrollWheelZoom: false, preferCanvas: true });
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(map);

                Object.keys(places).forEach(function (key) {
                    markers[key] = L.circleMarker(places[key], {
                        radius: 10, color: '#ffffff', weight: 2, fillColor: '#111111', fillOpacity: .94
                    }).on('click', function () {
                        if (state.d !== key) { setDept(key); }
                        // La bulle est reconstruite par le filtre : on la rouvre.
                        markers[key].openPopup();
                    });
                });
            }

            // Lien direct : /elections/ou-voter?departement=NORD
            var wanted = new URLSearchParams(location.search).get('departement');
            if (wanted && places[wanted]) { setDept(wanted); } else { render(); }
        })
        .catch(function () {
            list.innerHTML = '<div class="civ__empty">Le répertoire n’a pas pu être chargé. Vérifiez votre connexion, puis réessayez.</div>';
        });
})();
</script>
@endpush
@endif
