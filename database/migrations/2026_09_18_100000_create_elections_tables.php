<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Espace « Élections » : calendrier du CEP et son historique, acteurs
 * du processus, centres d'inscription et de vote, structures politiques
 * et leurs réponses au questionnaire de la rédaction.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('election_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->string('status', 20)->default('upcoming')->index();
            $table->string('source_label')->nullable();
            $table->string('source_url', 500)->nullable();
            $table->date('verified_on')->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
            $table->index('starts_on');
        });

        // Chaque changement de date est archivé : le calendrier a déjà
        // été reporté plusieurs fois, et le lecteur doit pouvoir le voir.
        Schema::create('election_event_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_event_id')->constrained()->cascadeOnDelete();
            $table->date('old_starts_on')->nullable();
            $table->date('old_ends_on')->nullable();
            $table->date('new_starts_on')->nullable();
            $table->date('new_ends_on')->nullable();
            $table->string('old_status', 20)->nullable();
            $table->string('new_status', 20)->nullable();
            $table->timestamp('changed_at')->useCurrent();
        });

        Schema::create('electoral_actors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon', 16)->nullable();
            $table->string('summary', 300);
            $table->text('role');
            $table->text('responsibilities')->nullable();
            $table->text('moments')->nullable();
            $table->text('watch_points')->nullable();
            $table->string('source_label')->nullable();
            $table->string('source_url', 500)->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('voting_centers', function (Blueprint $table) {
            $table->id();
            $table->string('department', 40)->index();
            $table->string('commune', 80);
            $table->string('section', 120)->nullable();
            $table->string('name');
            $table->string('address', 300)->nullable();
            $table->timestamps();
            $table->index(['department', 'commune']);
        });

        Schema::create('political_parties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('acronym', 60)->nullable();
            $table->unsignedSmallInteger('campaign_number')->nullable()->unique();
            $table->string('kind', 20)->default('structure');
            $table->string('logo')->nullable();
            $table->string('leader')->nullable();
            $table->string('website', 300)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('party_questions', function (Blueprint $table) {
            $table->id();
            $table->string('theme', 60);
            $table->string('question', 400);
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('party_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('political_party_id')->constrained()->cascadeOnDelete();
            $table->foreignId('party_question_id')->constrained()->cascadeOnDelete();
            $table->text('answer');
            $table->string('source_url', 500)->nullable();
            $table->date('received_on')->nullable();
            $table->timestamps();
            $table->unique(['political_party_id', 'party_question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('party_answers');
        Schema::dropIfExists('party_questions');
        Schema::dropIfExists('political_parties');
        Schema::dropIfExists('voting_centers');
        Schema::dropIfExists('electoral_actors');
        Schema::dropIfExists('election_event_revisions');
        Schema::dropIfExists('election_events');
    }
};
