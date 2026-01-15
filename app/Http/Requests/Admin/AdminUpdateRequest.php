<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateRequest extends FormRequest
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
            'name'     => 'sometimes',
            'email'    => 'sometimes|email|unique:admins,email,' . $this->route('admin')->id,
        ];
    }

    /**
     * Get the validation messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.string'       => __('validation.string', ['attribute' => __('validation.attributes.name')]),
            'email.email'       => __('validation.email', ['attribute' => __('validation.attributes.email')]),
        ];
    }
}
