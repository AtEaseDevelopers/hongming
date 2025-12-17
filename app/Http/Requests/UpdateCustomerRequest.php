<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Customer;

class CreateCustomerRequest extends FormRequest
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
        return [
            'code' => 'required|string|max:255|unique:customers,code',
            'company' => 'required|string|max:255',
            'paymentterm' => 'nullable',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:65535',
            'status' => 'required',
            'sst' => 'nullable|string|max:255',
            'tin' => 'nullable|string|max:255',
            'place_name' => 'nullable|string|max:255',
            'place_address' => 'nullable|string',
            'place_latitude' => 'nullable|numeric',
            'place_longitude' => 'nullable|numeric',
            'google_place_id' => 'nullable|string|max:255',
            'destinate_id' => 'nullable|string|max:255',
            'created_at' => 'nullable',
            'updated_at' => 'nullable'
        ];
    }
}