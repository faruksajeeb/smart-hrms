<?php

namespace App\Http\Requests\HR\EmployeeWeeklyOffAssignment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeWeeklyOffAssignmentRequest extends FormRequest
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

            'weekly_off_policy_id' => [

                'required',

                'integer',

                Rule::exists(
                    'weekly_off_policies',
                    'id'
                )->where(function ($query) {

                    return $query->where(
                        'status',
                        true
                    );

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
     * Friendly field names.
     */
    public function attributes(): array
    {
        return [

            'weekly_off_policy_id' => 'Weekly Off Policy',

            'effective_from' => 'Effective From',

            'effective_to' => 'Effective To',

            'assignment_type' => 'Assignment Type',

            'remarks' => 'Remarks',

        ];
    }

    /**
     * Custom messages.
     */
    public function messages(): array
    {
        return [

            'effective_to.after_or_equal' =>
                'Effective To must be on or after Effective From.',

            'weekly_off_policy_id.exists' =>
                'The selected weekly off policy is invalid or inactive.',

        ];
    }

    /**
     * Prepare request before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([

            'remarks' => $this->remarks
                ? trim($this->remarks)
                : null,

        ]);
    }
}