<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BarcodeWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', Rule::exists('parties', 'id')->where(fn ($query) => $query->where('status', 1)->whereIn('party_type', ['customer', 'both']))],
            'representative_id' => ['required', 'integer', Rule::exists('sales_representatives', 'id')->where('status', 'Active')],
            'work_order_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:work_order_date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.number_of_rolls' => ['required', 'integer', 'min:1'],
            'items.*.stickers_per_roll' => ['required', 'integer', 'min:1'],
            'items.*.sticker_length' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'items.*.sticker_width' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'items.*.type' => ['required', Rule::in(['DT', 'PROMO'])],
            'items.*.gap' => ['required', Rule::in(['with_gap', 'without_gap'])],
            'items.*.gap_mm' => ['nullable', 'required_if:items.*.gap,with_gap', 'numeric', 'gt:0'],
            'items.*.is_printing' => ['required', Rule::in(['yes', 'no'])],
            'items.*.printing_colors' => ['nullable', 'required_if:items.*.is_printing,yes', Rule::in(['single_color', 'two_color', 'multi_color'])],
            'items.*.remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_id' => 'customer', 'representative_id' => 'representative',
            'work_order_date' => 'date', 'items.*.number_of_rolls' => 'number of rolls',
            'items.*.stickers_per_roll' => 'stickers per roll', 'items.*.sticker_length' => 'sticker length',
            'items.*.sticker_width' => 'sticker width', 'items.*.gap_mm' => 'gap (mm)',
            'items.*.is_printing' => 'is printing', 'items.*.printing_colors' => 'printing colors',
        ];
    }
}
