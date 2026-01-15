<?php

namespace App\Http\Requests\User\Auth;

use Illuminate\Foundation\Http\FormRequest;

class EmailVerificationRequest extends FormRequest
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
            'token' => 'required',
        ];
    }

    /**
     * Custom message for the validation errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'token.required' => __('validation.required', ['attribute' => __('validation.attributes.token')]),
        ];
    }
}
