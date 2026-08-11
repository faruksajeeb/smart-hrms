<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $group = Str::of((string) $this->input('group_name'))
            ->trim()
            ->lower()
            ->replaceMatches('/\s+/', '-')
            ->value();

        $action = Str::of((string) $this->input('action'))
            ->trim()
            ->lower()
            ->replaceMatches('/\s+/', '-')
            ->value();

        $this->merge([
            'group_name' => $group ?: null,
            'action' => $action ?: null,
            'name' => $this->input('name') ?: ($group ? "{$group}.{$action}" : $action),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'group_name' => ['nullable', 'string', 'max:255'],
            'action' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255', Rule::unique('permissions', 'name')->where('guard_name', 'web')],
        ];
    }
}
