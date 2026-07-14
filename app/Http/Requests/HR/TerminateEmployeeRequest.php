<?php

namespace App\Http\Requests\HR;

use App\Models\EmployeeProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TerminateEmployeeRequest extends FormRequest
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
            'employment_status' => ['required', Rule::in([
                EmployeeProfile::STATUS_LEFT,
                EmployeeProfile::STATUS_TERMINATED,
            ])],
            'termination_date' => ['required', 'date'],
            'termination_type' => ['required', 'string', 'max:100'],
            'termination_reason' => ['required', 'string', 'max:5000'],
        ];
    }
}
