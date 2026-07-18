<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWeeklyOffPolicyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage attendance') ?? false;
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Policy Information
            |--------------------------------------------------------------------------
            */

            'company_id' => [
                'required',
                'exists:master_data_items,id',
            ],

            'policy_name' => [
                'required',
                'string',
                'max:100',
            ],

            'policy_code' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('weekly_off_policies', 'policy_code')
                    ->where(fn ($q) => $q->where('company_id', $this->company_id)),
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'status' => [
                'required',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Weekly Off Rules
            |--------------------------------------------------------------------------
            */

            'days' => [
                'required',
                'array',
                'min:1',
            ],

            'days.*.day_of_week' => [
                'required',
                'integer',
                'between:0,6',
            ],

            'days.*.week_type' => [
                'required',
                Rule::in([
                    'every',
                    'odd',
                    'even',
                    'specific',
                ]),
            ],

            'days.*.week_number' => [
                'nullable',
                'integer',
                'between:1,5',
            ],

            'days.*.off_type' => [
                'required',
                Rule::in([
                    'full_day',
                    'first_half',
                    'second_half',
                ]),
            ],

            'days.*.effective_from' => [
                'nullable',
                'date',
            ],

            'days.*.effective_to' => [
                'nullable',
                'date',
                'after_or_equal:days.*.effective_from',
            ],

            'days.*.status' => [
                'required',
                'boolean',
            ],
        ];
    }

    /**
     * Validation messages.
     */
    public function messages(): array
    {
        return [

            'days.required' =>
                'Please configure at least one weekly off day.',

            'days.min' =>
                'At least one weekly off day is required.',

            'days.*.day_of_week.required' =>
                'Day of week is required.',

            'days.*.day_of_week.between' =>
                'Invalid day selected.',

            'days.*.week_type.required' =>
                'Week type is required.',

            'days.*.off_type.required' =>
                'Off type is required.',
        ];
    }

    /**
     * Prepare request data.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => filter_var(
                $this->status,
                FILTER_VALIDATE_BOOLEAN
            ),
        ]);
    }
}