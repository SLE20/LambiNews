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
        'position',
    ];

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
