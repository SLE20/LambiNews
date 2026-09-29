<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Espace « Médias » : une grille de vidéos et une page de lecture.
 */
class VideoController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'rubrique' => ['nullable', 'string', 'max:80'],
            'q'        => ['nullable', 'string', 'max:80'],
        ]);

        $term = trim($validated['q'] ?? '');

        $videos = Video::query()
            ->live()
            ->with(['category', 'author'])
            ->when($validated['rubrique'] ?? null, fn ($q, $slug) => $q
                ->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($term !== '', function ($q) use ($term) {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $q->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('description', 'like', $like));
            })
            ->orderByDesc('published_at')
            ->paginate(24)
            ->withQueryString();

        // Une puce par rubrique qui a au moins une vidéo en ligne.
        $chips = Category::query()
            ->whereHas('videos', fn ($q) => $q->live())
            ->withCount(['videos' => fn ($q) => $q->live()])
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $featured = ($term === '' && empty($validated['rubrique']))
            ? Video::live()->with('category')->where('is_featured', true)->orderByDesc('published_at')->first()
            : null;

        return view('front.videos.index', [
            'videos'   => $videos,
            'chips'    => $chips,
            'current'  => $validated['rubrique'] ?? '',
            'term'     => $term,
            'featured' => $featured,
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $video = Video::query()->live()->with(['category', 'author'])->where('slug', $slug)->firstOrFail();

        $this->countView($request, $video);

        $next = Video::query()
            ->live()
            ->with('category')
            ->where('id', '!=', $video->id)
            // D'abord la même rubrique : c'est ce que le spectateur regardera.
            ->orderByRaw('CASE WHEN category_id <=> ? THEN 0 ELSE 1 END', [$video->category_id])
            ->orderByDesc('published_at')
            ->limit(12)
            ->get();

        return view('front.videos.show', ['video' => $video, 'next' => $next]);
    }

    /**
     * Une vue par visiteur et par vidéo, sur trois heures.
     *
     * Query builder brut : incrémenter par Eloquent toucherait
     * updated_at et ferait remonter la vidéo dans les tris.
     */
    private function countView(Request $request, Video $video): void
    {
        $key = 'video_seen_'.$video->id;

        if ($request->session()->get($key) > now()->subHours(3)->timestamp) {
            return;
        }

        $request->session()->put($key, now()->timestamp);
        DB::table('videos')->where('id', $video->id)->increment('views_count');
    }
}
