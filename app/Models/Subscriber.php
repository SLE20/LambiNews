<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    use CrudTrait;

    protected $fillable = [
        'email',
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