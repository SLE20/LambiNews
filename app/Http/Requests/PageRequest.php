<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_user()?->isEditor() === true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('pages', 'slug')
                    ->ignore($this->route('id')),
            ],

            'content' => [
                'required',
                'string',
            ],

            'meta_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'meta_description' => [
                'nullable',
                'string',
                'max:500',
            ],

            'is_active' => [
                'boolean',
            ],

            'show_in_footer' => [
                'boolean',
            ],

            'position' => [
                'required',
                'integer',
                'min:0',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'titre',
            'slug' => 'adresse URL',
            'content' => 'contenu',
            'meta_title' => 'titre SEO',
            'meta_description' => 'description SEO',
            'is_active' => 'statut',
            'show_in_footer' => 'affichage dans le pied de page',
            'position' => 'position',
        ];
    }
}