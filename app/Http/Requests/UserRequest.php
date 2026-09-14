<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_user()?->isAdmin() === true;
    }

    protected function prepareForValidation(): void
    {
        if (blank($this->password)) {
            $this->request->remove('password');
        }

        $this->merge([
            'facebook_url' => $this->cleanUrl(
                $this->facebook_url
            ),

            'instagram_url' => $this->cleanUrl(
                $this->instagram_url
            ),

            'tiktok_url' => $this->cleanUrl(
                $this->tiktok_url
            ),
        ]);
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users', 'slug')
                    ->ignore($userId),
            ],

            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($userId),
            ],

            'password' => [
                $userId ? 'nullable' : 'required',
                Password::min(8),
            ],

            'role' => [
                'required',
                Rule::in([
                    'admin',
                    'editor',
                    'author',
                ]),
            ],

            'job_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'bio' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            'facebook_url' => [
                'nullable',
                'url:http,https',
                'max:255',
            ],

            'instagram_url' => [
                'nullable',
                'url:http,https',
                'max:255',
            ],

            'tiktok_url' => [
                'nullable',
                'url:http,https',
                'max:255',
            ],

            'is_active' => [
                'boolean',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'slug' => 'adresse URL',
            'email' => 'adresse courriel',
            'password' => 'mot de passe',
            'role' => 'rôle',
            'job_title' => 'fonction',
            'bio' => 'biographie',
            'photo' => 'photo',
            'facebook_url' => 'lien Facebook',
            'instagram_url' => 'lien Instagram',
            'tiktok_url' => 'lien TikTok',
            'is_active' => 'statut',
        ];
    }

    private function cleanUrl(
        mixed $value
    ): ?string {
        if (blank($value)) {
            return null;
        }

        $value = trim((string) $value);

        if (!str_starts_with($value, 'http://')
            && !str_starts_with($value, 'https://')) {
            return 'https://'.$value;
        }

        return $value;
    }
}