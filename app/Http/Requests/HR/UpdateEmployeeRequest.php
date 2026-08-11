<?php

namespace App\Http\Requests\HR;

use App\Models\User;
use App\Services\EmployeeMasterDataService;
use App\Enums\EmploymentMovementType;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateEmployeeRequest extends StoreEmployeeRequest
{
    public function rules(): array
    {
        /** @var User $employee */
        $employee = $this->route('employee');

        return [
            ...$this->accountRulesFor($employee),
            ...$this->updateProfileRules(),
            ...app(EmployeeMasterDataService::class)->validationRules(),
            ...$this->documentRules(),
            'manager_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }

    protected function updateProfileRules(): array
    {
        return [
            'employment_status' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['nullable', 'string', 'max:50'],
            'work_location' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', 'string', 'max:100'],
            'blood_group' => ['nullable', 'string', 'max:50'],
            'marital_status' => ['nullable', 'string', 'max:100'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'skills' => ['nullable', 'string', 'max:5000'],
            'experience_summary' => ['nullable', 'string', 'max:5000'],
            'shift_id' => ['nullable', 'integer', 'exists:shifts,id'],
            'weekly_off_policy_id' => ['nullable', 'integer', 'exists:weekly_off_policies,id'],
            'joining_date' => ['nullable', 'date'],
            'probation_starts_on' => ['nullable', 'date'],
            'probation_ends_on' => ['nullable', 'date', 'after_or_equal:probation_starts_on'],
            'probation_status' => ['nullable', 'string', 'max:255'],
            'confirmation_date' => ['nullable', 'date'],
            'leave_policy_name' => ['nullable', 'string', 'max:255'],
            'annual_leave_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'sick_leave_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'casual_leave_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'carry_forward_leave_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'salary_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'salary_currency' => ['nullable', 'string', 'max:255'],
            'pay_frequency' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'tax_identifier' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var User $employee */
            $employee = $this->route('employee');

            if (!$employee->hasSubsequentEmploymentMovement()) {
                return;
            }

            $protectedFields = [
                'company_master_data_id' => 'company_id',
                'branch_master_data_id' => 'branch_id',
                'division_master_data_id' => 'division_id',
                'department_master_data_id' => 'department_id',
                'designation_master_data_id' => 'designation_id',
                'employment_type_master_data_id' => 'employment_type_id',
                'manager_id' => 'reporting_manager_id',
                'joining_date' => 'joining_date',
            ];

            $profileProtectedFields = [
                'department',
                'designation',
                'employment_type',
                'work_location',
            ];

            $changed = [];

            foreach ($protectedFields as $requestField => $userField) {
                if ($this->filled($requestField) && (string) $this->input($requestField) !== (string) ($employee->$userField ?? '')) {
                    $changed[] = $requestField;
                }
            }

            $profile = $employee->employeeProfile;

            foreach ($profileProtectedFields as $field) {
                if ($this->filled($field) && (string) $this->input($field) !== (string) ($profile->$field ?? '')) {
                    $changed[] = $field;
                }
            }

            if (!empty($changed)) {
                $validator->errors()->add(
                    'employment_fields',
                    'Employment organization information must be changed through Employment Movement.'
                );
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    protected function accountRulesFor(User $employee): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($employee->id),
            ],
            'employee_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique(User::class, 'employee_id')->ignore($employee->id),
            ],
        ];
    }
}
