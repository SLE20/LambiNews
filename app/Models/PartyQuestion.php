<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Question posée à toutes les structures politiques. */
class PartyQuestion extends Model
{
    use CrudTrait;

    protected $fillable = ['theme', 'question', 'position', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'position' => 'integer'];
    }

    public function answers(): HasMany
    {
        return $this->hasMany(PartyAnswer::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('position');
    }

    public function getLabelAttribute(): string
    {
        return $this->theme.' — '.$this->question;
    }
}
