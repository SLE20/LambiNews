<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Services\AdServer;
use Illuminate\Http\RedirectResponse;

/**
 * Compte le clic puis renvoie le visiteur vers l’annonceur.
 *
 * Passer par le site plutôt que de lier directement permet de produire
 * un chiffre vérifiable à remettre au client.
 */
class AdClickController extends Controller
{
    public function __invoke(AdServer $ads, Ad $ad): RedirectResponse
    {
        $ads->recordClick($ad);

        if (blank($ad->target_url)) {
            return redirect()->route('home');
        }

        return redirect()->away($ad->target_url, 302, [
            'Referrer-Policy' => 'no-referrer-when-downgrade',
        ]);
    }
}
