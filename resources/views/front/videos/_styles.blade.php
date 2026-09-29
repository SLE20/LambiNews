<style>
    .vid { padding: 22px 16px 70px; }
    .vid__wrap { max-width: 1500px; margin: 0 auto; }

    /* ---------- Puces de rubriques ---------- */
    .vid__chips {
        position: sticky; top: 0; z-index: 40;
        display: flex; align-items: center; gap: 8px;
        padding: 10px 0 12px; margin-bottom: 18px;
        overflow-x: auto; scrollbar-width: none;
        background: var(--background);
        border-bottom: 1px solid var(--border);
    }
    .vid__chips::-webkit-scrollbar { display: none; }
    .vid__chip {
        flex: none; padding: 8px 14px; border-radius: 999px;
        background: var(--surface); border: 1px solid var(--border);
        color: var(--text); font-size: .88rem; font-weight: 600; white-space: nowrap;
    }
    .vid__chip small { opacity: .55; font-size: .78rem; }
    .vid__chip:hover { border-color: var(--primary); }
    .vid__chip.is-active { background: var(--black); border-color: var(--black); color: #fff; }
    .vid__chip.is-active small { opacity: .7; }
    .vid__search { flex: none; margin-left: auto; }
    .vid__search input {
        width: 230px; height: 38px; padding: 0 14px; border-radius: 999px;
        border: 1px solid var(--border); background: var(--surface); font: inherit; font-size: .88rem;
    }
    .vid__search input:focus { outline: 2px solid var(--primary); outline-offset: 1px; }

    /* ---------- Vidéo à la une ---------- */
    .vid__hero {
        display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr);
        gap: 22px; margin-bottom: 30px; color: inherit;
    }
    .vid__hero-thumb {
        position: relative; display: block; aspect-ratio: 16/9; overflow: hidden;
        border-radius: 14px; background: #0b0b0b;
    }
    .vid__hero-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .vid__hero-body { display: flex; flex-direction: column; justify-content: center; gap: 8px; min-width: 0; }
    .vid__eyebrow {
        font-size: .7rem; font-weight: 800; letter-spacing: .12em;
        text-transform: uppercase; color: var(--primary-dark);
    }
    .vid__hero-title {
        font-family: "Playfair Display", Georgia, serif;
        font-size: clamp(1.3rem, 2.6vw, 1.9rem); line-height: 1.25;
    }
    .vid__hero-desc { color: var(--muted); font-size: .94rem; line-height: 1.6; }
    .vid__cta {
        align-self: flex-start; margin-top: 6px; padding: 10px 18px; border-radius: 999px;
        background: var(--primary); color: var(--black); font-weight: 800; font-size: .9rem;
    }
    .vid__play {
        position: absolute; inset: 0; margin: auto; width: 68px; height: 68px;
        display: grid; place-items: center; border-radius: 50%;
        background: rgba(0, 0, 0, .65); color: #fff;
        transition: background .15s, transform .15s;
    }
    .vid__play svg { width: 34px; height: 34px; margin-left: 3px; }
    .vid__hero:hover .vid__play { background: #d20a11; transform: scale(1.06); }

    /* ---------- Grille ---------- */
    .vid__grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
        gap: 26px 16px;
    }
    .vid__card { display: block; color: inherit; }
    .vid__thumb {
        position: relative; display: block; aspect-ratio: 16/9; overflow: hidden;
        border-radius: 12px; background: #0b0b0b;
        transition: border-radius .15s;
    }
    .vid__thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .vid__card:hover .vid__thumb { border-radius: 4px; }
    .vid__noimg { position: absolute; inset: 0; display: grid; place-items: center; color: #4a4a4a; font-size: 2rem; }
    .vid__time {
        position: absolute; right: 7px; bottom: 7px; padding: 2px 6px; border-radius: 5px;
        background: rgba(0, 0, 0, .82); color: #fff; font-size: .76rem; font-weight: 700;
        font-variant-numeric: tabular-nums;
    }
    .vid__info { display: flex; gap: 12px; padding: 12px 2px 0; }
    .vid__avatar {
        flex: none; width: 36px; height: 36px; border-radius: 50%; display: grid; place-items: center;
        background: var(--black); color: var(--primary); font-size: .74rem; font-weight: 800;
    }
    .vid__text { min-width: 0; }
    .vid__title {
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        font-size: .98rem; font-weight: 700; line-height: 1.35;
    }
    .vid__channel, .vid__meta { display: block; margin-top: 3px; font-size: .84rem; color: var(--muted); }
    .vid__card:hover .vid__channel { color: var(--text); }

    .vid__empty {
        padding: 50px 20px; text-align: center; color: var(--muted);
        background: var(--surface); border: 1px dashed var(--border); border-radius: var(--radius);
    }
    .vid__pages { margin-top: 28px; }

    @media (max-width: 900px) {
        .vid__hero { grid-template-columns: minmax(0, 1fr); gap: 14px; }
        .vid__search { width: 100%; margin: 0; }
        .vid__search input { width: 100%; }
        .vid__chips { flex-wrap: wrap; }
    }
    @media (max-width: 560px) {
        .vid { padding-top: 14px; }
        .vid__grid { grid-template-columns: minmax(0, 1fr); gap: 20px; }
        .vid__thumb, .vid__hero-thumb { border-radius: 0; margin: 0 -16px; }
        .vid__thumb { width: calc(100% + 32px); }
    }
</style>
