<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->boolean('is_sponsored')->default(false)->after('is_breaking');

            $table->string('sponsor_name')->nullable()->after('is_sponsored');
            $table->string('sponsor_url')->nullable()->after('sponsor_name');
            $table->string('sponsor_logo')->nullable()->after('sponsor_url');

            $table->index('is_sponsored');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex(['is_sponsored']);
            $table->dropColumn([
                'is_sponsored',
                'sponsor_name',
                'sponsor_url',
                'sponsor_logo',
            ]);
        });
    }
};
