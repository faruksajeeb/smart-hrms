<?php

namespace App\Http\Requests\HR\Leave;

use Illuminate\Foundation\Http\FormRequest;

class StoreOpeningBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'effective_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string'],
            'reason' => ['nullable', 'string'],
        ];
    }
}
