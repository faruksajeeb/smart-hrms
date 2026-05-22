<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the user creation view for admins.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Users/Create', [
            'availableRoles' => Role::query()->orderBy('name')->pluck('name')->values()->all(),
            'statusOptions' => [
                User::STATUS_ACTIVE,
                User::STATUS_INACTIVE,
            ],
        ]);
    }

    /**
     * Handle an incoming user creation request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'employee_id' => ['nullable', 'string', 'max:50', Rule::unique(User::class, 'employee_id')],
            'role' => ['required', Rule::in([
                User::ROLE_ADMIN,
                User::ROLE_HR,
                User::ROLE_EMPLOYEE,
            ])],
            'status' => ['required', Rule::in([
                User::STATUS_ACTIVE,
                User::STATUS_INACTIVE,
            ])],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'employee_id' => $request->employee_id,
            'password' => Hash::make($request->password),
            'status' => $request->status,
        ]);

        $user->assignRole($request->string('role')->toString());

        event(new Registered($user));

        return Redirect::route('admin.users.create')
            ->with('success', "{$user->name} has been created successfully.");
    }
}
