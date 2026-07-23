<?php

namespace App\Http\Requests\HR;

use App\Models\User;
use App\Services\EmployeeMasterDataService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateEmployeeRequest extends StoreEmployeeRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $employee */
        $employee = $this->route('employee');

        return [
            ...$this->accountRulesFor($employee),
            ...$this->profileRules(),
            ...app(EmployeeMasterDataService::class)->validationRules(),
            ...$this->documentRules(),
            'manager_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
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
