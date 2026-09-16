<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Poll extends Model
{
    use CrudTrait;

    protected $fillable = [
        'question',
        'slug',
        'layout',
        'headline',
        'eyebrow',
        'subtitle',
        'hero_image',
        'description',
        'is_active',
        'starts_at',
        'ends_at',
        'hide_results_before_vote',
        'max_votes_per_ip',
        'is_paid',
        'vote_price',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'is_active'                => 'boolean',
            'is_paid'                  => 'boolean',
            'vote_price'               => 'decimal:2',
            'max_votes_per_ip'         => 'integer',
            'hide_results_before_vote' => 'boolean',
            'starts_at'                => 'date',
            'ends_at'                  => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Poll $poll): void {
            if (blank($poll->slug)) {
                $poll->slug = Str::slug(Str::limit($poll->question, 60, ''))
                    .'-'.Str::lower(Str::random(5));
            }
        });
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class)->orderBy('position')->orderBy('id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    /**
     * Sondage ouvert au vote aujourd’hui.
     *
     * Un sondage à moins de deux choix n’a rien à comparer : il est
     * écarté plutôt que d’être affiché à moitié construit.
     */
    public function scopeOpen(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query
            ->has('options', '>=', 2)
            ->where('is_active', true)
            ->where(fn (Builder $q) => $q
                ->whereNull('starts_at')->orWhereDate('starts_at', '<=', $today))
            ->where(fn (Builder $q) => $q
                ->whereNull('ends_at')->orWhereDate('ends_at', '>=', $today));
    }

    public function isClosed(): bool
    {
        return ! $this->is_active
            || ($this->ends_at && $this->ends_at->lt(now()->startOfDay()));
    }

    /** Total des voix valides. */
    public function validVotes(): int
    {
        return (int) $this->options->sum('votes_count');
    }

    public function percentFor(PollOption $option): float
    {
        $total = $this->validVotes();

        return $total < 1 ? 0.0 : round(($option->votes_count / $total) * 100, 1);
    }

    /** Jours restants avant la clôture, ou null si le sondage n'a pas de fin. */
    public function daysLeft(): ?int
    {
        if (! $this->ends_at) {
            return null;
        }

        return max(0, (int) now()->startOfDay()->diffInDays($this->ends_at, false));
    }

    /**
     * Choix classés du plus voté au moins voté, avec pourcentage et
     * couleur résolue — la même série alimente la liste et le camembert.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    public function ranked(): \Illuminate\Support\Collection
    {
        $total = max(1, $this->validVotes());

        return $this->options
            ->sortByDesc('votes_count')
            ->values()
            ->map(fn (PollOption $option, int $i) => [
                'option'  => $option,
                'percent' => round(($option->votes_count / $total) * 100, 1),
                'color'   => $option->displayColor($i),
            ]);
    }

    /**
     * Titre du bandeau, dont le dernier mot est mis en couleur.
     *
     * Presque toujours un millésime (« SONDAJ PRÉZIDANSYÈL 2026 ») : le
     * détacher visuellement est ce qui donne son allure à l'affiche.
     */
    public function headlineHtml(): string
    {
        $headline = trim((string) ($this->headline ?: $this->question));
        $words    = preg_split('/\s+/u', $headline) ?: [];

        if (count($words) < 2) {
            return e($headline);
        }

        $last = array_pop($words);

        return e(implode(' ', $words)).' <em>'.e($last).'</em>';
    }

    /** Nombre de voix autorisées depuis une même connexion. */
    public function voteQuota(): int
    {
        return max(1, (int) ($this->max_votes_per_ip ?: 1));
    }

    public function allowsMultipleVotes(): bool
    {
        return $this->voteQuota() > 1;
    }

    public function isPaid(): bool
    {
        return $this->is_paid && (float) $this->vote_price > 0;
    }

    public function formattedPrice(): string
    {
        return number_format((float) $this->vote_price, 2).' '.($this->currency ?: 'USD');
    }

    /** Seules les voix réellement acquises comptent dans les résultats. */
    public function countedVotes()
    {
        return $this->votes()
            ->where('is_void', false)
            ->whereIn('payment_status', ['free', 'paid']);
    }

    public function isShowcase(): bool
    {
        return $this->layout === 'showcase';
    }

    public function getStatusLabel(): string
    {
        if (! $this->is_active) {
            return 'Désactivé';
        }

        if ($this->ends_at && $this->ends_at->lt(now()->startOfDay())) {
            return 'Clos le '.$this->ends_at->format('d/m/Y');
        }

        return $this->ends_at
            ? 'Ouvert jusqu’au '.$this->ends_at->format('d/m/Y')
            : 'Ouvert';
    }

    /** Résumé des règles, pour la liste de l'administration. */
    public function getRulesLabel(): string
    {
        $parts = [$this->voteQuota() === 1
            ? '1 voix / IP'
            : $this->voteQuota().' voix / IP'];

        if ($this->isPaid()) {
            $parts[] = 'payant — '.$this->formattedPrice();
        }

        return implode(' · ', $parts);
    }

    public function getVotesSummary(): string
    {
        return number_format($this->validVotes(), 0, ',', ' ').' voix';
    }
}
