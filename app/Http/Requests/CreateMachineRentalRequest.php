<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateMachineRentalRequest extends FormRequest
{
    public function rules()
    {
        return [
            'date' => 'required|date',
            'delivery_order_number' => 'required|string|max:255|unique:machine_rental,delivery_order_number',
            'customer_id' => 'required|exists:customers,id',
            'company_id' => 'required|exists:companies,id',
            'product_id' => 'required|array',
            'product_id.*' => 'required|exists:products,id',
            'quantity' => 'required|array',
            'quantity.*' => 'required|integer|min:1',
            'unit_price' => 'required|array',
            'unit_price.*' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
        ];
    }
}