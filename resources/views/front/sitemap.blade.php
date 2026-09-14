{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('home') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>hourly</changefreq>
        <priority>1.0</priority>
    </url>

    <url>
        <loc>{{ route('search') }}</loc>
        <lastmod>{{ now()->toAtomString() }}</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.4</priority>
    </url>

    @foreach($categories as $category)
        <url>
            <loc>
                {{ route('categories.show', $category->slug) }}
            </loc>

            <lastmod>
                {{ $category->updated_at->toAtomString() }}
            </lastmod>

            <changefreq>daily</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach

    @foreach($articles as $article)
        <url>
            <loc>
                {{ route('articles.show', $article->slug) }}
            </loc>

            <lastmod>
                {{ $article->updated_at->toAtomString() }}
            </lastmod>

            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
    @endforeach
</urlset>