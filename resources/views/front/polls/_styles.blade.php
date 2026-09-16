<style>
    .poll {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius); padding: 24px;
    }
    .poll--compact { padding: 18px; }
    .poll__eyebrow {
        text-transform: uppercase; letter-spacing: .12em; font-size: .7rem;
        font-weight: 700; color: var(--primary-dark); margin: 0 0 8px;
    }
    .poll__question {
        font-family: "Playfair Display", Georgia, serif;
        font-size: 1.2rem; line-height: 1.35; margin: 0 0 8px;
    }
    .poll__desc { color: var(--muted); font-size: .9rem; margin: 0 0 14px; }
    .poll__form { display: grid; gap: 10px; margin: 14px 0 0; }
    .poll__choice {
        display: flex; align-items: flex-start; gap: 10px; cursor: pointer;
        padding: 11px 14px; border-radius: 10px;
        border: 1.5px solid var(--border); background: var(--background);
    }
    .poll__choice:hover { border-color: var(--primary); }
    .poll__choice input { margin-top: 3px; accent-color: var(--primary); }
    .poll__choice small { display: block; color: var(--muted); font-size: .82rem; }
    .poll__trap { position: absolute; left: -9999px; height: 0; overflow: hidden; }
    .poll__submit {
        margin-top: 4px; padding: 12px 22px; border: 0; border-radius: 999px;
        background: var(--black); color: #fff; font: inherit; font-weight: 700;
        cursor: pointer;
    }
    .poll__submit:hover { background: var(--black-light); }
    .poll__results { display: grid; gap: 14px; margin-top: 14px; }
    .poll__rowhead {
        display: flex; justify-content: space-between; gap: 12px;
        font-size: .92rem; margin-bottom: 5px;
    }
    .poll__track {
        height: 10px; border-radius: 999px; background: var(--border);
        overflow: hidden;
    }
    .poll__fill {
        display: block; height: 100%; background: var(--primary);
        border-radius: 999px;
    }
    .poll__row.is-mine .poll__fill { background: var(--primary-dark); }
    .poll__row.is-mine .poll__rowhead strong::after {
        content: " ✓"; color: var(--primary-dark);
    }
    .poll__count { font-size: .78rem; color: var(--muted); }
    .poll__meta { margin: 16px 0 0; font-size: .82rem; color: var(--muted); }
    .poll__disclaimer {
        margin: 10px 0 0; padding-top: 10px;
        border-top: 1px solid var(--border);
        font-size: .76rem; color: var(--muted); line-height: 1.5;
    }

    /* ---------- Comparaison avec photos ---------- */
    /*
     * Quatre colonnes au plus : au-delà les visages deviennent trop
     * petits pour qu'on distingue les candidats.
     */
    .poll__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 14px;
        margin: 16px 0 4px;
        max-width: 100%;
    }
    @media (min-width: 720px) {
        .poll__grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    }
    @media (max-width: 620px) {
        .poll__grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    }
    .poll--compact .poll__grid {
        grid-template-columns: repeat(auto-fit, minmax(112px, 1fr));
        gap: 10px;
    }
    .poll__card {
        margin: 0;
        padding: 12px;
        border: 1.5px solid var(--border);
        border-radius: 12px;
        background: var(--background);
        text-align: center;
        position: relative;
    }
    .poll__card--choice { cursor: pointer; display: block; }
    .poll__card--choice:hover { border-color: var(--primary); }
    .poll__card--choice input {
        position: absolute; top: 10px; left: 10px;
        accent-color: var(--primary); width: 17px; height: 17px;
    }
    .poll__card--choice:has(input:checked) {
        border-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(216,169,34,.28);
    }
    .poll__photo {
        width: 84px; height: 84px; margin: 0 auto 10px;
        border-radius: 50%; overflow: hidden;
        background: var(--border);
        display: grid; place-items: center;
    }
    .poll--compact .poll__photo { width: 64px; height: 64px; }
    .poll__photo img { width: 100%; height: 100%; object-fit: cover; }
    .poll__initials {
        font-weight: 700; font-size: 1.4rem; color: var(--muted);
    }
    .poll__name { display: block; font-size: .95rem; line-height: 1.3; }
    .poll__card small {
        display: block; color: var(--muted); font-size: .78rem; margin-top: 2px;
    }
    .poll__pct {
        display: block; margin: 8px 0 6px;
        font-size: 1.25rem; font-weight: 700; color: var(--primary-dark);
    }
    .poll__card.is-leader { border-color: var(--primary); background: #fdf8e9; }
    .poll__card.is-mine::after {
        content: "✓ votre choix";
        display: block; margin-top: 8px;
        font-size: .72rem; font-weight: 700; color: var(--primary-dark);
    }
    .poll__card .poll__track { margin-top: 2px; }

    .poll__flash {
        margin: 0 0 16px; padding: 12px 16px; border-radius: 10px;
        background: #eef7ee; border: 1px solid #cfe6cf; color: #245b28;
        font-size: .92rem;
    }
</style>
