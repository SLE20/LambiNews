{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<rss version="2.0">
    <channel>
        <title>Lambi News</title>

        <link>{{ route('home') }}</link>

        <description>
            Le citoyen au cœur de l’information.
        </description>

        <language>fr</language>

        <lastBuildDate>
            {{ now()->toRssString() }}
        </lastBuildDate>

        <image>
            <url>{{ asset('images/lambinews-embleme.jpg') }}</url>
            <title>Lambi News</title>
            <link>{{ route('home') }}</link>
        </image>

        @foreach($articles as $article)
            <item>
                <title>
                    {{ $article->title }}
                </title>

                <link>
                    {{ route('articles.show', $article->slug) }}
                </link>

                <guid isPermaLink="true">
                    {{ route('articles.show', $article->slug) }}
                </guid>

                <description>
                    {{ $article->excerpt ?: Str::limit(
                        strip_tags($article->content),
                        250
                    ) }}
                </description>

                <category>
                    {{ $article->category->name }}
                </category>

                <author>
                    {{ $article->author->email }}
                    ({{ $article->author->name }})
                </author>

                <pubDate>
                    {{ $article->published_at?->toRssString() }}
                </pubDate>
            </item>
        @endforeach
    </channel>
</rss>