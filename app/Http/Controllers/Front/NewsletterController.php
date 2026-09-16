<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'email' => [
                    'required',
                    'email:rfc',
                    'max:255',
                ],
            ],
            [
                'email.required' => 'Veuillez saisir votre adresse courriel.',
                'email.email' => 'Veuillez saisir une adresse courriel valide.',
            ]
        );

        Subscriber::query()->updateOrCreate(
            [
                'email' => mb_strtolower(
                    trim($validated['email'])
                ),
            ],
            [
                'is_active' => true,
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]
        );

        return back()->with(
            'newsletter_success',
            'Merci ! Votre inscription à l’infolettre est confirmée.'
        );
    }

    /**
     * Désabonnement en un clic depuis le courriel.
     *
     * Accepte GET et POST : Gmail et Outlook appellent cette URL en POST
     * via l'en-tête List-Unsubscribe-Post, sans ouvrir de navigateur.
     * La sécurité tient au jeton aléatoire contenu dans l'adresse.
     */
    public function unsubscribe(string $token): View
    {
        $subscriber = Subscriber::query()
            ->where('token', $token)
            ->firstOrFail();

        if ($subscriber->is_active) {
            $subscriber->forceFill([
                'is_active'       => false,
                'unsubscribed_at' => now(),
            ])->save();
        }

        return view('front.newsletter-unsubscribed', compact('subscriber'));
    }
}
