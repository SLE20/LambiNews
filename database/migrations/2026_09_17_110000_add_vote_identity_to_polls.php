<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            /*
             * Comment on reconnaît un votant :
             *
             * device — appareil (IP + navigateur + cookie durable). Un
             *   téléphone et un ordinateur sur le même WiFi votent chacun
             *   une fois. C'est le réglage raisonnable en Haïti, où des
             *   milliers d'abonnés mobiles partagent une même IP publique.
             *
             * ip — connexion. Une seule voix par adresse IP, quel que soit
             *   l'appareil. Plus strict, mais fait taire tout un foyer ou
             *   un cybercafé.
             */
            $table->string('vote_identity', 10)->default('device')->after('max_votes_per_ip');
        });
    }

    public function down(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->dropColumn('vote_identity');
        });
    }
};
