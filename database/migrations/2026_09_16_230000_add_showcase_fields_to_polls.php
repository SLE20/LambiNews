<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            // Présentation « vitrine » : bandeau, accroche, grille de cartes.
            $table->string('layout', 20)->default('standard')->after('slug');
            $table->string('headline')->nullable()->after('layout');
            $table->string('eyebrow')->nullable()->after('headline');
            $table->string('subtitle')->nullable()->after('eyebrow');
            $table->string('hero_image')->nullable()->after('subtitle');
        });

        Schema::table('poll_options', function (Blueprint $table) {
            // Couleur de la carte, des barres et du camembert.
            $table->string('color', 7)->nullable()->after('image');
            $table->string('party_logo')->nullable()->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->dropColumn(['layout', 'headline', 'eyebrow', 'subtitle', 'hero_image']);
        });

        Schema::table('poll_options', function (Blueprint $table) {
            $table->dropColumn(['color', 'party_logo']);
        });
    }
};
