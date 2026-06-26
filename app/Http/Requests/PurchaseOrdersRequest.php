<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseOrdersRequest extends FormRequest
{
    /**
     * Indicates if the validator should stop on the first rule failure.
     *
     * @var bool
     */
    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

     /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'party_id'          => ['required', 'integer', Rule::exists('parties', 'id')->where('party_type','customer')],
            'order_date'        => 'required|date',
            'due_date'          => 'required|date|after_or_equal:order_date',
            'order_status'      => 'required|string|in:Pending,Processing,Cancelled,Completed,Production,Dispatched,Ready to Dispatch,Dispatch Pending',
            'mode_of_dispatch'  => 'nullable|string|in:Self Pickup,Courier,Delivery Vehicle',
            'note'              => 'nullable|string',
            'is_active'         => 'nullable|boolean',

            'item_id'           => 'required|array|min:1',
            'item_id.*'         => 'required|numeric|exists:items,id',

            'quantity'          => 'required|array|min:1',
            'quantity.*'        => 'required|numeric|min:1',

            'status'            => 'nullable|array',
            'status.*'          => 'nullable|string',

            'remarks'           => 'nullable|array',
            'remarks.*'         => 'nullable|string',
            'pakingremarks'    => 'nullable|array',
            'pakingremarks.*'  => 'nullable|string',
            'dispatchremarks'    => 'nullable|array',
            'dispatchremarks.*'  => 'nullable|string',
            
             'mode_of_delivery'  => 'nullable|string|in:Company Vehicle,Direct Customer,Courier',
             'dispatch_status'   => 'nullable|string|in:Pending,Dispatched,Completed,Dispatch Pending',
             'dispatch_remarks'  => 'nullable|string',
             'stock'             => 'nullable',
             'representative_id' => 'nullable',
        ];
    }
}
