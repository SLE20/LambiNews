<style>
    /*
        `a` et les conteneurs internes sont des `span` : il faut les
        passer en `block`, sinon `aspect-ratio`, `overflow` et les
        hauteurs en pourcentage ne s'appliquent pas — l'image sort alors
        à sa taille naturelle et démolit la carte.
    */
    .fdr {
        display: block; overflow: hidden;
        border: 1px solid var(--border); border-radius: 12px;
        background: var(--surface); color: inherit;
        transition: border-color .15s, box-shadow .15s, transform .15s;
    }
    .fdr:hover {
        border-color: var(--primary);
        box-shadow: var(--shadow); transform: translateY(-2px);
    }

    .fdr__cover {
        display: block; position: relative;
        aspect-ratio: 16/9; background: var(--border); overflow: hidden;
    }
    .fdr__cover > img {
        display: block; width: 100%; height: 100%; object-fit: cover;
    }

    /* Le portrait chevauche la bannière ; le corps compense d'autant. */
    .fdr__photo {
        display: block; position: absolute; left: 14px; bottom: -24px; z-index: 2;
        width: 56px; height: 56px; border-radius: 50%; overflow: hidden;
        border: 3px solid var(--surface); background: var(--surface);
        box-shadow: 0 2px 10px rgba(0,0,0,.18);
    }
    .fdr__photo img { display: block; width: 100%; height: 100%; object-fit: cover; }

    .fdr__body { display: block; padding: 16px; }
    .fdr__body--offset { padding-top: 32px; }

    .fdr__eyebrow {
        display: block; margin-bottom: 7px;
        font-size: .68rem; font-weight: 700; letter-spacing: .12em;
        text-transform: uppercase; color: var(--primary-dark);
    }
    .fdr__badge {
        display: inline-block; margin-bottom: 9px; padding: 3px 10px;
        border-radius: 999px; background: var(--primary); color: var(--black);
        font-size: .68rem; font-weight: 800;
        letter-spacing: .08em; text-transform: uppercase;
    }
    .fdr__badge--closed { background: var(--border); color: var(--muted); }

    .fdr__title {
        display: block; margin-bottom: 7px;
        font-family: "Playfair Display", Georgia, serif;
        font-size: 1.05rem; line-height: 1.32; font-weight: 700;
    }
    .fdr__summary {
        display: block; margin-bottom: 13px;
        font-size: .85rem; line-height: 1.5; color: var(--muted);
    }

    .fdr__bar {
        display: block; height: 9px; border-radius: 999px;
        background: var(--border); overflow: hidden;
    }
    .fdr__fill {
        display: block; height: 100%; border-radius: 999px;
        background: var(--primary);
    }
    .fdr__amounts {
        display: flex; justify-content: space-between; gap: 10px;
        margin-top: 9px; font-size: .84rem;
    }
    .fdr__amounts b { color: var(--primary-dark); font-weight: 800; }
    .fdr__amounts span { color: var(--muted); }
    .fdr__meta {
        display: block; margin-top: 8px;
        font-size: .76rem; color: var(--muted);
    }
    .fdr__cta {
        display: block; margin-top: 14px; padding: 11px;
        border-radius: 8px; text-align: center;
        background: var(--primary); color: var(--black);
        font-size: .88rem; font-weight: 800;
    }
    .fdr:hover .fdr__cta { background: var(--primary-dark); color: #fff; }
</style>
