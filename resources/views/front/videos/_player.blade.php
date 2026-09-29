{{--
    Lecteur vidéo.

    Vidéo YouTube : on affiche d'abord la vignette et on ne charge le
    lecteur de YouTube qu'au clic. La page reste légère, et rien n'est
    envoyé à YouTube tant que le visiteur n'a pas décidé de regarder.

    Vidéo servie par le site : lecteur maison, avec les commandes qu'on
    attend d'un lecteur vidéo — barre de progression, volume, vitesse,
    plein écran, raccourcis clavier.
--}}
@if($video->isYoutube())
    <div class="ply ply--yt" id="ply" data-id="{{ $video->youtube_id }}">
        <button type="button" class="ply__poster" id="ply-poster"
                aria-label="Lire la vidéo : {{ $video->title }}">
            @if($video->thumbUrl())
                <img src="{{ $video->thumbUrl() }}" alt="">
            @endif
            <span class="ply__ytbtn" aria-hidden="true">
                <svg viewBox="0 0 68 48"><path fill="#f00" d="M66.5 7.7a8.6 8.6 0 0 0-6-6C55.2 0 34 0 34 0S12.8 0 7.5 1.7a8.6 8.6 0 0 0-6 6A90 90 0 0 0 0 24a90 90 0 0 0 1.5 16.3 8.6 8.6 0 0 0 6 6C12.8 48 34 48 34 48s21.2 0 26.5-1.7a8.6 8.6 0 0 0 6-6A90 90 0 0 0 68 24a90 90 0 0 0-1.5-16.3Z"/><path fill="#fff" d="M45 24 27 14v20l18-10Z"/></svg>
            </span>
        </button>
    </div>
@else
    <div class="ply" id="ply" tabindex="0">
        <video id="ply-video" playsinline preload="metadata"
               @if($video->thumbUrl()) poster="{{ $video->thumbUrl() }}" @endif>
            <source src="{{ $video->fileUrl() }}" type="video/mp4">
            Votre navigateur ne peut pas lire cette vidéo.
        </video>

        <button type="button" class="ply__big" id="ply-big" aria-label="Lire">
            <svg viewBox="0 0 24 24"><path fill="currentColor" d="M8 5v14l11-7z"/></svg>
        </button>

        {{-- Retours visuels d'un saut de 10 s, comme sur mobile. --}}
        <span class="ply__seek ply__seek--back" id="ply-seek-back" aria-hidden="true">−10 s</span>
        <span class="ply__seek ply__seek--fwd" id="ply-seek-fwd" aria-hidden="true">+10 s</span>
        <span class="ply__spinner" id="ply-spinner" hidden aria-hidden="true"></span>

        <div class="ply__bar" id="ply-bar">
            <div class="ply__track" id="ply-track" role="slider" tabindex="0"
                 aria-label="Progression" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                <span class="ply__buffer" id="ply-buffer"></span>
                <span class="ply__played" id="ply-played"><span class="ply__knob"></span></span>
                <span class="ply__tip" id="ply-tip" hidden>0:00</span>
            </div>

            <div class="ply__btns">
                <button type="button" class="ply__btn" id="ply-play" aria-label="Lire">
                    <svg viewBox="0 0 24 24" class="ply__i-play"><path fill="currentColor" d="M8 5v14l11-7z"/></svg>
                    <svg viewBox="0 0 24 24" class="ply__i-pause" hidden><path fill="currentColor" d="M6 5h4v14H6zM14 5h4v14h-4z"/></svg>
                </button>

                <div class="ply__vol">
                    <button type="button" class="ply__btn" id="ply-mute" aria-label="Couper le son">
                        <svg viewBox="0 0 24 24" class="ply__i-son"><path fill="currentColor" d="M4 9v6h4l5 5V4L8 9H4zm12.5 3a4.5 4.5 0 0 0-2.5-4v8a4.5 4.5 0 0 0 2.5-4zM14 2v2a8 8 0 0 1 0 16v2a10 10 0 0 0 0-20z"/></svg>
                        <svg viewBox="0 0 24 24" class="ply__i-mute" hidden><path fill="currentColor" d="M4 9v6h4l5 5V4L8 9H4zm15.5 3 2.5-2.5-1.4-1.4L18 10.6 15.4 8 14 9.4l2.6 2.6L14 14.6l1.4 1.4 2.6-2.6 2.6 2.6 1.4-1.4L19.5 12z"/></svg>
                    </button>
                    <input type="range" id="ply-volume" min="0" max="1" step="0.05" value="1" aria-label="Volume">
                </div>

                <span class="ply__time"><b id="ply-cur">0:00</b> / <span id="ply-dur">0:00</span></span>

                <span class="ply__spacer"></span>

                <div class="ply__menu">
                    <button type="button" class="ply__btn" id="ply-gear" aria-label="Vitesse de lecture" aria-expanded="false">
                        <svg viewBox="0 0 24 24"><path fill="currentColor" d="M19.4 13a7.8 7.8 0 0 0 0-2l2-1.6-2-3.4-2.5 1a7.6 7.6 0 0 0-1.7-1l-.4-2.6h-3.9l-.4 2.6c-.6.2-1.2.6-1.7 1l-2.4-1-2 3.4L6.6 11a7.8 7.8 0 0 0 0 2l-2 1.6 2 3.4 2.4-1c.5.4 1.1.8 1.7 1l.4 2.6h3.9l.4-2.6c.6-.2 1.2-.6 1.7-1l2.5 1 2-3.4-2.2-1.6zM12 15.5a3.5 3.5 0 1 1 0-7 3.5 3.5 0 0 1 0 7z"/></svg>
                    </button>
                    <div class="ply__list" id="ply-speeds" hidden>
                        @foreach(['0.5', '0.75', '1', '1.25', '1.5', '2'] as $rate)
                            <button type="button" data-rate="{{ $rate }}" class="{{ $rate === '1' ? 'is-on' : '' }}">
                                {{ $rate === '1' ? 'Normale' : $rate.'×' }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <button type="button" class="ply__btn" id="ply-full" aria-label="Plein écran">
                    <svg viewBox="0 0 24 24" class="ply__i-full"><path fill="currentColor" d="M7 14H5v5h5v-2H7v-3zm-2-4h2V7h3V5H5v5zm12 7h-3v2h5v-5h-2v3zM14 5v2h3v3h2V5h-5z"/></svg>
                    <svg viewBox="0 0 24 24" class="ply__i-exit" hidden><path fill="currentColor" d="M5 16h3v3h2v-5H5v2zm3-8H5v2h5V5H8v3zm6 11h2v-3h3v-2h-5v5zm2-11V5h-2v5h5V8h-3z"/></svg>
                </button>
            </div>
        </div>
    </div>
@endif

@push('scripts')
@if($video->isYoutube())
<script>
(function () {
    var box = document.getElementById('ply');
    var poster = document.getElementById('ply-poster');
    if (!box || !poster) { return; }

    poster.addEventListener('click', function () {
        var frame = document.createElement('iframe');
        frame.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(box.dataset.id)
            + '?autoplay=1&rel=0&modestbranding=1&playsinline=1';
        frame.title = @json($video->title);
        frame.allow = 'accelerometer; autoplay; encrypted-media; picture-in-picture; fullscreen';
        frame.allowFullscreen = true;
        frame.setAttribute('frameborder', '0');
        box.innerHTML = '';
        box.appendChild(frame);
    });
})();
</script>
@else
<script>
(function () {
    var box = document.getElementById('ply');
    var v = document.getElementById('ply-video');
    if (!box || !v) { return; }

    var $ = function (id) { return document.getElementById(id); };
    var track = $('ply-track'), played = $('ply-played'), buffer = $('ply-buffer'), tip = $('ply-tip');
    var cur = $('ply-cur'), dur = $('ply-dur'), big = $('ply-big'), bar = $('ply-bar');
    var volume = $('ply-volume'), speeds = $('ply-speeds'), gear = $('ply-gear'), spinner = $('ply-spinner');
    var hideTimer = null;

    function fmt(s) {
        s = Math.max(0, Math.floor(s || 0));
        var h = Math.floor(s / 3600), m = Math.floor((s % 3600) / 60), x = s % 60;
        return h ? h + ':' + String(m).padStart(2, '0') + ':' + String(x).padStart(2, '0')
                 : m + ':' + String(x).padStart(2, '0');
    }
    function icons(a, b, on) {
        box.querySelector(a).hidden = on;
        box.querySelector(b).hidden = !on;
    }

    /* ---------------- Lecture ---------------- */
    function toggle() { v.paused ? v.play() : v.pause(); }

    v.addEventListener('play', function () {
        box.classList.add('is-playing');
        icons('.ply__i-play', '.ply__i-pause', true);
        $('ply-play').setAttribute('aria-label', 'Pause');
        idle();
    });
    v.addEventListener('pause', function () {
        box.classList.remove('is-playing');
        icons('.ply__i-play', '.ply__i-pause', false);
        $('ply-play').setAttribute('aria-label', 'Lire');
        box.classList.remove('is-idle');
    });
    v.addEventListener('waiting', function () { spinner.hidden = false; });
    v.addEventListener('playing', function () { spinner.hidden = true; });
    v.addEventListener('ended', function () { box.classList.remove('is-playing'); box.classList.remove('is-idle'); });

    big.addEventListener('click', toggle);
    $('ply-play').addEventListener('click', toggle);
    v.addEventListener('click', toggle);

    /* ---------------- Progression ---------------- */
    v.addEventListener('loadedmetadata', function () { dur.textContent = fmt(v.duration); });
    v.addEventListener('timeupdate', function () {
        var pct = v.duration ? (v.currentTime / v.duration) * 100 : 0;
        played.style.width = pct + '%';
        cur.textContent = fmt(v.currentTime);
        track.setAttribute('aria-valuenow', Math.round(pct));
    });
    v.addEventListener('progress', function () {
        if (v.buffered.length && v.duration) {
            buffer.style.width = (v.buffered.end(v.buffered.length - 1) / v.duration) * 100 + '%';
        }
    });

    function ratioAt(e) {
        var r = track.getBoundingClientRect();
        var x = (e.touches ? e.touches[0].clientX : e.clientX) - r.left;
        return Math.min(1, Math.max(0, x / r.width));
    }
    function seekTo(e) { if (v.duration) { v.currentTime = ratioAt(e) * v.duration; } }

    var dragging = false;
    track.addEventListener('pointerdown', function (e) {
        dragging = true; track.setPointerCapture(e.pointerId); seekTo(e);
    });
    track.addEventListener('pointermove', function (e) {
        if (dragging) { seekTo(e); }
        if (v.duration) {
            tip.hidden = false;
            tip.textContent = fmt(ratioAt(e) * v.duration);
            tip.style.left = (ratioAt(e) * 100) + '%';
        }
    });
    track.addEventListener('pointerup', function () { dragging = false; });
    track.addEventListener('pointerleave', function () { dragging = false; tip.hidden = true; });

    /* ---------------- Son ---------------- */
    var saved = null;
    try { saved = localStorage.getItem('ln_player_volume'); } catch (e) {}
    if (saved !== null) { v.volume = parseFloat(saved); volume.value = saved; }

    volume.addEventListener('input', function () {
        v.volume = parseFloat(volume.value);
        v.muted = v.volume === 0;
        try { localStorage.setItem('ln_player_volume', volume.value); } catch (e) {}
    });
    $('ply-mute').addEventListener('click', function () { v.muted = !v.muted; });
    v.addEventListener('volumechange', function () {
        icons('.ply__i-son', '.ply__i-mute', v.muted || v.volume === 0);
        volume.value = v.muted ? 0 : v.volume;
    });

    /* ---------------- Vitesse ---------------- */
    gear.addEventListener('click', function () {
        speeds.hidden = !speeds.hidden;
        gear.setAttribute('aria-expanded', String(!speeds.hidden));
    });
    speeds.querySelectorAll('button').forEach(function (b) {
        b.addEventListener('click', function () {
            v.playbackRate = parseFloat(b.dataset.rate);
            speeds.querySelectorAll('button').forEach(function (o) { o.classList.remove('is-on'); });
            b.classList.add('is-on');
            speeds.hidden = true;
            gear.setAttribute('aria-expanded', 'false');
        });
    });
    document.addEventListener('click', function (e) {
        if (!speeds.hidden && !e.target.closest('.ply__menu')) { speeds.hidden = true; }
    });

    /* ---------------- Plein écran ---------------- */
    function full() {
        if (document.fullscreenElement) { document.exitFullscreen(); }
        else if (box.requestFullscreen) { box.requestFullscreen(); }
        else if (v.webkitEnterFullscreen) { v.webkitEnterFullscreen(); } // iPhone
    }
    $('ply-full').addEventListener('click', full);
    v.addEventListener('dblclick', full);
    document.addEventListener('fullscreenchange', function () {
        var on = document.fullscreenElement === box;
        box.classList.toggle('is-full', on);
        icons('.ply__i-full', '.ply__i-exit', on);
    });

    /* ---------------- Sauts de 10 s ---------------- */
    function jump(sec) {
        v.currentTime = Math.min(v.duration || 0, Math.max(0, v.currentTime + sec));
        var el = $(sec < 0 ? 'ply-seek-back' : 'ply-seek-fwd');
        el.classList.add('is-on');
        setTimeout(function () { el.classList.remove('is-on'); }, 450);
    }

    var lastTap = 0;
    v.addEventListener('pointerup', function (e) {
        if (e.pointerType !== 'touch') { return; }
        var now = Date.now();
        if (now - lastTap < 300) {
            var r = v.getBoundingClientRect();
            jump(e.clientX - r.left < r.width / 2 ? -10 : 10);
        }
        lastTap = now;
    });

    /* ---------------- Clavier ---------------- */
    box.addEventListener('keydown', function (e) {
        var k = e.key.toLowerCase();
        var handled = true;

        if (k === ' ' || k === 'k') { toggle(); }
        else if (k === 'arrowright') { jump(5); }
        else if (k === 'arrowleft') { jump(-5); }
        else if (k === 'l') { jump(10); }
        else if (k === 'j') { jump(-10); }
        else if (k === 'arrowup') { v.volume = Math.min(1, v.volume + 0.1); }
        else if (k === 'arrowdown') { v.volume = Math.max(0, v.volume - 0.1); }
        else if (k === 'm') { v.muted = !v.muted; }
        else if (k === 'f') { full(); }
        else if (k >= '0' && k <= '9' && v.duration) { v.currentTime = v.duration * (parseInt(k, 10) / 10); }
        else { handled = false; }

        if (handled) { e.preventDefault(); idle(); }
    });

    /* ---------------- Commandes qui s'effacent ---------------- */
    function idle() {
        box.classList.remove('is-idle');
        clearTimeout(hideTimer);
        if (!v.paused) {
            hideTimer = setTimeout(function () { box.classList.add('is-idle'); }, 2600);
        }
    }
    ['pointermove', 'pointerdown'].forEach(function (evt) { box.addEventListener(evt, idle); });
    bar.addEventListener('pointerenter', function () { clearTimeout(hideTimer); box.classList.remove('is-idle'); });
})();
</script>
@endif
@endpush
