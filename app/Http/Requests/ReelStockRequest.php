<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReelStockRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'actual_code' => ['nullable', 'string', 'max:100'],
            'reel_id' => ['required', 'exists:reels,id'],
            'reel_provider_id' => ['required', 'exists:reel_providers,id'],
            'reel_warehouse_id' => ['required', 'exists:reel_warehouses,id'],
            'original_length' => ['required', 'numeric', 'gt:0', 'max:999999999.999'],
            'purchase_price' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'received_at' => ['required', 'date'],
            'is_active' => ['required', 'boolean'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
