<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('site_settings')->insert([
            'key' => 'youtube_url', 'value' => '',
            'label' => 'Chaîne YouTube', 'hint' => null,
            'group' => 'reseaux', 'type' => 'url', 'position' => 35,
            'is_secret' => false, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('site_settings')->where('key', 'youtube_url')->delete();
    }
};
