{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset
    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
>
    <url>
        <loc>{{ route('home') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>hourly</changefreq>
        <priority>1.0</priority>
    </url>

    <url>
        <loc>{{ route('contact.create') }}</loc>
        <changefreq>yearly</changefreq>
        <priority>0.3</priority>
    </url>

    {{-- Espace Élections --}}
    @foreach(['elections.index' => 'daily', 'elections.calendar' => 'daily', 'elections.where' => 'weekly', 'elections.parties' => 'weekly', 'elections.cycle' => 'monthly'] as $name => $freq)
        <url>
            <loc>{{ route($name) }}</loc>
            <changefreq>{{ $freq }}</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach
    @foreach($electionActors as $actor)
        <url><loc>{{ route('elections.actor', $actor) }}</loc><changefreq>monthly</changefreq><priority>0.5</priority></url>
    @endforeach
    @foreach($electionParties as $party)
        <url><loc>{{ route('elections.party', $party->slug) }}</loc><lastmod>{{ $party->updated_at->toAtomString() }}</lastmod><changefreq>weekly</changefreq><priority>0.5</priority></url>
    @endforeach

    {{-- Rubriques --}}
    @foreach($categories as $category)
        <url>
            <loc>{{ route('categories.show', $category->slug) }}</loc>
            <lastmod>{{ $category->updated_at->toAtomString() }}</lastmod>
            <changefreq>daily</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach

    {{-- Articles, avec leur photo : Google Images indexe aussi par ce biais. --}}
    @foreach($articles as $article)
        <url>
            <loc>{{ route('articles.show', $article->slug) }}</loc>
            <lastmod>{{ $article->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>

            @if($article->featured_image)
                <image:image>
                    <image:loc>{{ asset('storage/'.$article->featured_image) }}</image:loc>
                    <image:title>{{ $article->title }}</image:title>
                </image:image>
            @endif
        </url>
    @endforeach

    {{-- Auteurs --}}
    @foreach($authors as $author)
        <url>
            <loc>{{ route('authors.show', $author->slug) }}</loc>
            <lastmod>{{ $author->updated_at->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.5</priority>
        </url>
    @endforeach

    {{-- Pages institutionnelles --}}
    @foreach($pages as $page)
        <url>
            <loc>{{ route('pages.show', $page->slug) }}</loc>
            <lastmod>{{ $page->updated_at->toAtomString() }}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.4</priority>
        </url>
    @endforeach
</urlset>
