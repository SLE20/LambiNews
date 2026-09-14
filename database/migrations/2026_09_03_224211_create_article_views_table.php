<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_views', function (Blueprint $table) {
            $table->id();

            $table->foreignId('article_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('visitor_hash', 64)->index();
            $table->string('session_id')->nullable()->index();
            $table->string('ip_hash', 64)->nullable()->index();

            $table->string('referrer', 2048)->nullable();
            $table->string('user_agent', 1024)->nullable();

            $table->string('device_type', 20)->nullable()->index();
            $table->string('browser', 50)->nullable();
            $table->string('operating_system', 50)->nullable();

            $table->string('country_code', 2)->nullable()->index();
            $table->string('city')->nullable();

            $table->timestamp('viewed_at')->index();
            $table->timestamps();

            $table->index(['article_id', 'viewed_at']);
            $table->index(['visitor_hash', 'article_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_views');
    }
};