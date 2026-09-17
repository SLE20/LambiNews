<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Une campagne porte deux images : la bannière, qui plante le décor, et
 * un portrait — le bénéficiaire, l'équipe, l'objet financé. C'est le
 * visage qui donne envie de donner ; la bannière seule reste abstraite.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fundraisers', function (Blueprint $table): void {
            $table->string('photo')->nullable()->after('cover_image');
        });
    }

    public function down(): void
    {
        Schema::table('fundraisers', function (Blueprint $table): void {
            $table->dropColumn('photo');
        });
    }
};
