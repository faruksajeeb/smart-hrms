<?php

namespace App\Http\Requests\HR\Leave;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeavePolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'policy_name' => ['required', 'string', 'max:255'],
            'policy_code' => ['required', 'string', 'max:255', 'unique:leave_policies,policy_code'],
            'description' => ['nullable', 'string'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after:effective_from'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
