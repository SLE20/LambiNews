<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Vidéo de la rédaction, hébergée sur YouTube ou servie par le site.
 */
class Video extends Model
{
    use CrudTrait;

    public const SOURCE_YOUTUBE = 'youtube';

    public const SOURCE_FILE = 'file';

    protected $fillable = [
        'title', 'slug', 'description', 'source', 'youtube_id', 'video_url',
        'thumbnail', 'duration_seconds', 'category_id', 'author_id',
        'is_published', 'is_featured', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published'     => 'boolean',
            'is_featured'      => 'boolean',
            'published_at'     => 'datetime',
            'duration_seconds' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Video $video): void {
            if (blank($video->slug)) {
                $video->slug = Str::slug(Str::limit($video->title, 70, ''))
                    .'-'.Str::lower(Str::random(5));
            }

            // Une adresse YouTube complète est plus simple à coller qu'un
            // identifiant : on extrait l'identifiant nous-mêmes.
            if (filled($video->youtube_id)) {
                $video->youtube_id = self::extractYoutubeId($video->youtube_id);
            }

            if ($video->is_published && blank($video->published_at)) {
                $video->published_at = now();
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

    public function scopeLive(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    /** Accepte une URL YouTube sous toutes ses formes, ou l'identifiant seul. */
    public static function extractYoutubeId(string $value): string
    {
        $value = trim($value);

        if (preg_match('~(?:youtu\.be/|v=|/embed/|/shorts/|/live/)([A-Za-z0-9_-]{11})~', $value, $m)) {
            return $m[1];
        }

        return preg_match('~^[A-Za-z0-9_-]{11}$~', $value) ? $value : '';
    }

    public function isYoutube(): bool
    {
        return $this->source === self::SOURCE_YOUTUBE && filled($this->youtube_id);
    }

    /** Adresse du fichier à lire, pour une vidéo servie par le site. */
    public function fileUrl(): ?string
    {
        if (blank($this->video_url)) {
            return null;
        }

        return Str::startsWith($this->video_url, ['http://', 'https://'])
            ? $this->video_url
            : asset('storage/'.ltrim($this->video_url, '/'));
    }

    /** Vignette : celle envoyée par la rédaction, sinon celle de YouTube. */
    public function thumbUrl(): ?string
    {
        if (filled($this->thumbnail)) {
            return asset('storage/'.$this->thumbnail);
        }

        return $this->isYoutube()
            ? 'https://i.ytimg.com/vi/'.$this->youtube_id.'/hqdefault.jpg'
            : null;
    }

    /** Durée en 12:34, ou 1:02:03 au-delà d'une heure. */
    public function durationLabel(): ?string
    {
        $seconds = (int) $this->duration_seconds;

        if ($seconds <= 0) {
            return null;
        }

        return $seconds >= 3600
            ? sprintf('%d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60)
            : sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
    }

    /** « 12 k vues » plutôt que « 12 345 vues » : c'est ce qu'on lit. */
    public function viewsLabel(): string
    {
        $n = (int) $this->views_count;

        if ($n >= 1_000_000) {
            return rtrim(rtrim(number_format($n / 1_000_000, 1, ',', ' '), '0'), ',').' M vues';
        }

        if ($n >= 1_000) {
            return rtrim(rtrim(number_format($n / 1_000, 1, ',', ' '), '0'), ',').' k vues';
        }

        return $n.' vue'.($n > 1 ? 's' : '');
    }

    /** « il y a 3 jours », comme sur les plateformes vidéo. */
    public function agoLabel(): string
    {
        return ($this->published_at ?: $this->created_at)?->diffForHumans() ?? '';
    }

    public function getSourceLabel(): string
    {
        return $this->isYoutube() ? 'YouTube' : 'Fichier';
    }

    public function getPublicationLabel(): string
    {
        return $this->is_published ? 'Publiée' : 'Brouillon';
    }
}
