<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Page du document du CEP où figure le centre : le lecteur peut vérifier. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voting_centers', function (Blueprint $table) {
            $table->unsignedSmallInteger('source_page')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('voting_centers', function (Blueprint $table) {
            $table->dropColumn('source_page');
        });
    }
};
