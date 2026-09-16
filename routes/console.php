<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Afficher une citation inspirante');

Schedule::command('articles:publish-scheduled')
    ->everyMinute()
    ->withoutOverlapping();

/*
 * Infolettre : petites vagues régulières plutôt qu'un envoi massif, que
 * l'hébergement mutualisé rejetterait au-delà de son quota horaire.
 */
Schedule::command('newsletter:send --limit=25')
    ->everyMinute()
    ->withoutOverlapping();