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
    .poll__flash {
        margin: 0 0 16px; padding: 12px 16px; border-radius: 10px;
        background: #eef7ee; border: 1px solid #cfe6cf; color: #245b28;
        font-size: .92rem;
    }
</style>
