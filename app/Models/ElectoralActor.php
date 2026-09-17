<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Acteur du processus électoral (CEP, électeurs, partis…). */
class ElectoralActor extends Model
{
    use CrudTrait;

    protected $fillable = [
        'name', 'slug', 'icon', 'summary', 'role', 'responsibilities', 'moments',
        'watch_points', 'source_label', 'source_url', 'position', 'is_published',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'position' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (ElectoralActor $actor): void {
            if (blank($actor->slug)) {
                $actor->slug = Str::slug($actor->name);
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)->orderBy('position');
    }

    /** Un élément par ligne dans l'administration, une liste à l'écran. */
    public function lines(string $field): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $this->{$field}))));
    }
}
