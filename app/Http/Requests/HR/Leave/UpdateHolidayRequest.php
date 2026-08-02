<?php

namespace App\Http\Requests\HR\Leave;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'holiday_name' => ['required', 'string', 'max:255'],
            'holiday_code' => ['required', 'string', 'max:255', Rule::unique('holiday_calendars', 'holiday_code')->ignore($this->route('holiday'))],
            'holiday_date' => ['required', 'date'],
            'holiday_type' => ['required', 'in:national,religious,company,branch,optional'],
            'is_recurring' => ['boolean'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
            'scopes' => ['nullable', 'array'],
            'scopes.*.company_id' => ['nullable', 'integer', 'exists:master_data_items,id'],
            'scopes.*.branch_id' => ['nullable', 'integer', 'exists:master_data_items,id'],
            'scopes.*.division_id' => ['nullable', 'integer', 'exists:master_data_items,id'],
            'scopes.*.department_id' => ['nullable', 'integer', 'exists:master_data_items,id'],
        ];
    }
}
