<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ad extends Model
{
    use CrudTrait;

    public const TYPE_IMAGE   = 'image';
    public const TYPE_HTML    = 'html';
    public const TYPE_ADSENSE = 'adsense';

    protected $fillable = [
        'name',
        'client_name',
        'client_email',
        'position',
        'type',
        'image',
        'html_code',
        'target_url',
        'alt_text',
        'starts_at',
        'ends_at',
        'is_active',
        'weight',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at'   => 'date',
            'is_active' => 'boolean',
            'weight'    => 'integer',
        ];
    }

    /**
     * Emplacements disponibles.
     *
     * Ajouter une entrée ici la rend immédiatement sélectionnable dans
     * l’administration ; il reste à poser <x-ad position="..."/> dans le
     * gabarit correspondant.
     *
     * @return array<string, string>
     */
    public static function positions(): array
    {
        return [
            'header'         => 'Bandeau haut de page (728×90)',
            'home_top'       => 'Accueil — sous les gros titres (970×250)',
            'sidebar_top'    => 'Colonne de droite — haut (300×250)',
            'sidebar_bottom' => 'Colonne de droite — bas (300×600)',
            'in_article'     => 'Dans l’article (336×280)',
            'below_article'  => 'Sous l’article (728×90)',
            'footer'         => 'Pied de page (728×90)',
        ];
    }

    /** @return array<string, string> */
    public static function types(): array
    {
        return [
            self::TYPE_IMAGE   => 'Image téléversée',
            self::TYPE_HTML    => 'Code HTML de l’annonceur',
            self::TYPE_ADSENSE => 'Régie (AdSense, Ezoic…)',
        ];
    }

    public function dailyStats(): HasMany
    {
        return $this->hasMany(AdDailyStat::class);
    }

    /**
     * Bannières diffusables aujourd’hui : actives et dans leur fenêtre de
     * programmation. Une date vide signifie « sans limite ».
     */
    public function scopeLive(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query
            ->where('is_active', true)
            ->where(fn (Builder $q) => $q
                ->whereNull('starts_at')
                ->orWhereDate('starts_at', '<=', $today))
            ->where(fn (Builder $q) => $q
                ->whereNull('ends_at')
                ->orWhereDate('ends_at', '>=', $today));
    }

    public function scopeForPosition(Builder $query, string $position): Builder
    {
        return $query->where('position', $position);
    }

    /** Taux de clics, en pourcentage. */
    public function getCtrAttribute(): float
    {
        if ($this->impressions_count < 1) {
            return 0.0;
        }

        return round(($this->clicks_count / $this->impressions_count) * 100, 2);
    }

    public function getPositionLabel(): string
    {
        return self::positions()[$this->position] ?? $this->position;
    }

    public function getTypeLabel(): string
    {
        return self::types()[$this->type] ?? $this->type;
    }

    /** Résumé « 1 234 vues / 56 clics / 4,54 % » pour l’administration. */
    public function getPerformance(): string
    {
        return number_format($this->impressions_count, 0, ',', ' ').' vues / '
            .number_format($this->clicks_count, 0, ',', ' ').' clics / '
            .number_format($this->ctr, 2, ',', ' ').' %';
    }

    /** Une bannière programmée mais pas encore commencée, ou déjà terminée. */
    public function getScheduleLabel(): string
    {
        if (! $this->is_active) {
            return 'Désactivée';
        }

        $today = now()->startOfDay();

        if ($this->starts_at && $this->starts_at->gt($today)) {
            return 'Programmée pour le '.$this->starts_at->format('d/m/Y');
        }

        if ($this->ends_at && $this->ends_at->lt($today)) {
            return 'Terminée le '.$this->ends_at->format('d/m/Y');
        }

        return $this->ends_at
            ? 'En ligne jusqu’au '.$this->ends_at->format('d/m/Y')
            : 'En ligne';
    }
}
