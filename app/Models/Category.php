<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | Champs modifiables
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'position',
        'is_active',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Boot
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::saving(function (Category $category) {

            /*
             * Génération automatique du slug
             * si aucun slug n'a été fourni.
             */
            if (blank($category->slug)) {
                $category->slug = Str::slug(
                    $category->name
                );
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Rubrique parente
    |--------------------------------------------------------------------------
    */

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'parent_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Toutes les sous-rubriques
    |--------------------------------------------------------------------------
    |
    | Utilisé notamment dans Backpack.
    |
    */

    public function children(): HasMany
    {
        return $this->hasMany(
            self::class,
            'parent_id'
        )
        ->orderBy('position')
        ->orderBy('name');
    }

    /*
    |--------------------------------------------------------------------------
    | Sous-rubriques actives
    |--------------------------------------------------------------------------
    |
    | À utiliser sur le site public.
    |
    */

    public function activeChildren(): HasMany
    {
        return $this->hasMany(
            self::class,
            'parent_id'
        )
        ->where('is_active', true)
        ->orderBy('position')
        ->orderBy('name');
    }

    /*
    |--------------------------------------------------------------------------
    | Articles
    |--------------------------------------------------------------------------
    */

    public function articles(): HasMany
    {
        return $this->hasMany(
            Article::class
        );
    }
}