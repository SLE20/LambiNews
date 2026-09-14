<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use CrudTrait;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'ip_address',
        'user_agent',
        'read_at',
        'answered_at',
    ];

    protected $hidden = [
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'answered_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ContactMessage $contactMessage) {
            if (
                in_array(
                    $contactMessage->status,
                    ['read', 'answered'],
                    true
                )
                && blank($contactMessage->read_at)
            ) {
                $contactMessage->read_at = now();
            }

            if (
                $contactMessage->status === 'answered'
                && blank($contactMessage->answered_at)
            ) {
                $contactMessage->answered_at = now();
            }
        });
    }
}