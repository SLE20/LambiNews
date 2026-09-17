<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Fundraiser extends Model
{
    use CrudTrait;

    public const STATUS_DRAFT  = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'title', 'slug', 'summary', 'story', 'cover_image', 'photo', 'beneficiary',
        'goal_amount', 'currency', 'status', 'is_featured', 'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'goal_amount'   => 'decimal:2',
            'raised_amount' => 'decimal:2',
            'is_featured'   => 'boolean',
            'starts_at'     => 'date',
            'ends_at'       => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Fundraiser $fundraiser): void {
            if (blank($fundraiser->slug)) {
                $fundraiser->slug = Str::slug(Str::limit($fundraiser->title, 60, ''))
                    .'-'.Str::lower(Str::random(5));
            }
        });
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(FundraiserContribution::class);
    }

    /** Contributions confirmées, les seules qui comptent. */
    public function completedContributions(): HasMany
    {
        return $this->contributions()->where('status', FundraiserContribution::STATUS_COMPLETED);
    }

    /** Campagnes visibles du public. */
    public function scopeLive(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query
            ->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_CLOSED])
            ->where(fn (Builder $q) => $q
                ->whereNull('starts_at')->orWhereDate('starts_at', '<=', $today));
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && (! $this->ends_at || $this->ends_at->gte(now()->startOfDay()));
    }

    /** Progression, plafonnée à 100 % pour la barre. */
    public function percent(): float
    {
        $goal = (float) $this->goal_amount;

        return $goal <= 0 ? 0.0 : min(100, round(((float) $this->raised_amount / $goal) * 100, 1));
    }

    /** Progression réelle, qui peut dépasser 100 %. */
    public function rawPercent(): float
    {
        $goal = (float) $this->goal_amount;

        return $goal <= 0 ? 0.0 : round(((float) $this->raised_amount / $goal) * 100, 1);
    }

    public function remaining(): float
    {
        return max(0, (float) $this->goal_amount - (float) $this->raised_amount);
    }

    public function daysLeft(): ?int
    {
        return $this->ends_at
            ? max(0, (int) now()->startOfDay()->diffInDays($this->ends_at, false))
            : null;
    }

    public function formatted(float $amount): string
    {
        return number_format($amount, 2, ',', ' ').' '.$this->currency;
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT  => 'Brouillon',
            self::STATUS_ACTIVE => 'En cours',
            self::STATUS_CLOSED => 'Clôturée',
            default             => $this->status,
        };
    }

    public function getProgressLabel(): string
    {
        return $this->formatted((float) $this->raised_amount)
            .' / '.$this->formatted((float) $this->goal_amount)
            .' ('.number_format($this->rawPercent(), 0).' %)';
    }
}
