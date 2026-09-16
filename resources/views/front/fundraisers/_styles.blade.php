<style>
    .fr { padding: 44px 16px 80px; }
    .fr__wrap { max-width: 1060px; margin: 0 auto; }
    .fr__head { text-align: center; margin-bottom: 34px; }
    .fr__eyebrow {
        text-transform: uppercase; letter-spacing: .14em; font-size: .76rem;
        font-weight: 700; color: var(--primary-dark); margin: 0 0 8px;
    }
    .fr__title {
        font-family: "Playfair Display", Georgia, serif;
        font-size: clamp(1.8rem, 5vw, 2.6rem); margin: 0 0 12px; line-height: 1.15;
    }
    .fr__lead { color: var(--muted); max-width: 58ch; margin: 0 auto; }

    .fr__grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 20px; }
    .fr__card {
        display: block; background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius); overflow: hidden; color: inherit;
        transition: border-color .15s, box-shadow .15s, transform .15s;
    }
    .fr__card:hover { border-color: var(--primary); box-shadow: var(--shadow); transform: translateY(-2px); }
    .fr__cover { aspect-ratio: 16/9; background: var(--border); overflow: hidden; }
    .fr__cover img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .fr__body { padding: 18px; }
    .fr__cardtitle {
        font-family: "Playfair Display", Georgia, serif;
        font-size: 1.12rem; line-height: 1.32; margin: 0 0 8px;
    }
    .fr__summary { color: var(--muted); font-size: .88rem; margin: 0 0 14px; }

    /* ---------- Barre de progression ---------- */
    .fr__bar { height: 9px; border-radius: 999px; background: var(--border); overflow: hidden; }
    .fr__fill { display: block; height: 100%; border-radius: 999px; background: var(--primary); }
    .fr__amounts {
        display: flex; justify-content: space-between; gap: 10px;
        margin-top: 9px; font-size: .84rem;
    }
    .fr__raised { font-weight: 800; color: var(--primary-dark); }
    .fr__goal { color: var(--muted); }
    .fr__meta { margin-top: 10px; font-size: .78rem; color: var(--muted); }
    .fr__badge {
        display: inline-block; padding: 3px 10px; border-radius: 999px;
        font-size: .68rem; font-weight: 800; letter-spacing: .08em;
        text-transform: uppercase; background: #e8f5ec; color: #166534;
    }
    .fr__badge--closed { background: #f1f5f9; color: #64748b; }

    /* ---------- Page de détail ---------- */
    .fr__detail { display: grid; grid-template-columns: minmax(0,1fr) 360px; gap: 28px; align-items: start; }
    .fr__hero { border-radius: var(--radius); overflow: hidden; margin-bottom: 22px; }
    .fr__hero img { width: 100%; display: block; }
    .fr__story { font-size: 1rem; line-height: 1.8; white-space: pre-line; }
    .fr__panel {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius); padding: 22px; margin-bottom: 18px;
    }
    .fr__big { font-size: 1.9rem; font-weight: 800; color: var(--primary-dark); line-height: 1.1; }
    .fr__sub { color: var(--muted); font-size: .86rem; margin-top: 4px; }
    .fr__stats { display: flex; gap: 18px; margin-top: 16px; font-size: .84rem; }
    .fr__stats b { display: block; font-size: 1.1rem; }

    .fr__label { display: block; font-weight: 600; font-size: .9rem; margin-bottom: 6px; }
    .fr__opt { font-weight: 400; color: var(--muted); }
    .fr input[type=text], .fr input[type=email], .fr input[type=tel],
    .fr input[type=number], .fr textarea {
        width: 100%; padding: 11px 13px; border-radius: 10px;
        border: 1.5px solid var(--border); background: var(--background);
        font: inherit; color: inherit;
    }
    .fr input:focus, .fr textarea:focus { outline: none; border-color: var(--primary); }
    .fr__chips { display: grid; grid-template-columns: repeat(4,1fr); gap: 8px; margin: 10px 0; }
    .fr__chip {
        padding: 11px 4px; border-radius: 10px; cursor: pointer; font: inherit;
        border: 1.5px solid var(--border); background: var(--background); font-weight: 700;
    }
    .fr__chip.is-active { background: var(--primary); border-color: var(--primary); color: var(--black); }
    .fr__methods { display: grid; gap: 10px; margin: 14px 0; }
    .fr__method {
        display: flex; align-items: center; gap: 11px; padding: 12px 14px;
        border: 1.5px solid var(--border); border-radius: 10px;
        background: var(--background); cursor: pointer;
    }
    .fr__method:has(input:checked) { border-color: var(--primary); background: #fdf8e9; }
    .fr__method input { accent-color: var(--primary); }
    .fr__method small { display: block; color: var(--muted); font-size: .78rem; }
    .fr__submit {
        width: 100%; padding: 13px; border: 0; border-radius: 999px;
        background: var(--black); color: #fff; font: inherit; font-weight: 700; cursor: pointer;
    }
    .fr__submit:hover { background: var(--black-light); }
    .fr__err, .fr__info {
        margin: 0 0 12px; padding: 11px 15px; border-radius: 10px; font-size: .88rem;
        background: #fdecea; border: 1px solid #f5c2bd; color: #8d2419;
    }
    .fr__info { background: #eef6fd; border-color: #c3ddf5; color: #14507f; }
    .fr__donors { display: grid; gap: 10px; }
    .fr__donor {
        display: flex; justify-content: space-between; gap: 10px;
        padding-bottom: 9px; border-bottom: 1px solid var(--border); font-size: .88rem;
    }
    .fr__donor:last-child { border-bottom: 0; }
    .fr__donor b { color: var(--primary-dark); }
    .fr__empty {
        padding: 40px; text-align: center; color: var(--muted);
        background: var(--surface); border: 1px dashed var(--border); border-radius: var(--radius);
    }
    @media (max-width: 900px) { .fr__detail { grid-template-columns: 1fr; } }
</style>
