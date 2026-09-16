{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
{{--
    Plan du site Google Actualités.
    Ne doit contenir que des articles publiés depuis moins de deux jours.
--}}
<urlset
    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"
>
    @foreach($articles as $article)
        <url>
            <loc>{{ route('articles.show', $article->slug) }}</loc>

            <news:news>
                <news:publication>
                    <news:name>Lambi News</news:name>
                    <news:language>fr</news:language>
                </news:publication>

                <news:publication_date>{{ $article->published_at->toAtomString() }}</news:publication_date>
                <news:title>{{ $article->title }}</news:title>
            </news:news>
        </url>
    @endforeach
</urlset>
