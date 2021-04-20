<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostCodePhoneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'phone_number' => 'required',
            'sms_code' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'phone_number.required' => 'The phone number field is required',
            'sms_code.required'  => 'The sms code field is required',
        ];
    }
}
