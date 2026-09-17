<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Échéance du calendrier électoral.
 *
 * Toute modification de date ou d'état est archivée : le calendrier a
 * été reporté à plusieurs reprises et le lecteur doit pouvoir le
 * constater sans croire la rédaction sur parole.
 */
class ElectionEvent extends Model
{
    use CrudTrait;

    public const STATUSES = [
        'done'      => 'Fait',
        'ongoing'   => 'En cours',
        'upcoming'  => 'À venir',
        'postponed' => 'Reporté',
        'cancelled' => 'Annulé',
    ];

    protected $fillable = [
        'title', 'description', 'starts_on', 'ends_on', 'status',
        'source_label', 'source_url', 'verified_on', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'starts_on'    => 'date',
            'ends_on'      => 'date',
            'verified_on'  => 'date',
            'is_published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (ElectionEvent $event): void {
            // Corriger un brouillon n'est pas un report : seul un
            // changement sur une échéance déjà publiée est archivé.
            if (! $event->getOriginal('is_published')
                || ! $event->isDirty(['starts_on', 'ends_on', 'status'])) {
                return;
            }

            $event->revisions()->create([
                'old_starts_on' => $event->getOriginal('starts_on'),
                'old_ends_on'   => $event->getOriginal('ends_on'),
                'new_starts_on' => $event->starts_on,
                'new_ends_on'   => $event->ends_on,
                'old_status'    => $event->getOriginal('status'),
                'new_status'    => $event->status,
                'changed_at'    => now(),
            ]);
        });
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ElectionEventRevision::class)->latest('changed_at');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /** Prochaine échéance publiée qui n'est pas encore passée. */
    public static function next(): ?self
    {
        $today = now()->toDateString();

        return static::published()
            ->whereIn('status', ['upcoming', 'ongoing', 'postponed'])
            ->where(fn (Builder $q) => $q->where('starts_on', '>=', $today)
                ->orWhere('ends_on', '>=', $today))
            ->orderByRaw('CASE WHEN starts_on >= ? THEN 0 ELSE 1 END', [$today])
            ->orderBy('starts_on')
            ->first();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function periodLabel(): string
    {
        $start = $this->starts_on?->translatedFormat('j F Y');

        return $this->ends_on
            ? $start.' → '.$this->ends_on->translatedFormat('j F Y')
            : (string) $start;
    }

    /** Pour la liste de l'administration. */
    public function getPublicationLabel(): string
    {
        return $this->is_published ? 'Publié' : 'Brouillon — à vérifier';
    }
}
