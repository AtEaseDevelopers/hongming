<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Crypt;

class UpdateMachineRentalRequest extends FormRequest
{
    public function rules()
    {
        $machineRentalId = $this->route('machineRental');
        $id = Crypt::decrypt($machineRentalId);

        $machineRental = \App\Models\MachineRental::find($id);

        $deliveryOrderNumberRule = ['required', 'string', 'max:255'];

        // Only check uniqueness if the delivery order number is being changed
        if ($this->input('delivery_order_number') !== $machineRental->delivery_order_number) {
            $deliveryOrderNumberRule[] = Rule::unique('machine_rental')->ignore($machineRentalId);
        }

        return [
            'date' => 'required|date',
            'delivery_order_number' => $deliveryOrderNumberRule,
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