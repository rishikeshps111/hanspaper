<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReelSaleRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'customer_id' => [
                'required',
                Rule::exists('parties', 'id')->where(fn ($query) =>
                    $query->whereIn('party_type', ['customer', 'both'])->where('status', 1)
                ),
            ],
            'sale_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:5000'],
            'is_gst_applicable' => ['nullable', 'boolean'],
            'sgst_percentage' => ['nullable', 'required_if:is_gst_applicable,1', 'numeric', 'min:0', 'max:100'],
            'cgst_percentage' => ['nullable', 'required_if:is_gst_applicable,1', 'numeric', 'min:0', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.reel_id' => ['required', 'distinct', Rule::exists('reels', 'id')->where('is_active', true)],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.warehouse_quantities' => ['required', 'array', 'min:1'],
            'items.*.warehouse_quantities.*.warehouse_id' => [
                'required',
                Rule::exists('reel_warehouses', 'id')->where('is_active', true),
            ],
            'items.*.warehouse_quantities.*.quantity' => ['required', 'integer', 'min:0'],
        ];
    }
}
