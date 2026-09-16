<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            // Client : sert à regrouper les statistiques et à facturer.
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();

            /*
             * Emplacement sur le site. Liste fermée, définie dans
             * App\Models\Ad::positions().
             */
            $table->string('position', 40)->index();

            /*
             * image  : visuel téléversé + lien
             * html   : code fourni par l’annonceur
             * adsense: script d’une régie ; les impressions sont comptées
             *          par la régie, pas par nous
             */
            $table->string('type', 20)->default('image');

            $table->string('image')->nullable();
            $table->text('html_code')->nullable();
            $table->string('target_url')->nullable();
            $table->string('alt_text')->nullable();

            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();

            $table->boolean('is_active')->default(true);

            // Poids du tirage quand plusieurs bannières visent le même emplacement.
            $table->unsignedSmallInteger('weight')->default(1);

            $table->unsignedBigInteger('impressions_count')->default(0);
            $table->unsignedBigInteger('clicks_count')->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['is_active', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
