<?php

namespace App\Http\Requests\HR\Leave;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'leave_policy_id' => ['required', 'integer', 'exists:leave_policies,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_half_day' => ['boolean'],
            'half_day_session' => ['nullable', 'in:morning,afternoon'],
            'is_emergency' => ['boolean'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'attachments' => ['nullable', 'array'],
            'attachments.*.file_name' => ['required_with:attachments', 'string', 'max:255'],
            'attachments.*.file_path' => ['required_with:attachments', 'string', 'max:255'],
            'attachments.*.file_size' => ['nullable', 'integer', 'max:10240'],
            'attachments.*.mime_type' => ['nullable', 'string', 'max:100'],
        ];
    }
}
