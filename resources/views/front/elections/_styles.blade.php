<style>
    .el { padding: 34px 16px 80px; }
    .el__wrap { max-width: 1180px; margin: 0 auto; }
    .el__eyebrow {
        margin: 0 0 8px; font-size: .74rem; font-weight: 800; letter-spacing: .14em;
        text-transform: uppercase; color: var(--primary-dark);
    }
    .el__title {
        margin: 0 0 12px; font-family: "Playfair Display", Georgia, serif;
        font-size: clamp(1.8rem, 5vw, 2.7rem); line-height: 1.12;
    }
    .el__lead { margin: 0; max-width: 64ch; color: var(--muted); font-size: 1.02rem; }
    .el__h2 {
        display: flex; align-items: center; gap: 10px; margin: 0 0 16px;
        font-family: "Playfair Display", Georgia, serif; font-size: 1.45rem;
    }
    .el__h2::before { content: ""; width: 5px; height: 22px; border-radius: 3px; background: var(--primary); }
    .el__section { margin-top: 44px; }
    .el__more { font-weight: 700; color: var(--primary-dark); }

    /* Bandeau d'avertissement */
    .el__notice {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px;
        margin-bottom: 22px; padding: 10px 16px; border-radius: 10px;
        background: #fff8e6; border: 1px solid #f0d58c; font-size: .88rem;
    }
    .el__notice a { font-weight: 700; color: var(--primary-dark); white-space: nowrap; }

    /* Sous-navigation */
    .el__nav {
        display: flex; gap: 6px; overflow-x: auto; margin: 0 0 26px; padding-bottom: 4px;
        scrollbar-width: none;
    }
    .el__nav::-webkit-scrollbar { display: none; }
    .el__nav a {
        flex: none; padding: 8px 14px; border-radius: 999px; font-size: .86rem; font-weight: 700;
        background: var(--surface); border: 1px solid var(--border); color: var(--text);
    }
    .el__nav a:hover { border-color: var(--primary); }
    .el__nav a.is-active { background: var(--black); border-color: var(--black); color: var(--primary); }

    .el__card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius); padding: 20px;
    }
    .el__grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); }
    .el__source { margin-top: 8px; font-size: .78rem; color: var(--muted); }
    .el__source a { color: inherit; text-decoration: underline; }

    .el__badge {
        display: inline-block; padding: 3px 10px; border-radius: 999px;
        font-size: .68rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
    }
    .el__badge--done { background: #e7f6ec; color: #166534; }
    .el__badge--ongoing { background: #fff1c2; color: #8a5a00; }
    .el__badge--upcoming { background: #e8eefc; color: #1e40af; }
    .el__badge--postponed { background: #fde8e8; color: #9b1c1c; }
    .el__badge--cancelled { background: #eef0f3; color: #4b5563; }

    .el__empty {
        padding: 26px; border: 1px dashed var(--border); border-radius: var(--radius);
        text-align: center; color: var(--muted); background: var(--surface);
    }

    /* Acteurs */
    .el__actor { display: flex; flex-direction: column; gap: 8px; color: inherit; transition: border-color .15s, transform .15s; }
    .el__actor:hover { border-color: var(--primary); transform: translateY(-2px); }
    .el__actor-icon {
        width: 46px; height: 46px; border-radius: 12px; display: grid; place-items: center;
        font-size: 1.45rem; background: var(--background);
    }
    .el__actor strong { font-size: 1.02rem; }
    .el__actor span { font-size: .86rem; color: var(--muted); line-height: 1.5; }

    .el__list { margin: 0; padding: 0; list-style: none; display: grid; gap: 8px; }
    .el__list li { position: relative; padding-left: 26px; line-height: 1.55; }
    .el__list li::before {
        content: "✓"; position: absolute; left: 0; top: 1px; width: 18px; height: 18px; border-radius: 50%;
        display: grid; place-items: center; font-size: .7rem; font-weight: 800;
        background: var(--primary); color: var(--black);
    }

    @media (max-width: 680px) {
        .el { padding-top: 22px; }
        .el__card { padding: 16px; }
    }
</style>
