<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('front.contact');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag(
            'contact',
            [
                'name' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'email' => [
                    'required',
                    'email:rfc',
                    'max:255',
                ],

                'phone' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'subject' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'message' => [
                    'required',
                    'string',
                    'min:10',
                    'max:5000',
                ],

                'website' => [
                    'nullable',
                    'max:0',
                ],
            ],
            [
                'name.required' => 'Veuillez saisir votre nom.',
                'email.required' => 'Veuillez saisir votre adresse courriel.',
                'email.email' => 'L’adresse courriel est invalide.',
                'subject.required' => 'Veuillez indiquer le sujet du message.',
                'message.required' => 'Veuillez écrire votre message.',
                'message.min' => 'Votre message doit contenir au moins 10 caractères.',
                'message.max' => 'Votre message ne peut pas dépasser 5 000 caractères.',
                'website.max' => 'Le message n’a pas pu être envoyé.',
            ]
        );

        ContactMessage::query()->create([
            'name' => trim($validated['name']),
            'email' => mb_strtolower(
                trim($validated['email'])
            ),
            'phone' => $validated['phone'] ?? null,
            'subject' => trim($validated['subject']),
            'message' => trim($validated['message']),
            'status' => 'unread',
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit(
                (string) $request->userAgent(),
                1000,
                ''
            ),
        ]);

        return back()->with(
            'contact_success',
            'Votre message a bien été envoyé à Lambi News.'
        );
    }
}