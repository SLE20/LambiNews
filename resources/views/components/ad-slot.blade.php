<div class="ad-slot ad-slot--{{ $position }}{{ $class ? ' '.$class : '' }}">
    @if($ad->type === \App\Models\Ad::TYPE_IMAGE && $ad->image)
        <a
            href="{{ route('ads.click', $ad) }}"
            rel="nofollow sponsored noopener"
            target="_blank"
        >
            <img
                src="{{ asset('storage/'.$ad->image) }}"
                alt="{{ $ad->alt_text ?: $ad->name }}"
                loading="lazy"
                decoding="async"
            >
        </a>
    @else
        {{-- Code fourni par l’annonceur ou la régie. --}}
        {!! $ad->html_code !!}
    @endif

    <span class="ad-slot__label">Publicité</span>
</div>
