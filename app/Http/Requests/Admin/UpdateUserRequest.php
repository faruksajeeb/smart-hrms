<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
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
        /** @var User $user */
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'employee_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique(User::class, 'employee_id')->ignore($user->id),
            ],
            'primary_role' => ['required', Rule::in(AccessControl::systemRoles())],
            'additional_roles' => ['nullable', 'array'],
            'additional_roles.*' => [
                'string',
                Rule::exists('roles', 'name')->where('guard_name', 'web'),
            ],
            'status' => ['required', Rule::in([User::STATUS_ACTIVE, User::STATUS_INACTIVE])],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }
}
