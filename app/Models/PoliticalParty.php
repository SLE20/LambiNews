<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/** Structure politique agréée par le CEP. */
class PoliticalParty extends Model
{
    use CrudTrait;

    protected $fillable = [
        'name', 'slug', 'acronym', 'campaign_number', 'kind', 'logo',
        'leader', 'website', 'description', 'is_published',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'campaign_number' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (PoliticalParty $party): void {
            if (blank($party->slug)) {
                $party->slug = Str::slug($party->acronym ?: $party->name).'-'.Str::lower(Str::random(4));
            }
        });
    }

    public function answers(): HasMany
    {
        return $this->hasMany(PartyAnswer::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function displayName(): string
    {
        return $this->acronym && $this->acronym !== $this->name
            ? $this->name.' ('.$this->acronym.')'
            : $this->name;
    }

    /** Couleur stable dérivée du sigle, pour la pastille sans logo. */
    public function badgeColor(): string
    {
        return 'hsl('.(crc32((string) ($this->acronym ?: $this->name)) % 360).' 55% 38%)';
    }
}
