<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->unsignedBigInteger('telegram_message_id')->nullable()->after('views_count');
            $table->timestamp('telegram_posted_at')->nullable()->after('telegram_message_id');

            $table->index('telegram_posted_at');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex(['telegram_posted_at']);
            $table->dropColumn(['telegram_message_id', 'telegram_posted_at']);
        });
    }
};
