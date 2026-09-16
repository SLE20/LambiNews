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
        'description',
        'is_active',
        'starts_at',
        'ends_at',
        'hide_results_before_vote',
    ];

    protected function casts(): array
    {
        return [
            'is_active'                => 'boolean',
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

    public function getVotesSummary(): string
    {
        return number_format($this->validVotes(), 0, ',', ' ').' voix';
    }
}
