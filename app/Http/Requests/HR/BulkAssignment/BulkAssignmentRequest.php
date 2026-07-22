<?php

namespace App\Http\Requests\HR\BulkAssignment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage attendance');
    }

    public function rules(): array
    {
        return [
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', 'exists:users,id'],
            'shift_id' => ['required', 'integer', 'exists:shifts,id'],
            'weekly_off_policy_id' => ['required', 'integer', 'exists:weekly_off_policies,id'],
            'effective_from' => ['required', 'date', 'after_or_equal:today'],
            'assignment_type' => [
                'required',
                Rule::in([
                    'initial',
                    'transfer',
                    'promotion',
                    'temporary',
                    'manual',
                ]),
            ],
            'remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'employee_ids' => 'Employees',
            'shift_id' => 'Shift',
            'weekly_off_policy_id' => 'Weekly Off Policy',
            'effective_from' => 'Effective From',
            'assignment_type' => 'Assignment Type',
            'remarks' => 'Remarks',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_ids.required' => 'Please select at least one employee.',
            'employee_ids.min' => 'Please select at least one employee.',
            'shift_id.exists' => 'The selected shift is invalid or inactive.',
            'weekly_off_policy_id.exists' => 'The selected weekly off policy is invalid or inactive.',
            'effective_from.after_or_equal' => 'Effective From must be today or a future date.',
        ];
    }
}
