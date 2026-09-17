<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Trace d'un changement de date ou d'état dans le calendrier. */
class ElectionEventRevision extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'old_starts_on', 'old_ends_on', 'new_starts_on', 'new_ends_on',
        'old_status', 'new_status', 'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'old_starts_on' => 'date',
            'old_ends_on'   => 'date',
            'new_starts_on' => 'date',
            'new_ends_on'   => 'date',
            'changed_at'    => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(ElectionEvent::class, 'election_event_id');
    }

    public function summary(): string
    {
        $fmt = fn ($d) => $d?->translatedFormat('j F Y') ?? '—';
        $parts = [];

        $day = fn ($d) => $d?->toDateString();

        if ($day($this->old_starts_on) !== $day($this->new_starts_on)
            || $day($this->old_ends_on) !== $day($this->new_ends_on)) {
            $parts[] = 'date : '.$fmt($this->old_starts_on)
                .($this->old_ends_on ? ' → '.$fmt($this->old_ends_on) : '')
                .' devient '.$fmt($this->new_starts_on)
                .($this->new_ends_on ? ' → '.$fmt($this->new_ends_on) : '');
        }

        if ($this->old_status !== $this->new_status) {
            $parts[] = 'état : '.(ElectionEvent::STATUSES[$this->old_status] ?? $this->old_status)
                .' devient '.(ElectionEvent::STATUSES[$this->new_status] ?? $this->new_status);
        }

        return ucfirst(implode(' ; ', $parts));
    }
}
