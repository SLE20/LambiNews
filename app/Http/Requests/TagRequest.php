<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('tags', 'slug')
                    ->ignore($this->route('id')),
            ],

            
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'slug' => 'adresse URL',
        ];
    }
}