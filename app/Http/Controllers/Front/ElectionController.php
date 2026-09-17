<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ElectionEvent;
use App\Models\ElectoralActor;
use App\Models\PartyQuestion;
use App\Models\PoliticalParty;
use App\Models\SiteSetting;
use App\Models\VotingCenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Espace « Élections 2026 » : un guide factuel du processus.
 *
 * Lambi News n'organise aucune élection et ne recommande aucun
 * candidat. Chaque date et chaque chiffre affichés portent leur source
 * et leur date de vérification.
 */
class ElectionController extends Controller
{
    public function index(): View
    {
        $women = (int) preg_replace('/\D/', '', (string) SiteSetting::get('oni_women', ''));
        $men   = (int) preg_replace('/\D/', '', (string) SiteSetting::get('oni_men', ''));

        return view('front.elections.index', [
            'next'     => ElectionEvent::next(),
            'upcoming' => ElectionEvent::published()->orderBy('starts_on')->get(),
            'actors'   => ElectoralActor::published()->get(),
            'oni'      => ($women + $men) > 0 ? [
                'women'   => $women,
                'men'     => $men,
                'total'   => $women + $men,
                'checked' => SiteSetting::get('oni_checked_on', ''),
                'url'     => SiteSetting::get('oni_source_url', ''),
            ] : null,
            'cepStats'  => SiteSetting::get('cep_stats_url', ''),
            'centers'   => $this->departmentCounts(),
            'parties'   => PoliticalParty::published()->count(),
            'questionCount' => PartyQuestion::active()->count(),
            'articles'  => $this->electionArticles(),
        ]);
    }

    public function calendar(): View
    {
        $events = ElectionEvent::published()
            ->with('revisions')
            ->orderBy('starts_on')
            ->get();

        return view('front.elections.calendar', [
            'events'    => $events,
            'next'      => ElectionEvent::next(),
            'postponed' => $events->sum(fn ($e) => $e->revisions->count()),
        ]);
    }

    public function cycle(): View
    {
        return view('front.elections.cycle', [
            'actors' => ElectoralActor::published()->get(),
        ]);
    }

    public function actor(string $slug): View
    {
        $actor = ElectoralActor::published()->where('slug', $slug)->firstOrFail();

        return view('front.elections.actor', [
            'actor'  => $actor,
            'others' => ElectoralActor::published()->where('id', '!=', $actor->id)->get(),
        ]);
    }

    public function whereToVote(): View
    {
        return view('front.elections.where-to-vote', [
            'departments' => $this->departmentCounts(),
            'total'       => VotingCenter::count(),
        ]);
    }

    /**
     * Recherche dans le répertoire des centres, côté serveur : la liste
     * complète dépasse le millier de lignes, inutile de l'envoyer à un
     * téléphone qui n'en affichera que quelques-unes.
     */
    public function centers(Request $request): JsonResponse
    {
        $v = $request->validate([
            'departement' => ['nullable', 'string', 'max:40'],
            'commune'     => ['nullable', 'string', 'max:80'],
            'section'     => ['nullable', 'string', 'max:120'],
            'q'           => ['nullable', 'string', 'max:80'],
            'page'        => ['nullable', 'integer', 'min:1'],
        ]);

        $query = VotingCenter::query()
            ->when($v['departement'] ?? null, fn ($q, $d) => $q->where('department', $d))
            ->when($v['commune'] ?? null, fn ($q, $c) => $q->where('commune', $c))
            ->when($v['section'] ?? null, fn ($q, $s) => $q->where('section', $s))
            ->when($v['q'] ?? null, function ($q, $term) {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)
                    ->orWhere('address', 'like', $like)
                    ->orWhere('commune', 'like', $like)
                    ->orWhere('section', 'like', $like));
            });

        $communes = ($v['departement'] ?? null)
            ? VotingCenter::where('department', $v['departement'])->distinct()->orderBy('commune')->pluck('commune')
            : [];

        $sections = ($v['departement'] ?? null) && ($v['commune'] ?? null)
            ? VotingCenter::where('department', $v['departement'])->where('commune', $v['commune'])
                ->whereNotNull('section')->distinct()->orderBy('section')->pluck('section')
            : [];

        $page = $query->orderBy('department')->orderBy('commune')->orderBy('name')
            ->paginate(12, ['id', 'department', 'commune', 'section', 'name', 'address']);

        return response()->json([
            'total'    => $page->total(),
            'page'     => $page->currentPage(),
            'pages'    => $page->lastPage(),
            'items'    => $page->items(),
            'communes' => $communes,
            'sections' => $sections,
        ])->header('Cache-Control', 'public, max-age=300');
    }

    public function parties(Request $request): View
    {
        $term = trim((string) $request->query('q', ''));

        $parties = PoliticalParty::published()
            ->withCount('answers')
            ->when($term !== '', function ($q) use ($term) {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)
                    ->orWhere('acronym', 'like', $like)
                    ->orWhere('campaign_number', ctype_digit($term) ? (int) $term : -1));
            })
            ->orderBy('campaign_number')
            ->get();

        return view('front.elections.parties', [
            'parties'   => $parties,
            'term'      => $term,
            'questions' => PartyQuestion::active()->get(),
            'answered'  => PoliticalParty::published()->has('answers')->count(),
            'total'     => PoliticalParty::published()->count(),
        ]);
    }

    public function party(string $slug): View
    {
        $party = PoliticalParty::published()->where('slug', $slug)->firstOrFail();
        $answers = $party->answers()->get()->keyBy('party_question_id');

        return view('front.elections.party', [
            'party'     => $party,
            'questions' => PartyQuestion::active()->get(),
            'answers'   => $answers,
        ]);
    }

    /** @return array<string, array{label: string, city: string, lat: float, lng: float, count: int}> */
    private function departmentCounts(): array
    {
        $counts = Cache::remember('elections.center_counts', 600, fn () => VotingCenter::query()
            ->selectRaw('department, COUNT(*) AS n')->groupBy('department')->pluck('n', 'department')->all());

        $out = [];
        foreach (VotingCenter::DEPARTMENTS as $key => [$label, $city, $lat, $lng]) {
            $out[$key] = ['label' => $label, 'city' => $city, 'lat' => $lat, 'lng' => $lng, 'count' => (int) ($counts[$key] ?? 0)];
        }

        return $out;
    }

    /** Derniers articles qui parlent des élections. */
    private function electionArticles()
    {
        return Article::query()
            ->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            // « CEP » seul attraperait « exception » ou « accepter ».
            ->where(fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', 'like', '%election%'))
                ->orWhere('title', 'like', '%élection%')
                ->orWhere('title', 'like', '%électora%')
                ->orWhere('title', 'like', '% CEP %')
                ->orWhere('title', 'like', 'CEP %'))
            ->latest('published_at')
            ->limit(6)
            ->get();
    }
}
