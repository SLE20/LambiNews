<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Services\OgImageGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Article extends Model
{
    use CrudTrait;
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'author_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'image_caption',
        'status',
        'is_featured',
        'is_breaking',
        'allow_comments',
        'published_at',
        'seo_title',
        'seo_description',
        'is_sponsored',
        'sponsor_name',
        'sponsor_url',
        'sponsor_logo',
    ];

    public function comments(): HasMany
{
    return $this->hasMany(Comment::class);
}

public function articleViews(): HasMany
{
    return $this->hasMany(ArticleView::class);
}

public function approvedComments(): HasMany
{
    return $this->hasMany(Comment::class)
        ->where('status', 'approved')
        ->whereNull('parent_id')
        ->oldest();
}

    public function tags(): BelongsToMany
{
    return $this->belongsToMany(Tag::class);
}

    protected function casts(): array
    {
        return [
            'is_sponsored' => 'boolean',
            'is_featured' => 'boolean',
            'allow_comments' => 'boolean',
            'views_count' => 'integer',
            'published_at' => 'datetime',
            'is_breaking' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Article $article) {
            if (blank($article->slug)) {
                $article->slug = Str::slug($article->title);
            }

            if (
                $article->status === 'published'
                && blank($article->published_at)
            ) {
                $article->published_at = now();
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Articles éditoriaux uniquement.
     *
     * Le contenu sponsorisé reste consultable et indexable, mais il ne
     * doit pas se mêler aux fils d’actualité : ni RSS, ni sitemap Google
     * Actualités, où il serait présenté comme du journalisme.
     */
    public function scopeEditorial(Builder $query): Builder
    {
        return $query->where('is_sponsored', false);
    }

    /**
     * URL de l’image de partage 1200×630 servie aux réseaux sociaux.
     *
     * L’empreinte contenue dans l’URL change avec le titre ou la photo,
     * ce qui force Facebook et consorts à régénérer leur aperçu.
     */
    public function getOgImageUrlAttribute(): string
    {
        return route('og.image', [
            'version' => OgImageGenerator::version($this),
            'slug'    => $this->slug,
        ]);
    }
}