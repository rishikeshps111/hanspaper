<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
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
            'product' => 'required|exists:items,id',
            'requested_quantity' => 'required|numeric|min:1',
            'assigned_machine' => 'nullable|exists:machines,id',
            'assigned_production_user' => 'nullable|exists:employees,id',
            'assigned_packing_user' => 'nullable|exists:employees,id',
            'production_remark' => 'nullable|string',
            'packing_remark' => 'nullable|string',
            'dispatch_remark' => 'nullable|string',
        ];
    }
}
