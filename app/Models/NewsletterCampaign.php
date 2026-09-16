<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsletterCampaign extends Model
{
    use CrudTrait;

    public const STATUS_DRAFT  = 'draft';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_SENT   = 'sent';

    protected $fillable = [
        'subject',
        'preheader',
        'intro',
        'sponsor_name',
        'sponsor_url',
        'sponsor_image',
        'sponsor_text',
        'article_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'queued_at' => 'datetime',
            'sent_at'   => 'datetime',
        ];
    }

    public function sends(): HasMany
    {
        return $this->hasMany(NewsletterSend::class);
    }

    /** Articles inclus automatiquement : les plus récents, hors sponsorisé. */
    public function articles()
    {
        return Article::query()
            ->published()
            ->editorial()
            ->with(['category', 'author'])
            ->latest('published_at')
            ->limit(max(1, (int) $this->article_count))
            ->get();
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT  => 'Brouillon',
            self::STATUS_QUEUED => 'Envoi en cours',
            self::STATUS_SENT   => 'Envoyée',
            default             => $this->status,
        };
    }

    public function getProgress(): string
    {
        if ($this->status === self::STATUS_DRAFT) {
            return '—';
        }

        return $this->sent_count.' / '.$this->recipients_count.' envoyés'
            .($this->failed_count > 0 ? ' — '.$this->failed_count.' en échec' : '');
    }
}
