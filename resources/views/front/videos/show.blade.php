@extends('front.layouts.app')

@section('title', $video->title.' — Médias — Lambi News')
@section('meta_description', \Illuminate\Support\Str::limit($video->description ?: $video->title, 160))

@push('styles')
    @include('front.videos._styles')
    @include('front.videos._watch_styles')
@endpush

@section('content')
<section class="vid vid--watch">
    <div class="vid__wrap">
        <div class="wat">
            <div class="wat__main">
                @include('front.videos._player')

                <h1 class="wat__title">{{ $video->title }}</h1>

                <div class="wat__row">
                    <div class="wat__channel">
                        <span class="vid__avatar" aria-hidden="true">LN</span>
                        <span>
                            <b>Lambi News</b>
                            <small>{{ $video->author?->name ?: 'Rédaction' }}</small>
                        </span>
                        @if(str_starts_with(\App\Models\SiteSetting::get('telegram_url', ''), 'https://'))
                            <a class="wat__sub" href="{{ \App\Models\SiteSetting::get('telegram_url') }}"
                               target="_blank" rel="noopener">S’abonner</a>
                        @endif
                    </div>

                    <div class="wat__actions">
                        @php($share = route('videos.show', $video->slug))
                        <a class="wat__act" target="_blank" rel="noopener"
                           href="https://api.whatsapp.com/send?text={{ urlencode($video->title.' '.$share) }}">WhatsApp</a>
                        <a class="wat__act" target="_blank" rel="noopener"
                           href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($share) }}">Facebook</a>
                        <button type="button" class="wat__act" id="wat-copy" data-url="{{ $share }}">Copier le lien</button>
                        @if($video->isYoutube())
                            <a class="wat__act" target="_blank" rel="noopener"
                               href="https://www.youtube.com/watch?v={{ $video->youtube_id }}">Voir sur YouTube</a>
                        @endif
                    </div>
                </div>

                <div class="wat__desc" id="wat-desc">
                    <p class="wat__stats">
                        {{ $video->viewsLabel() }} · {{ $video->agoLabel() }}
                        @if($video->category)
                            · <a href="{{ route('videos.index', ['rubrique' => $video->category->slug]) }}">{{ $video->category->name }}</a>
                        @endif
                    </p>

                    @if($video->description)
                        <div class="wat__text">{{ $video->description }}</div>
                        <button type="button" class="wat__more" id="wat-more">Plus</button>
                    @endif
                </div>

                <x-telegram-cta variant="inline" />
            </div>

            <aside class="wat__side">
                <h2 class="wat__sidetitle">À suivre</h2>

                @forelse($next as $other)
                    <a class="wat__next" href="{{ route('videos.show', $other->slug) }}">
                        <span class="wat__nextthumb">
                            @if($other->thumbUrl())
                                <img src="{{ $other->thumbUrl() }}" alt="" loading="lazy">
                            @endif
                            @if($other->durationLabel())
                                <span class="vid__time">{{ $other->durationLabel() }}</span>
                            @endif
                        </span>
                        <span class="wat__nextbody">
                            <strong>{{ $other->title }}</strong>
                            <small>Lambi News</small>
                            <small>{{ $other->viewsLabel() }} · {{ $other->agoLabel() }}</small>
                        </span>
                    </a>
                @empty
                    <p class="wat__none">Pas d’autre vidéo pour le moment.</p>
                @endforelse

                <a class="wat__all" href="{{ route('videos.index') }}">Toutes les vidéos →</a>
            </aside>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    var copy = document.getElementById('wat-copy');
    if (copy) {
        copy.addEventListener('click', function () {
            var done = function () {
                var old = copy.textContent;
                copy.textContent = '✓ Lien copié';
                setTimeout(function () { copy.textContent = old; }, 2000);
            };
            if (navigator.clipboard) {
                navigator.clipboard.writeText(copy.dataset.url).then(done);
            } else {
                var tmp = document.createElement('input');
                tmp.value = copy.dataset.url;
                document.body.appendChild(tmp);
                tmp.select();
                document.execCommand('copy');
                tmp.remove();
                done();
            }
        });
    }

    // La description reste repliée tant qu'elle dépasse trois lignes.
    var desc = document.getElementById('wat-desc');
    var more = document.getElementById('wat-more');
    var text = desc && desc.querySelector('.wat__text');

    if (more && text) {
        if (text.scrollHeight <= text.clientHeight + 4) {
            more.hidden = true;
        } else {
            more.addEventListener('click', function () {
                var open = desc.classList.toggle('is-open');
                more.textContent = open ? 'Moins' : 'Plus';
            });
        }
    }
})();
</script>
@endpush
