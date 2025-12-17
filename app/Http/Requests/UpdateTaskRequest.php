<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Task;

class UpdateTaskRequest extends FormRequest
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
            'driver_id' => 'required',
            'invoice_id' => 'nullable',
            'status' => 'required',
            'delivery_order_id' => 'required|exists:delivery_orders,id',
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
        ];

        // Add conditional validation for return reason
        if ($this->input('status') == 3) { // Returned status
            $rules['return_reason'] = 'required|string|max:255';
        } else {
            $rules['return_reason'] = 'nullable|string|max:255';
        }

        return $rules;
    }

    // Optional: Custom validation messages
    public function messages()
    {
        return [
            'return_reason.required' => 'Return reason is required when status is Returned.',
            'this_load.max' => 'This Load cannot be greater than the remaining quantity.',
        ];
    }
}
