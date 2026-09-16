<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('location')->nullable()->after('body');

            /*
             * Contact affiché publiquement. Distinct des coordonnées du
             * demandeur, qui restent privées : pour une petite annonce ou
             * une offre d'emploi, l'acheteur ou le candidat doit pouvoir
             * joindre l'annonceur, ce qui n'est pas le cas d'un avis de
             * décès.
             */
            $table->string('public_contact')->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['location', 'public_contact']);
        });
    }
};
