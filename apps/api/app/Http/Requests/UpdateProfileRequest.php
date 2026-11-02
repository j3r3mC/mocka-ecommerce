<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $profile = $this->user()?->profile;

        return [
            'username' => [
                'sometimes',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('profiles', 'username')
                    ->ignore($profile?->id),
            ],

            'bio' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],

            'city' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],
        ];
    }
}