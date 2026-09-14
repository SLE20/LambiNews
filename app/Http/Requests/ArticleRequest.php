<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }


    protected function prepareForValidation(): void
{
    $user = backpack_user();

    if ($user?->role === 'author') {
        $status = in_array(
            $this->input('status'),
            ['draft', 'review'],
            true
        )
            ? $this->input('status')
            : 'draft';

        $this->merge([
            'author_id' => $user->id,
            'status' => $status,
        ]);
    }
}

    public function rules(): array
    {
        $articleId = $this->route('id');

        return [
            'category_id' => [
                'required',
                'integer',
                'exists:categories,id',
            ],
            'is_breaking' => [
    'boolean',
],

            'author_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('articles', 'slug')->ignore($articleId),
            ],

            'excerpt' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'content' => [
                'required',
                'string',
            ],

            'featured_image' => [
    'nullable',
    'image',
    'mimes:jpg,jpeg,png,webp',
    'max:5120',
],

            'image_caption' => [
                'nullable',
                'string',
                'max:255',
            ],

           'status' => [
    'required',
    Rule::in(
        backpack_user()?->role === 'author'
            ? ['draft', 'review']
            : ['draft', 'review', 'scheduled', 'published', 'archived']
    ),
],

            'published_at' => [
    Rule::requiredIf(
        fn () => $this->input('status') === 'scheduled'
    ),
    'nullable',
    'date',
],

            'is_featured' => [
                'boolean',
            ],

            'allow_comments' => [
                'boolean',
            ],

            'seo_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'seo_description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'tags' => [
    'nullable',
    'array',
],

'tags.*' => [
    'integer',
    'distinct',
    'exists:tags,id',
],
        ];
    }

    public function messages(): array
{
    return [
        'published_at.required' =>
            'La date de publication est obligatoire pour un article programmé.',
    ];
}

    public function attributes(): array
    {
        return [
            'category_id' => 'rubrique',
            'author_id' => 'auteur',
            'title' => 'titre',
            'slug' => 'adresse URL',
            'excerpt' => 'résumé',
            'content' => 'contenu',
            'featured_image' => 'image principale',
            'image_caption' => 'légende',
            'status' => 'statut',
            'published_at' => 'date de publication',
            'seo_title' => 'titre SEO',
            'seo_description' => 'description SEO',
            'tags' => 'mots-clés',
            'tags.*' => 'mot-clé',
            'is_breaking' => 'dernière minute',
        ];
    }
}