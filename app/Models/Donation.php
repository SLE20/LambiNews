<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Donation extends Model
{
    use CrudTrait;

    public const STATUS_PENDING   = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'reference',
        'amount',
        'currency',
        'donor_name',
        'donor_email',
        'message',
        'is_anonymous',
        'status',
        'provider',
        'checkout_url',
        'paypal_order_id',
        'paypal_capture_id',
        'payer_email',
        'payload',
        'ip_hash',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'is_anonymous' => 'boolean',
            'payload'      => 'array',
            'paid_at'      => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Donation $donation): void {
            if (blank($donation->reference)) {
                $donation->reference = 'LN-'.Str::upper(Str::random(10));
            }
        });
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /** Nom à afficher publiquement, en respectant l’anonymat demandé. */
    public function getPublicNameAttribute(): string
    {
        if ($this->is_anonymous || blank($this->donor_name)) {
            return 'Yon donatè anonim';
        }

        return $this->donor_name;
    }

    /** Nom affiché dans l’administration (l’anonymat y reste visible). */
    public function getDisplayName(): string
    {
        $name = filled($this->donor_name) ? $this->donor_name : '—';

        return $this->is_anonymous ? $name.' (anonyme)' : $name;
    }

    public function getFormattedAmount(): string
    {
        return number_format((float) $this->amount, 2).' '.$this->currency;
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'Encaissé',
            self::STATUS_PENDING   => 'En attente',
            self::STATUS_CANCELLED => 'Annulé',
            self::STATUS_FAILED    => 'Échoué',
            default                => $this->status,
        };
    }
}
