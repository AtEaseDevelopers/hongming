<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;
use Illuminate\Support\Facades\Crypt;

class UpdateProductRequest extends FormRequest
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
        $id = $this->route('product');
        $decryptedId = Crypt::decrypt($id);
        
        return [
            'code' => 'required|string|max:255|unique:products,code,'.$decryptedId,
            'name' => 'required|string|max:255',
            'status' => 'required',
            'type' => 'required',
            'countdown' => 'nullable|integer|min:1|max:1440',
            'uoms' => 'required|array|min:1',
            'uoms.*.name' => 'required|string|max:50',
            'uoms.*.price' => 'required|numeric|min:0',
            'created_at' => 'nullable',
            'updated_at' => 'nullable'
        ];
    }
}