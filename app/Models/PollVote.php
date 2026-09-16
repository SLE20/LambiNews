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
        'payment_status',
        'amount',
        'paypal_order_id',
        'paypal_capture_id',
        'paid_at',
        'voted_at',
    ];

    public const PAY_FREE    = 'free';
    public const PAY_PENDING = 'pending_payment';
    public const PAY_PAID    = 'paid';

    protected function casts(): array
    {
        return [
            'is_void'  => 'boolean',
            'amount'   => 'decimal:2',
            'paid_at'  => 'datetime',
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
