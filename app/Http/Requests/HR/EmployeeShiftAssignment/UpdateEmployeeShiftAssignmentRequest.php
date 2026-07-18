<?php

namespace App\Http\Requests\HR\EmployeeShiftAssignment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeShiftAssignmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage attendance');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],
            'shift_id' => [
                'required',
                'integer',
                Rule::exists('shifts', 'id')
                    ->where(function ($query) {
                        return $query->where('status', true);
                    }),
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
            'user_id' => 'Employee',
            'shift_id' => 'Shift',
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
            'shift_id.exists' => 'The selected shift is invalid or inactive.',
            'effective_to.after_or_equal' => 'Effective To must be on or after Effective From.',
        ];
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            // Ensure user_id is set from route parameter if not provided
            'user_id' => $this->user_id ?? $this->route('employee')?->id,
        ]);
    }
}