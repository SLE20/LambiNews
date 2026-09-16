<?php

use App\Models\Subscriber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Rattrape les abonnés créés sans jeton.
     *
     * Le hook de génération n'avait pas pris sur la première version :
     * sans jeton, le rendu du courriel échoue au moment de construire le
     * lien de désabonnement, et l'envoi entier tombe.
     */
    public function up(): void
    {
        Subscriber::query()->whereNull('token')->get()->each(function (Subscriber $s): void {
            $s->forceFill(['token' => Str::lower(Str::random(32))])->saveQuietly();
        });
    }

    public function down(): void
    {
        // Rien à défaire : on ne retire pas un jeton déjà distribué.
    }
};
