<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DispatchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    public function rules()
    {
        return [
            'purchase_order_id' => 'required|exists:purchase_order_masters,id',
            'purchase_order_identifier' => 'required|string|max:80',
            'dispatch_order'    => 'required|string|max:50',
            'customer_id'       => 'required|exists:customers,id',
            'remarks'           => 'nullable|string|max:2000',
            'mode_of_delivery'  => 'required|in:Courier,Direct Customer,Company Vehicle',
        ];
    }
}
