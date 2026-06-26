<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

class ItemTransactionRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {


        $rulesArray = [
            'item_id' => 'required|integer|exists:items,id',
            'quantity' => 'required|numeric|min:0',
        ];

        if ($this->isMethod('PUT')) {
            $itemId                     = $this->input('item_id');
            $rulesArray['quantity']        = ['required', 'min:0'];
        }else{
            $itemId                     = $this->input('item_id');
            $rulesArray['quantity']        = ['required', 'min:0'];
        }

        
        return $rulesArray;

    }
    public function messages(): array
    {
        $responseMessages = [];

        if ($this->isMethod('PUT')) {
            $responseMessages['item_id.required']    = 'ID Not found to update record';
        }

        return $responseMessages;
    }
    /**
     * Get the "after" validation callables for the request.
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $data = $validator->getData();
            $data['sale_price']             = $data['sale_price']??0;
            $data['sale_price_discount']    = $data['sale_price_discount']??0;
            $data['purchase_price']         = $data['purchase_price']??0;
            $data['wholesale_price']        = $data['wholesale_price']??0;
            $data['min_stock']              = $data['min_stock']??0;
            $data['opening_quantity']       = $data['opening_quantity']??0;
            $data['at_price']               = $data['at_price']??0;
            $data['conversion_rate']        = ($data['is_service']) ? 1 : $data['conversion_rate'];
            $data['tracking_type']          = ($data['is_service']) ? 'regular' : $data['tracking_type'];
            $data['min_stock']              = ($data['is_service']) ? 0 : $data['min_stock'];
            $data['item_location']          = ($data['is_service']) ? null : $data['item_location'];
            $data['sku']                    = $data['sku'] ?? null;
            $data['mrp']                    = $data['mrp'] ?? 0;
            $data['msp']                    = $data['msp'] ?? 0;

            $this->replace($data);
        });
    }
}
