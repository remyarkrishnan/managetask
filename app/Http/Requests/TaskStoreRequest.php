<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // We will handle authorization via Policies/Gates
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'string', 'in:low,medium,high'],
            'status' => ['required', 'string', 'in:pending,in_progress,completed'],
            'due_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
