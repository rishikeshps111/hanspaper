<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'reel_brand_id' => ['required', 'integer', 'exists:reel_brands,id'],
            'reel_type_id' => ['required', 'integer', 'exists:reel_types,id'],
            'reel_gsm_id' => ['required', 'integer', 'exists:reel_gsms,id'],
            'width' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
            'length' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:9999999999.99'],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'is_active' => ['required', 'boolean'],
            'remarks' => ['required', 'string', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'reel_brand_id' => 'brand',
            'reel_type_id' => 'reel type',
            'reel_gsm_id' => 'GSM',
        ];
    }
}
