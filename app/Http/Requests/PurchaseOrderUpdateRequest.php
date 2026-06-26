<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseOrderUpdateRequest extends FormRequest
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
            'party_id' => ['required', 'integer', Rule::exists('parties', 'id')->where('party_type', 'customer')],
            'representative_id' => 'nullable',
            'order_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:order_date',
            'item_id' => 'required|array|min:1',
            'item_id.*' => 'required|numeric|exists:items,id',

            'product_item_id' => 'nullable|array|min:1',
            'product_item_id.*' => 'nullable|numeric',

            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|numeric|min:1',

            'status' => 'nullable|array',
            'status.*' => 'nullable|string',

            'remarks' => 'nullable|array',
            'remarks.*' => 'nullable|string',
            'pakingremarks' => 'nullable|array',
            'pakingremarks.*' => 'nullable|string',
            'dispatchremarks' => 'nullable|array',
            'dispatchremarks.*' => 'nullable|string',

            'mode_of_delivery' => 'nullable|string|in:Company Vehicle,Direct Customer,Courier',
            'dispatch_status' => 'nullable|string|in:Pending,Dispatched,Completed,Dispatch Pending',
            'dispatch_remarks' => 'nullable|string',
            'stock' => 'nullable',
        ];
    }
}
