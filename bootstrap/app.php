<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
         * Désabonnement en un clic : Gmail et Outlook appellent cette URL
         * en POST depuis l'en-tête List-Unsubscribe-Post, sans jeton CSRF.
         * La sécurité repose sur le jeton aléatoire présent dans l'URL.
         */
        $middleware->validateCsrfTokens(except: [
            'infolettre/dezabone/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
