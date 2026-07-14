<?php

namespace App\Http\Requests\HR;

use App\Models\EmployeeProfile;
use App\Models\User;
use App\Services\EmployeeMasterDataService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreEmployeeRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->accountRules(),
            ...$this->profileRules(),
            ...app(EmployeeMasterDataService::class)->validationRules(),
            ...$this->documentRules(),
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function accountRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'employee_id' => ['required', 'string', 'max:50', Rule::unique(User::class, 'employee_id')],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function profileRules(): array
    {
        return [
            'employment_status' => ['required', Rule::in($this->employmentStatuses())],
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
            'joining_date' => ['nullable', 'date'],
            'probation_starts_on' => ['nullable', 'date'],
            'probation_ends_on' => ['nullable', 'date', 'after_or_equal:probation_starts_on'],
            'probation_status' => ['required', Rule::in($this->probationStatuses())],
            'confirmation_date' => ['nullable', 'date'],
            'leave_policy_name' => ['nullable', 'string', 'max:255'],
            'annual_leave_days' => ['required', 'integer', 'min:0', 'max:365'],
            'sick_leave_days' => ['required', 'integer', 'min:0', 'max:365'],
            'casual_leave_days' => ['required', 'integer', 'min:0', 'max:365'],
            'carry_forward_leave_days' => ['required', 'integer', 'min:0', 'max:365'],
            'salary_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'salary_currency' => ['required', 'string', 'size:3'],
            'pay_frequency' => ['nullable', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'tax_identifier' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function documentRules(): array
    {
        $rules = [
            'document_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
            'document_cv' => ['nullable', 'file', 'mimes:pdf,docx,txt,md', 'max:10240'],
            'document_nid' => ['nullable', 'file', 'max:10240'],
            'document_passport' => ['nullable', 'file', 'max:10240'],
            'document_certificates' => ['nullable', 'file', 'max:10240'],
            'document_appointment_letter' => ['nullable', 'file', 'max:10240'],
            'document_joining_letter' => ['nullable', 'file', 'max:10240'],
        ];

        foreach (array_keys($rules) as $field) {
            $suffix = str_replace('document_', '', $field);
            $rules["document_{$suffix}_expiry_date"] = ['nullable', 'date'];
            $rules["document_{$suffix}_remarks"] = ['nullable', 'string', 'max:5000'];
        }

        return $rules;
    }

    /**
     * @return array<int, string>
     */
    protected function employmentStatuses(): array
    {
        return [
            EmployeeProfile::STATUS_ONBOARDING,
            EmployeeProfile::STATUS_PROBATION,
            EmployeeProfile::STATUS_ACTIVE,
            EmployeeProfile::STATUS_LEFT,
            EmployeeProfile::STATUS_TERMINATED,
            EmployeeProfile::STATUS_REJOINED,
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function probationStatuses(): array
    {
        return [
            EmployeeProfile::PROBATION_PENDING,
            EmployeeProfile::PROBATION_CONFIRMED,
            EmployeeProfile::PROBATION_EXTENDED,
            EmployeeProfile::PROBATION_FAILED,
        ];
    }
}
