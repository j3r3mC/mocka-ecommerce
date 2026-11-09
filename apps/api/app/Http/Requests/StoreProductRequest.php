<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ProductCondition;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
                'required',
                'string',
                'max:150',
            ],

            'description' => [
                'required',
                'string',
                'max:5000',
            ],

            'price_cents' => [
                'required',
                'integer',
                'min:1',
            ],

            'condition' => [
                'required',
                Rule::enum(ProductCondition::class),
            ],

            'city' => [
                'required',
                'string',
                'max:100',
            ],
        ];
    }
}