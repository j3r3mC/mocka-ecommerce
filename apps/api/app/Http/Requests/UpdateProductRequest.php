<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ProductCondition;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
        return [
            'title' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],

            'description' => [
                'sometimes',
                'required',
                'string',
                'max:5000',
            ],

            'price_cents' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
            ],

            'condition' => [
                'sometimes',
                'required',
                Rule::enum(ProductCondition::class),
            ],

            'city' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],
        ];
    }
}