<style>
    .vid--watch { padding-top: 16px; }
    .wat { display: grid; grid-template-columns: minmax(0, 1fr) 380px; gap: 26px; align-items: start; }
    .wat__main { min-width: 0; }

    /* ---------- Lecteur ---------- */
    .ply {
        position: relative; width: 100%; aspect-ratio: 16/9; overflow: hidden;
        border-radius: 12px; background: #000; color: #fff; outline: none;
    }
    .ply video, .ply iframe { width: 100%; height: 100%; display: block; border: 0; }
    .ply.is-full { border-radius: 0; }
    .ply.is-idle { cursor: none; }
    .ply.is-idle .ply__bar { opacity: 0; transform: translateY(8px); pointer-events: none; }

    .ply__poster { position: absolute; inset: 0; width: 100%; padding: 0; border: 0; background: #000; cursor: pointer; }
    .ply__poster img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .ply__ytbtn { position: absolute; inset: 0; margin: auto; width: 68px; height: 48px; }
    .ply__ytbtn svg { width: 100%; height: 100%; filter: drop-shadow(0 2px 8px rgba(0,0,0,.4)); }
    .ply__poster:hover .ply__ytbtn { transform: scale(1.08); transition: transform .15s; }

    .ply__big {
        position: absolute; inset: 0; margin: auto; width: 76px; height: 76px;
        display: grid; place-items: center; border: 0; border-radius: 50%; cursor: pointer;
        background: rgba(0, 0, 0, .55); color: #fff; transition: opacity .2s, transform .2s;
    }
    .ply__big svg { width: 38px; height: 38px; margin-left: 4px; }
    .ply__big:hover { background: #d20a11; transform: scale(1.05); }
    .ply.is-playing .ply__big { opacity: 0; pointer-events: none; }

    .ply__spinner {
        position: absolute; inset: 0; margin: auto; width: 46px; height: 46px; border-radius: 50%;
        border: 3px solid rgba(255, 255, 255, .25); border-top-color: #fff;
        animation: ply-spin .8s linear infinite;
    }
    @keyframes ply-spin { to { transform: rotate(360deg); } }

    .ply__seek {
        position: absolute; top: 50%; transform: translateY(-50%); padding: 10px 16px;
        border-radius: 999px; background: rgba(0, 0, 0, .6); font-size: .9rem; font-weight: 700;
        opacity: 0; transition: opacity .2s; pointer-events: none;
    }
    .ply__seek--back { left: 8%; }
    .ply__seek--fwd { right: 8%; }
    .ply__seek.is-on { opacity: 1; }

    /* ---------- Barre de commandes ---------- */
    .ply__bar {
        position: absolute; left: 0; right: 0; bottom: 0; padding: 22px 12px 8px;
        background: linear-gradient(transparent, rgba(0, 0, 0, .78));
        transition: opacity .25s, transform .25s;
    }
    .ply__track { position: relative; height: 16px; cursor: pointer; touch-action: none; }
    .ply__track::before {
        content: ""; position: absolute; left: 0; right: 0; top: 7px; height: 3px;
        background: rgba(255, 255, 255, .3); border-radius: 3px;
    }
    .ply__buffer, .ply__played {
        position: absolute; left: 0; top: 7px; height: 3px; border-radius: 3px; width: 0;
    }
    .ply__buffer { background: rgba(255, 255, 255, .45); }
    .ply__played { background: #d20a11; }
    .ply__knob {
        position: absolute; right: -6px; top: -4.5px; width: 12px; height: 12px; border-radius: 50%;
        background: #d20a11; transform: scale(0); transition: transform .15s;
    }
    .ply__track:hover .ply__knob, .ply__track:focus-visible .ply__knob { transform: scale(1); }
    .ply__track:hover::before, .ply__track:hover .ply__buffer, .ply__track:hover .ply__played { height: 5px; top: 6px; }
    .ply__tip {
        position: absolute; bottom: 18px; transform: translateX(-50%); padding: 2px 7px; border-radius: 4px;
        background: rgba(0, 0, 0, .85); font-size: .74rem; font-variant-numeric: tabular-nums; white-space: nowrap;
    }

    .ply__btns { display: flex; align-items: center; gap: 4px; margin-top: 2px; }
    .ply__btn {
        width: 38px; height: 38px; display: grid; place-items: center; flex: none;
        border: 0; border-radius: 50%; background: none; color: #fff; cursor: pointer;
    }
    .ply__btn svg { width: 22px; height: 22px; }
    .ply__btn:hover { background: rgba(255, 255, 255, .15); }
    .ply__btn:focus-visible { outline: 2px solid #fff; outline-offset: -2px; }
    .ply__spacer { flex: 1; }
    .ply__time { font-size: .82rem; font-variant-numeric: tabular-nums; margin-left: 4px; white-space: nowrap; }

    .ply__vol { display: flex; align-items: center; }
    .ply__vol input {
        width: 0; opacity: 0; accent-color: #fff; cursor: pointer;
        transition: width .18s ease, opacity .18s ease;
    }
    .ply__vol:hover input, .ply__vol input:focus-visible { width: 72px; opacity: 1; margin-left: 4px; }

    .ply__menu { position: relative; }
    .ply__list {
        position: absolute; right: 0; bottom: 46px; min-width: 132px; padding: 6px;
        border-radius: 10px; background: rgba(20, 20, 20, .96); box-shadow: 0 8px 24px rgba(0,0,0,.4);
    }
    .ply__list button {
        display: block; width: 100%; padding: 8px 12px; border: 0; border-radius: 7px;
        background: none; color: #fff; text-align: left; font: inherit; font-size: .86rem; cursor: pointer;
    }
    .ply__list button:hover { background: rgba(255, 255, 255, .14); }
    .ply__list button.is-on { color: var(--primary); font-weight: 800; }

    /* ---------- Sous le lecteur ---------- */
    .wat__title {
        margin: 16px 0 12px; font-family: "Playfair Display", Georgia, serif;
        font-size: clamp(1.25rem, 2.6vw, 1.7rem); line-height: 1.3;
    }
    .wat__row {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
        gap: 12px; padding-bottom: 14px;
    }
    .wat__channel { display: flex; align-items: center; gap: 12px; }
    .wat__channel b { display: block; font-size: .96rem; }
    .wat__channel small { color: var(--muted); font-size: .82rem; }
    .wat__sub {
        margin-left: 6px; padding: 9px 18px; border-radius: 999px;
        background: var(--black); color: #fff; font-size: .86rem; font-weight: 800;
    }
    .wat__sub:hover { background: var(--primary); color: var(--black); }
    .wat__actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .wat__act {
        padding: 9px 15px; border: 0; border-radius: 999px; cursor: pointer;
        background: var(--surface); border: 1px solid var(--border);
        color: var(--text); font: inherit; font-size: .84rem; font-weight: 700;
    }
    .wat__act:hover { border-color: var(--primary); color: var(--text); }

    .wat__desc {
        padding: 14px 16px; border-radius: 12px; background: var(--surface); border: 1px solid var(--border);
    }
    .wat__stats { margin: 0 0 8px; font-size: .88rem; font-weight: 700; }
    .wat__stats a { color: var(--primary-dark); }
    .wat__text {
        display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
        white-space: pre-line; line-height: 1.65; font-size: .94rem;
    }
    .wat__desc.is-open .wat__text { -webkit-line-clamp: unset; }
    .wat__more {
        margin-top: 6px; padding: 0; border: 0; background: none; cursor: pointer;
        color: var(--text); font: inherit; font-weight: 800; font-size: .86rem;
    }

    /* ---------- Colonne « À suivre » ---------- */
    .wat__sidetitle { margin: 0 0 12px; font-size: 1rem; font-weight: 800; }
    .wat__next { display: grid; grid-template-columns: 168px minmax(0, 1fr); gap: 10px; margin-bottom: 12px; color: inherit; }
    .wat__nextthumb {
        position: relative; aspect-ratio: 16/9; border-radius: 10px; overflow: hidden; background: #0b0b0b;
    }
    .wat__nextthumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .wat__nextbody strong {
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        font-size: .9rem; line-height: 1.32;
    }
    .wat__nextbody small { display: block; margin-top: 3px; font-size: .79rem; color: var(--muted); }
    .wat__next:hover strong { color: var(--primary-dark); }
    .wat__none { color: var(--muted); font-size: .9rem; }
    .wat__all { display: inline-block; margin-top: 8px; font-weight: 700; color: var(--primary-dark); }

    @media (max-width: 1100px) {
        .wat { grid-template-columns: minmax(0, 1fr); }
        .wat__side { border-top: 1px solid var(--border); padding-top: 18px; }
        .wat__next { grid-template-columns: 200px minmax(0, 1fr); }
    }
    @media (max-width: 560px) {
        .vid--watch { padding-top: 0; }
        .ply { margin: 0 -16px; width: calc(100% + 32px); border-radius: 0; }
        .wat__next { grid-template-columns: 148px minmax(0, 1fr); }
        .wat__actions { width: 100%; }
    }
</style>
