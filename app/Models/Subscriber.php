<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Subscriber extends Model
{
    use CrudTrait;

    protected $fillable = [
        'email',
        'token',
        'is_active',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        /*
         * Jeton de désabonnement, indispensable au lien « se désabonner »
         * de chaque courriel.
         */
        static::creating(function (Subscriber $subscriber): void {
            if (blank($subscriber->token)) {
                $subscriber->token = Str::lower(Str::random(32));
            }
        });

        static::saving(function (Subscriber $subscriber) {
            if (!$subscriber->isDirty('is_active')) {
                return;
            }

            if ($subscriber->is_active) {
                $subscriber->subscribed_at ??= now();
                $subscriber->unsubscribed_at = null;

                return;
            }

            $subscriber->unsubscribed_at = now();
        });
    }
}