<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('subscribers', 'email')
                    ->ignore($this->route('id')),
            ],

            'is_active' => [
                'boolean',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'adresse courriel',
            'is_active' => 'statut',
        ];
    }
}