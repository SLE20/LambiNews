<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Canal Telegram public de Lambi News. */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('site_settings')
            ->where('key', 'telegram_url')
            ->where(fn ($q) => $q->whereNull('value')->orWhere('value', ''))
            ->update(['value' => 'https://t.me/lambinews12', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('site_settings')
            ->where('key', 'telegram_url')
            ->where('value', 'https://t.me/lambinews12')
            ->update(['value' => '', 'updated_at' => now()]);
    }
};
