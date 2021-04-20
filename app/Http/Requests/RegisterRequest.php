<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required',
            'email' => 'required',
            'user_type' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'The name field is required',
            'email.required'  => 'The email field is required',
            'user_type.required'  => 'Select user type',
        ];
    }
}
