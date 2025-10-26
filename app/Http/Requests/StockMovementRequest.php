<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class StockMovementRequest extends FormRequest
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
        return [
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Returns an array of custom attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'product',
            'type' => 'movement type',
            'quantity' => 'quantity',
            'notes' => 'notes',
        ];
    }

    /**
     * Returns an array of custom error messages for the stock movement form.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Please select a product.',
            'product_id.exists' => 'The selected product does not exist.',
            'type.required' => 'Please select a movement type.',
            'type.in' => 'Invalid movement type selected.',
            'quantity.required' => 'Please enter the quantity.',
            'quantity.integer' => 'Quantity must be a number.',
            'quantity.min' => 'Quantity must be at least 1.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }

    /**
     * Run custom validation after the default validation rules have been applied.
     *
     * If the request is trying to reduce the stock of a product, this method will check if there is enough stock.
     * If there is not enough stock, a custom error message will be added to the validator.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($this->type === 'out') {
                $product = Product::find($this->product_id);

                if ($product && $product->current_stock < $this->quantity) {
                    $validator->errors()->add(
                        'quantity',
                        "Insufficient stock. Current stock is {$product->current_stock} units, but you're trying to reduce by {$this->quantity} units."
                    );
                }
            }
        });
    }
}
