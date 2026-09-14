<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Afficher une citation inspirante');

Schedule::command('articles:publish-scheduled')
    ->everyMinute()
    ->withoutOverlapping();