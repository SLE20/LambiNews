<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Statistiques agrégées par jour.
     *
     * On n’enregistre pas une ligne par affichage : sur un hébergement
     * mutualisé, une table d’événements bruts deviendrait vite
     * ingérable. Une ligne par bannière et par jour suffit à produire
     * les rapports remis aux annonceurs.
     */
    public function up(): void
    {
        Schema::create('ad_daily_stats', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ad_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('date');

            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);

            $table->timestamps();

            $table->unique(['ad_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_daily_stats');
    }
};
