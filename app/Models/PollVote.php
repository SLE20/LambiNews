<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollVote extends Model
{
    protected $fillable = [
        'poll_id',
        'poll_option_id',
        'voter_hash',
        'ip_hash',
        'user_agent',
        'referrer',
        'is_void',
        'voted_at',
    ];

    protected function casts(): array
    {
        return [
            'is_void'  => 'boolean',
            'voted_at' => 'datetime',
        ];
    }

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(PollOption::class, 'poll_option_id');
    }
}
