<?php

namespace App\Http\Requests\HR\EmployeeReportingManagerAssignment;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportingManagerAssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage attendance');
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'employee_id' => [
                'required',
                'integer',
                'exists:users,id',
                'different:manager_id',
            ],
            'manager_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'effective_from' => [
                'required',
                'date',
            ],
            'effective_to' => [
                'nullable',
                'date',
                'after_or_equal:effective_from',
            ],
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
            'remarks' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'employee_id' => 'Employee',
            'manager_id' => 'Reporting Manager',
            'effective_from' => 'Effective From',
            'effective_to' => 'Effective To',
            'assignment_type' => 'Assignment Type',
            'remarks' => 'Remarks',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'employee_id.different' => 'An employee cannot be their own reporting manager.',
            'effective_to.after_or_equal' => 'Effective To must be on or after Effective From.',
            'manager_id.exists' => 'The selected reporting manager is invalid.',
        ];
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'employee_id' => $this->employee_id ?? $this->route('assignment')?->user_id ?? $this->route('employee')?->id,
        ]);
    }
}
