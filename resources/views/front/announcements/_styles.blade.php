<style>
    .anons { padding: 44px 16px 80px; }
    .anons__wrap { max-width: 900px; margin: 0 auto; }
    .anons__head { text-align: center; margin-bottom: 32px; }
    .anons__eyebrow {
        text-transform: uppercase; letter-spacing: .14em; font-size: .78rem;
        font-weight: 700; color: var(--primary-dark); margin: 0 0 8px;
    }
    .anons__title {
        font-family: "Playfair Display", Georgia, serif;
        font-size: clamp(1.8rem, 5vw, 2.6rem); margin: 0 0 12px; line-height: 1.15;
    }
    .anons__lead { color: var(--muted); max-width: 58ch; margin: 0 auto; }
    .anons__filters {
        display: flex; flex-wrap: wrap; gap: 8px; justify-content: center;
        margin: 0 0 28px;
    }
    .anons__filter {
        padding: 8px 16px; border-radius: 999px; font-size: .88rem;
        border: 1.5px solid var(--border); background: var(--surface);
    }
    .anons__filter.is-active {
        background: var(--primary); border-color: var(--primary);
        color: var(--black); font-weight: 600;
    }
    .anons__list { display: grid; gap: 16px; }
    .anons__card {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius); padding: 22px 24px;
        display: block; transition: box-shadow .15s;
    }
    .anons__card:hover { box-shadow: var(--shadow); }
    .anons__tag {
        display: inline-block; padding: 4px 11px; border-radius: 4px;
        background: #2f2a1c; color: var(--primary);
        font-size: .68rem; font-weight: 700; letter-spacing: .1em;
        text-transform: uppercase; margin-bottom: 10px;
    }
    .anons__card h2, .anons__card h3 {
        font-family: "Playfair Display", Georgia, serif;
        margin: 0 0 8px; font-size: 1.25rem; line-height: 1.3;
    }
    .anons__excerpt { color: var(--muted); margin: 0; font-size: .95rem; }
    .anons__date { font-size: .8rem; color: var(--muted); margin-top: 10px; }
    .anons__empty {
        padding: 44px; text-align: center; color: var(--muted);
        background: var(--surface); border: 1px dashed var(--border);
        border-radius: var(--radius);
    }
    .anons__cta {
        margin-top: 34px; text-align: center; background: var(--black);
        color: #fff; border-radius: var(--radius); padding: 30px 24px;
    }
    .anons__cta h2 {
        font-family: "Playfair Display", Georgia, serif;
        margin: 0 0 8px; font-size: 1.45rem;
    }
    .anons__cta p { color: rgba(255,255,255,.75); margin: 0 0 18px; }
    .anons__btn {
        display: inline-block; padding: 13px 28px; border-radius: 999px;
        background: var(--primary); color: var(--black); font-weight: 700;
    }
    .anons__body {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius); padding: 30px;
        white-space: pre-line; line-height: 1.8;
    }
    .anons__photo { border-radius: 10px; margin: 0 auto 22px; }
    .anons__meta {
        margin-top: 22px; padding-top: 16px; border-top: 1px solid var(--border);
        font-size: .84rem; color: var(--muted);
    }
    /* --- formulaire --- */
    .anons__form {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius); box-shadow: var(--shadow);
        padding: 28px; display: grid; gap: 18px;
    }
    .anons__label { display: block; font-weight: 600; font-size: .92rem; margin-bottom: 6px; }
    .anons__opt { font-weight: 400; color: var(--muted); }
    .anons input[type="text"], .anons input[type="email"],
    .anons input[type="tel"], .anons textarea, .anons select {
        width: 100%; padding: 12px 14px; border-radius: 10px;
        border: 1.5px solid var(--border); background: var(--background);
        font: inherit; color: inherit;
    }
    .anons input:focus, .anons textarea:focus, .anons select:focus {
        outline: none; border-color: var(--primary);
    }
    .anons__grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .anons__price {
        display: flex; align-items: baseline; justify-content: space-between;
        padding: 16px 18px; border-radius: 10px; background: var(--background);
        border: 1px solid var(--border);
    }
    .anons__price strong { font-size: 1.6rem; }
    .anons__error, .anons__alert {
        margin: 0; padding: 12px 16px; border-radius: 10px;
        background: #fdecea; border: 1px solid #f5c2bd; color: #8d2419;
        font-size: .92rem;
    }
    .anons__secure { font-size: .82rem; color: var(--muted); text-align: center; margin: 0; }
    @media (max-width: 600px) { .anons__grid { grid-template-columns: 1fr; } }
</style>
