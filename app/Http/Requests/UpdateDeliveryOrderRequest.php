<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\DeliveryOrder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;

class UpdateDeliveryOrderRequest extends FormRequest
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
        $userRole = Auth::user()->roles()->pluck('name')->first();
        $isAdmin = $userRole === 'admin';

        $rules = [
            'date' => 'required|date',
            'dono' => 'required|string|max:255',
            'place_name' => 'required|string|max:255',
            'place_address' => 'required|string|max:255',
            'place_latitude' => 'required|numeric',
            'place_longitude' => 'required|numeric',
            'customer_id' => 'required|exists:customers,id',
            'product_id' => 'required|exists:products,id',
            'company_id' => 'required|exists:companies,id',
            'total_order' => 'required|integer',
            'progress_total' => 'nullable|integer',
            'strength_at' => 'nullable|string|max:255',
            'slump' => 'nullable|string|max:255',
            'remark' => 'nullable|string|max:255',
            'status' => 'sometimes|in:0,1,2,3,4,5,6,7',
        ];

        if (!$isAdmin) {
            $rules['status'] = 'required|in:0,2,3,4,5,6,7'; // Exclude status 2
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'status.in' => 'You are not authorized to set status to ready to deliver. Please contact admin.',
        ];
    }
}
