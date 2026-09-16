<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class FundraiserContribution extends Model
{
    use CrudTrait;

    public const STATUS_PENDING   = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public const PROVIDER_PAYPAL  = 'paypal';
    public const PROVIDER_MONCASH = 'moncash';

    protected $fillable = [
        'fundraiser_id', 'reference', 'amount', 'currency',
        'donor_name', 'donor_email', 'donor_phone', 'message', 'is_anonymous',
        'provider', 'status', 'provider_order_id', 'provider_capture_id',
        'checkout_url', 'ip_hash', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'is_anonymous' => 'boolean',
            'paid_at'      => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (FundraiserContribution $c): void {
            if (blank($c->reference)) {
                $c->reference = 'KP-'.Str::upper(Str::random(10));
            }
        });
    }

    public function fundraiser(): BelongsTo
    {
        return $this->belongsTo(Fundraiser::class);
    }

    public function publicName(): string
    {
        return ($this->is_anonymous || blank($this->donor_name))
            ? 'Yon donatè anonim'
            : $this->donor_name;
    }

    public function getProviderLabel(): string
    {
        return $this->provider === self::PROVIDER_MONCASH ? 'MonCash' : 'PayPal';
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING   => 'En attente',
            self::STATUS_COMPLETED => 'Reçue',
            self::STATUS_FAILED    => 'Échouée',
            self::STATUS_CANCELLED => 'Annulée',
            default                => $this->status,
        };
    }

    public function getFormattedAmount(): string
    {
        return number_format((float) $this->amount, 2, ',', ' ').' '.$this->currency;
    }
}
