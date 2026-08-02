<?php

namespace App\Http\Requests\HR\Leave;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'leave_name' => ['required', 'string', 'max:255'],
            'leave_code' => ['required', 'string', 'max:255', Rule::unique('leave_types', 'leave_code')->ignore($this->route('leave_type'))],
            'description' => ['nullable', 'string'],
            'is_paid' => ['boolean'],
            'display_color' => ['nullable', 'string', 'max:50'],
            'display_order' => ['integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
