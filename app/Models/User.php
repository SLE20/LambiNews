<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use CrudTrait;
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'password',
        'role',
        'job_title',
        'bio',
        'photo',
        'facebook_url',
        'instagram_url',
        'tiktok_url',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if (blank($user->slug)) {
                $baseSlug = Str::slug($user->name);

                do {
                    $slug = $baseSlug.'-'.Str::lower(
                        Str::random(6)
                    );
                } while (
                    self::query()
                        ->where('slug', $slug)
                        ->when(
                            $user->exists,
                            fn ($query) => $query
                                ->whereKeyNot($user->id)
                        )
                        ->exists()
                );

                $user->slug = $slug;
            }
        });
    }

    public function articles(): HasMany
    {
        return $this->hasMany(
            Article::class,
            'author_id'
        );
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEditor(): bool
    {
        return in_array(
            $this->role,
            ['admin', 'editor'],
            true
        );
    }

    public function canAccessBackpack(): bool
    {
        return $this->is_active
            && in_array(
                $this->role,
                ['admin', 'editor', 'author'],
                true
            );
    }
}