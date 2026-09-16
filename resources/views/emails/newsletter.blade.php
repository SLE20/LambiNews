<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $campaign->subject }}</title>
</head>
{{-- Styles en ligne : les clients de messagerie ignorent les feuilles externes. --}}
<body style="margin:0;padding:0;background:#f7f5ef;font-family:Arial,Helvetica,sans-serif;color:#1c1a17;">

    @if($campaign->preheader)
        {{-- Texte d'aperçu, masqué dans le corps mais lu par la boîte de réception. --}}
        <div style="display:none;max-height:0;overflow:hidden;opacity:0;">
            {{ $campaign->preheader }}
        </div>
    @endif

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f7f5ef;padding:24px 12px;">
        <tr><td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden;">

                <tr>
                    <td style="background:#080808;padding:22px 26px;">
                        <span style="color:#d8a922;font-size:20px;font-weight:bold;letter-spacing:2px;">LAMBI NEWS</span>
                    </td>
                </tr>

                @if($campaign->intro)
                    <tr><td style="padding:26px 26px 0;font-size:15px;line-height:1.7;">
                        {!! nl2br(e($campaign->intro)) !!}
                    </td></tr>
                @endif

                @foreach($articles as $article)
                    <tr><td style="padding:22px 26px 0;">
                        <a href="{{ route('articles.show', $article->slug) }}"
                           style="color:#1c1a17;text-decoration:none;">
                            <span style="display:inline-block;background:#2f2a1c;color:#d8a922;font-size:10px;font-weight:bold;letter-spacing:1.5px;padding:4px 9px;border-radius:3px;text-transform:uppercase;">
                                {{ $article->category?->name ?? 'Actualité' }}
                            </span>
                            <h2 style="margin:10px 0 6px;font-size:19px;line-height:1.35;">{{ $article->title }}</h2>
                            <p style="margin:0;color:#706b61;font-size:14px;line-height:1.6;">
                                {{ \Illuminate\Support\Str::limit(strip_tags($article->excerpt ?: $article->content), 150) }}
                            </p>
                        </a>
                    </td></tr>

                    @if(! $loop->last)
                        <tr><td style="padding:18px 26px 0;">
                            <hr style="border:0;border-top:1px solid #e9e3d8;margin:0;">
                        </td></tr>
                    @endif
                @endforeach

                @if($campaign->sponsor_name)
                    <tr><td style="padding:26px 26px 0;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                               style="background:#f7f5ef;border:1px solid #e9e3d8;border-radius:10px;">
                            <tr><td style="padding:18px;">
                                <p style="margin:0 0 8px;font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:#706b61;">
                                    Publicité
                                </p>

                                @if($campaign->sponsor_image)
                                    <a href="{{ $campaign->sponsor_url ?: '#' }}">
                                        <img src="{{ asset('storage/'.$campaign->sponsor_image) }}"
                                             alt="{{ $campaign->sponsor_name }}"
                                             style="max-width:100%;border-radius:6px;display:block;margin-bottom:10px;">
                                    </a>
                                @endif

                                <strong style="font-size:15px;">{{ $campaign->sponsor_name }}</strong>

                                @if($campaign->sponsor_text)
                                    <p style="margin:6px 0 0;font-size:14px;color:#706b61;line-height:1.6;">
                                        {{ $campaign->sponsor_text }}
                                    </p>
                                @endif

                                @if($campaign->sponsor_url)
                                    <p style="margin:10px 0 0;">
                                        <a href="{{ $campaign->sponsor_url }}"
                                           style="color:#a87b12;font-size:14px;font-weight:bold;">En savoir plus →</a>
                                    </p>
                                @endif
                            </td></tr>
                        </table>
                    </td></tr>
                @endif

                <tr><td style="padding:26px;">
                    <a href="{{ route('home') }}"
                       style="display:inline-block;background:#d8a922;color:#080808;padding:12px 26px;border-radius:999px;font-weight:bold;text-decoration:none;font-size:14px;">
                        Lire tout sur Lambi News
                    </a>
                </td></tr>

                <tr><td style="background:#f7f5ef;padding:20px 26px;font-size:12px;color:#706b61;line-height:1.7;">
                    Vous recevez ce courriel parce que vous vous êtes inscrit à
                    l’infolettre de Lambi News.<br>
                    <a href="{{ $unsubscribeUrl }}" style="color:#706b61;text-decoration:underline;">
                        Se désabonner en un clic
                    </a>
                </td></tr>

            </table>
        </td></tr>
    </table>
</body>
</html>
