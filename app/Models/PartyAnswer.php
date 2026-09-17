<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Réponse d'une structure politique, publiée telle quelle. */
class PartyAnswer extends Model
{
    use CrudTrait;

    protected $fillable = ['political_party_id', 'party_question_id', 'answer', 'source_url', 'received_on'];

    protected function casts(): array
    {
        return ['received_on' => 'date'];
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(PoliticalParty::class, 'political_party_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(PartyQuestion::class, 'party_question_id');
    }
}
