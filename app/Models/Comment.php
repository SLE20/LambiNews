<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use CrudTrait;

    protected $fillable = [
        'article_id',
        'parent_id',
        'name',
        'email',
        'body',
        'status',
        'ip_address',
        'user_agent',
        'approved_at',
    ];

    protected $hidden = [
        'email',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Comment $comment) {
            if (
                $comment->status === 'approved'
                && blank($comment->approved_at)
            ) {
                $comment->approved_at = now();
            }

            if ($comment->status !== 'approved') {
                $comment->approved_at = null;
            }
        });
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->where('status', 'approved')
            ->oldest();
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }
}