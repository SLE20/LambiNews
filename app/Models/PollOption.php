<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollOption extends Model
{
    use CrudTrait;

    protected $fillable = [
        'poll_id',
        'label',
        'subtitle',
        'image',
        'color',
        'party_logo',
        'position',
    ];

    /**
     * Palette de repli, dans l'ordre d'affichage.
     *
     * Elle évite qu'un sondage créé sans choisir de couleurs sorte en
     * camaïeu illisible.
     */
    private const PALETTE = [
        '#1d4ed8', '#dc2626', '#16a34a', '#ea580c',
        '#0d9488', '#7c3aed', '#64748b', '#eab308',
        '#db2777', '#0284c7',
    ];

    /** Couleur choisie par la rédaction, ou couleur de repli. */
    public function displayColor(int $index = 0): string
    {
        return filled($this->color)
            ? $this->color
            : self::PALETTE[$index % count(self::PALETTE)];
    }

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    /** Utilisé par le select de l’administration. */
    public function identifiableAttribute(): string
    {
        return 'label';
    }
}
