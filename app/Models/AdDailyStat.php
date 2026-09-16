<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdDailyStat extends Model
{
    protected $table = 'ad_daily_stats';

    protected $fillable = [
        'ad_id',
        'date',
        'impressions',
        'clicks',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }
}
