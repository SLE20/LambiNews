<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\Thumbnailer;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sert la vignette d'un article.
 *
 * Générée à la première demande puis relue depuis le cache. Le
 * navigateur réclame plusieurs largeurs en parallèle (srcset), ce qui
 * répartit la conversion au lieu de la concentrer sur le rendu de la page.
 */
class ThumbnailController extends Controller
{
    public function __invoke(Thumbnailer $thumbnailer, int $width, string $slug): Response
    {
        abort_unless(in_array($width, Thumbnailer::WIDTHS, true), 404);

        $article = Article::query()
            ->where('slug', $slug)
            ->whereNotNull('featured_image')
            ->firstOrFail();

        $jpeg = $thumbnailer->render($article->featured_image, $width);

        abort_if($jpeg === null, 404);

        return response($jpeg, 200, [
            'Content-Type'   => 'image/jpeg',
            'Content-Length' => (string) strlen($jpeg),
            'Cache-Control'  => 'public, max-age=2592000',
        ]);
    }
}
