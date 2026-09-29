<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Espace « Médias » : les vidéos de la rédaction.
 *
 * Deux origines possibles. YouTube, quand la vidéo y est déjà publiée —
 * l'hébergement et la bande passante ne coûtent alors rien. Ou un
 * fichier servi par le site, pour une vidéo courte qu'on ne veut pas
 * mettre chez un tiers.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->string('source', 12)->default('youtube');
            $table->string('youtube_id', 20)->nullable();
            $table->string('video_url', 500)->nullable();
            $table->string('thumbnail')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();

            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();

            $table->boolean('is_published')->default(true)->index();
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable()->index();
            $table->unsignedBigInteger('views_count')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
