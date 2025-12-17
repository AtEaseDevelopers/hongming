<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Task;
use App\Models\DeliveryOrder;

class CreateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'date' => 'required',
            'task_number' => 'required|string|max:50',
            'lorry_id' => 'required',
            'company_id' => 'required',
            'invoice_id' => 'nullable',
            'status' => 'required',
            'delivery_order_id' => [
                'required',
                'exists:delivery_orders,id',
                function ($attribute, $value, $fail) {
                    $deliveryOrder = DeliveryOrder::find($value);
                    if ($deliveryOrder && $deliveryOrder->status == 6) {
                        $fail('The selected delivery order is already in Completed Status.');
                    }
                }
            ], 
            'countdown' => 'required|integer|min:1|max:1440', 
            'this_load' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) {
                    $deliveryOrderId = $this->input('delivery_order_id');

                    if ($deliveryOrderId) {
                        $deliveryOrder = \App\Models\DeliveryOrder::find($deliveryOrderId);

                        if ($deliveryOrder) {
                            $maxLoad = $deliveryOrder->total_order - $deliveryOrder->progress_total;

                            if ($value > $maxLoad) {
                                $fail("This Load cannot be greater than {$maxLoad}.");
                            }
                        }
                    }
                }
            ],
            'return_remarks' => 'nullable|string|max:255',
        ];

        // Add conditional validation for return reason when status is returned
        if ($this->input('status') == 3) { // Returned status
            $rules['return_reason'] = 'required|string|max:255';
        } else {
            $rules['return_reason'] = 'nullable|string|max:255';
        }

        // Add conditional validation for images when status is completed
        if ($this->input('status') == 2) { // Completed status
            $rules['signed_do_image'] = 'required|image|mimes:jpeg,png,jpg,gif|max:5120'; // 5MB max
            $rules['proof_of_delivery_image'] = 'required|image|mimes:jpeg,png,jpg,gif|max:5120'; // 5MB max
        } else {
            $rules['signed_do_image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120';
            $rules['proof_of_delivery_image'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'signed_do_image.required' => 'Signed Delivery Order image is required when status is Completed.',
            'proof_of_delivery_image.required' => 'Proof of Delivery image is required when status is Completed.',
            'signed_do_image.image' => 'The signed DO must be an image file.',
            'proof_of_delivery_image.image' => 'The proof of delivery must be an image file.',
            'signed_do_image.max' => 'The signed DO image must not be larger than 5MB.',
            'proof_of_delivery_image.max' => 'The proof of delivery image must not be larger than 5MB.',
            'return_reason.required' => 'Return reason is required when status is Returned.',
            'company_id.required' => 'The Branch field is required.',
            'company_id.exists' => 'The selected Branch is invalid.',
        ];
    }
}