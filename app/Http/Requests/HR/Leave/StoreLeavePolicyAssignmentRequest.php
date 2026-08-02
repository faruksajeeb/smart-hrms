<?php

namespace App\Http\Requests\HR\Leave;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeavePolicyAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'leave_policy_id' => ['required', 'integer', 'exists:leave_policies,id'],
            'company_id' => ['nullable', 'integer', 'exists:master_data_items,id'],
            'branch_id' => ['nullable', 'integer', 'exists:master_data_items,id'],
            'division_id' => ['nullable', 'integer', 'exists:master_data_items,id'],
            'department_id' => ['nullable', 'integer', 'exists:master_data_items,id'],
            'section_id' => ['nullable', 'integer', 'exists:master_data_items,id'],
            'unit_id' => ['nullable', 'integer', 'exists:master_data_items,id'],
            'designation_id' => ['nullable', 'integer', 'exists:master_data_items,id'],
            'employment_type' => ['nullable', 'string', 'max:255'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after:effective_from'],
            'remarks' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
