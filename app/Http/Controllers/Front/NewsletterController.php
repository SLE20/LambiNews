<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
}