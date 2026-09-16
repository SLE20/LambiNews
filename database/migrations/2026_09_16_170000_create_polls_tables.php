<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            // Cacher les résultats tant que le visiteur n'a pas voté.
            $table->boolean('hide_results_before_vote')->default(false);
            $table->unsignedBigInteger('votes_count')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'ends_at']);
        });

        Schema::create('poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('subtitle')->nullable();
            $table->string('image')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->unsignedBigInteger('votes_count')->default(0);
            $table->timestamps();

            $table->index(['poll_id', 'position']);
        });

        Schema::create('poll_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('poll_option_id')->constrained()->cascadeOnDelete();

            /*
             * Empreinte du votant : HMAC de l'IP, du navigateur et de la
             * session, comme pour le comptage des lectures. Un index
             * unique sur (poll_id, voter_hash) limite à une voix.
             */
            $table->string('voter_hash', 64);
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('referrer', 255)->nullable();

            // Permet d'annuler en bloc les voix d'une même vague suspecte.
            $table->boolean('is_void')->default(false);

            $table->timestamp('voted_at');
            $table->timestamps();

            $table->unique(['poll_id', 'voter_hash']);
            $table->index(['poll_id', 'voted_at']);
            $table->index(['poll_id', 'ip_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_votes');
        Schema::dropIfExists('poll_options');
        Schema::dropIfExists('polls');
    }
};
