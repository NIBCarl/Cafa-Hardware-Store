<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'string|max:255',
            'sku' => 'string|unique:products,sku,' . $this->product->id,
            'barcode' => 'nullable|string|unique:products,barcode,' . $this->product->id,
            'description' => 'nullable|string',
            'price' => 'numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'integer|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean'
        ];
    }
}
