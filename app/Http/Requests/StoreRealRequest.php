<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRealRequest extends FormRequest
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
            'real_no'   => 'required|string|max:200',
            'brand'     => 'required|exists:brands,id',
            'category'  => 'required|exists:item_categories,id',
            'gsm'       => 'required|string',
            'subcode'   => 'required|string|max:255',
            'width'     => 'required|numeric',
            'length'    => 'required|numeric',
            'weight'    => 'required|numeric',
            'is_active' => 'boolean',
        ];
    }
}
