<?php

use App\Models\Ad;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            // Jeton du lien de rapport remis à l’annonceur.
            $table->string('report_token', 40)->nullable()->unique()->after('id');
        });

        // Les bannières déjà créées doivent aussi avoir leur lien.
        Ad::query()->whereNull('report_token')->get()->each(function (Ad $ad): void {
            $ad->forceFill(['report_token' => Str::lower(Str::random(28))])->saveQuietly();
        });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn('report_token');
        });
    }
};
