<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxSkuLength = 15;
        return [
            'sku' => [
                'required',
                'string',
                'max:' . $maxSkuLength,
                Rule::unique('products', 'sku'),
                'regex:/^[A-Z0-9]{1,4}-[A-Z0-9]{1,4}-[0-9]{1,5}$/'
            ],

            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'current_stock' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.required' => 'The SKU field is required.',
            'sku.string' => 'The SKU must be a string.',
            'sku.max' => 'The SKU may not be greater than ' . $this->rules()['sku'][2] . ' characters.',
            'sku.unique' => 'The SKU has already been taken.',
            'sku.regex' => 'The SKU format is invalid. It should match the pattern XXX-YYY-ZZZ.',
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'description.string' => 'The description must be a string.',
            'description.max' => 'The description may not be greater than 255 characters.',
            'current_stock.required' => 'The current stock field is required.',
            'current_stock.integer' => 'The current stock must be an integer.',
            'current_stock.min' => 'The current stock must be at least 0.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response([
            "message" => "Validation Error",
            "errors" => $validator->getMessageBag()
        ], 422));
    }
}
