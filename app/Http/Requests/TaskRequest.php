<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
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
        $statusRule = Rule::in(['pending', 'in_progress', 'done']);

        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'assigned_to' => 'sometimes|required|string|exists:users,id',
            'status' => "sometimes|required|{$statusRule}",
            'due_date' => 'sometimes|required|date|after_or_equal:today',
        ];
    }
}
