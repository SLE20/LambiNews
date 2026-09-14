<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CommentController extends Controller
{
    public function store(
        Request $request,
        string $slug
    ): RedirectResponse {
        $article = Article::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        abort_unless(
            $article->allow_comments,
            403
        );

        $validated = $request->validateWithBag(
            'comment',
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

                'body' => [
                    'required',
                    'string',
                    'min:3',
                    'max:2000',
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

                'body.required' => 'Veuillez écrire votre commentaire.',

                'body.min' => 'Le commentaire est trop court.',

                'body.max' => 'Le commentaire ne peut pas dépasser 2 000 caractères.',

                'website.max' => 'Le commentaire n’a pas pu être envoyé.',
            ]
        );

        $article->comments()->create([
            'name' => trim($validated['name']),

            'email' => mb_strtolower(
                trim($validated['email'])
            ),

            'body' => trim($validated['body']),

            'status' => 'pending',

            'ip_address' => $request->ip(),

            'user_agent' => Str::limit(
                (string) $request->userAgent(),
                1000,
                ''
            ),
        ]);

        return back()
            ->with(
                'comment_success',
                'Merci ! Votre commentaire sera publié après validation.'
            )
            ->withFragment('commentaires');
    }
}