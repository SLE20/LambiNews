<a href="{{ $url }}" target="_blank" rel="noopener" class="tgc tgc--{{ $variant }}">
    <span class="tgc__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="26" height="26"><path fill="currentColor" d="M21.9 4.6 18.7 19.7c-.2 1.1-.9 1.3-1.8.8l-4.9-3.6-2.4 2.3c-.3.3-.5.5-1 .5l.4-5 9.1-8.2c.4-.4-.1-.6-.6-.2L6.2 13.4l-4.8-1.5c-1-.3-1.1-1 .2-1.5L20.5 3.3c.9-.3 1.7.2 1.4 1.3Z"/></svg>
    </span>
    <span class="tgc__text">
        <b>Rejoignez Lambi News sur Telegram</b>
        <small>Les informations en direct, gratuitement, sur votre téléphone.</small>
    </span>
    <span class="tgc__btn">Rejoindre</span>
</a>

@once
@push('styles')
<style>
    .tgc {
        display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: 14px;
        background: linear-gradient(135deg, #229ed9, #1b7fc0); color: #fff;
        box-shadow: 0 8px 22px rgba(34, 158, 217, .28);
        transition: transform .15s, box-shadow .15s;
    }
    .tgc:hover { color: #fff; transform: translateY(-2px); box-shadow: 0 12px 28px rgba(34, 158, 217, .38); }
    .tgc__icon {
        flex: none; width: 46px; height: 46px; border-radius: 50%; display: grid; place-items: center;
        background: #fff; color: #229ed9;
    }
    .tgc__text { flex: 1; min-width: 0; }
    .tgc__text b { display: block; font-size: .98rem; line-height: 1.3; }
    .tgc__text small { display: block; margin-top: 2px; font-size: .8rem; opacity: .9; line-height: 1.35; }
    .tgc__btn {
        flex: none; padding: 9px 14px; border-radius: 999px; font-size: .84rem; font-weight: 800;
        background: #fff; color: #1b7fc0;
    }
    .tgc--card { flex-wrap: wrap; margin-bottom: 18px; }
    .tgc--card .tgc__btn { width: 100%; text-align: center; }
    .tgc--inline { margin: 28px 0; }
    @media (max-width: 520px) {
        .tgc--inline { flex-wrap: wrap; }
        .tgc--inline .tgc__btn { width: 100%; text-align: center; }
    }
</style>
@endpush
@endonce
