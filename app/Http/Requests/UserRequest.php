<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        $rules = [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'role' => ['sometimes', 'required', 'string', Rule::in(['admin', 'manager', 'staff'])],
            'status' => ['sometimes', 'boolean'],
        ];

        // for creating a new user
        if ($this->isMethod('post')) {
            $rules['email'] = ['required', 'string', 'email', 'max:255', 'unique:users'];
            $rules['password'] = ['required', 'string', 'min:8'];
        }

        // for updating an existing user
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $userId = $this->route('user')->id;
            $rules['email'] = ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userId)];
            $rules['password'] = ['sometimes', 'nullable', 'string', 'min:8'];
        }

        return $rules;
    }
}
