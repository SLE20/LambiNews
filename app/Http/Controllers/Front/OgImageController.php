<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\OgImageGenerator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sert l’image de partage d’un article, en 1200×630.
 *
 * L’URL porte une empreinte de l’article ({version}) : quand le titre ou
 * la photo changent, l’adresse change et Facebook régénère son aperçu au
 * lieu de resservir l’ancienne image depuis son cache.
 */
class OgImageController extends Controller
{
    public function show(
        OgImageGenerator $generator,
        string $version,
        string $slug
    ): Response {
        $article = Article::query()
            ->published()
            ->with('category')
            ->where('slug', $slug)
            ->firstOrFail();

        $jpeg = $generator->render($article);

        return response($jpeg, 200, [
            'Content-Type'   => 'image/jpeg',
            'Content-Length' => (string) strlen($jpeg),
            // L’empreinte est dans l’URL : le contenu ne changera jamais.
            'Cache-Control'  => 'public, max-age=31536000, immutable',
        ]);
    }
}
