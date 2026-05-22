<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Support\AccessControl;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));

        $users = User::query()
            ->with('roles:id,name')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'status' => $user->status,
                'primary_role' => $user->primaryRole(),
                'roles' => $user->roles->pluck('name')->values()->all(),
                'can_delete' => auth()->id() !== $user->id,
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
            ],
            'stats' => [
                'total' => User::count(),
                'active' => User::where('status', User::STATUS_ACTIVE)->count(),
                'inactive' => User::where('status', User::STATUS_INACTIVE)->count(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Users/Create', $this->formOptions());
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'employee_id' => $request->filled('employee_id') ? $request->string('employee_id')->toString() : null,
            'password' => Hash::make($request->string('password')->toString()),
            'status' => $request->string('status')->toString(),
        ]);

        $user->syncRoles($this->resolveRoles($request));

        event(new Registered($user));

        return to_route('admin.users.index')
            ->with('success', "{$user->name} has been created successfully.");
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): Response
    {
        $user->load('roles:id,name');

        return Inertia::render('Admin/Users/Edit', [
            ...$this->formOptions(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'status' => $user->status,
                'primary_role' => $user->primaryRole(),
                'additional_roles' => $user->roles
                    ->pluck('name')
                    ->reject(fn (string $roleName) => AccessControl::isProtectedRole($roleName))
                    ->values()
                    ->all(),
            ],
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $roles = $this->resolveRoles($request);

        if ($user->id === auth()->id() && $request->string('status')->toString() === User::STATUS_INACTIVE) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        if (
            $user->hasRole(User::ROLE_ADMIN)
            && ! in_array(User::ROLE_ADMIN, $roles, true)
            && User::role(User::ROLE_ADMIN)->whereKeyNot($user->id)->count() === 0
        ) {
            return back()->with('error', 'The last admin must retain the admin role.');
        }

        $user->fill([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'employee_id' => $request->filled('employee_id') ? $request->string('employee_id')->toString() : null,
            'status' => $request->string('status')->toString(),
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->string('password')->toString());
        }

        $user->save();
        $user->syncRoles($roles);

        return to_route('admin.users.index')
            ->with('success', "{$user->name} has been updated successfully.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->hasRole(User::ROLE_ADMIN) && User::role(User::ROLE_ADMIN)->count() <= 1) {
            return back()->with('error', 'You cannot delete the last admin account.');
        }

        $name = $user->name;
        $user->delete();

        return to_route('admin.users.index')
            ->with('success', "{$name} has been deleted successfully.");
    }

    /**
     * Build shared form options for create and edit views.
     *
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'primaryRoles' => AccessControl::systemRoles(),
            'additionalRoles' => Role::query()
                ->whereNotIn('name', AccessControl::systemRoles())
                ->orderBy('name')
                ->pluck('name')
                ->values()
                ->all(),
            'statusOptions' => [
                User::STATUS_ACTIVE,
                User::STATUS_INACTIVE,
            ],
        ];
    }

    /**
     * Resolve the role payload from a user form request.
     *
     * @return array<int, string>
     */
    protected function resolveRoles(StoreUserRequest|UpdateUserRequest $request): array
    {
        $additionalRoles = collect($request->input('additional_roles', []))
            ->filter(fn (mixed $roleName) => is_string($roleName) && $roleName !== '')
            ->values()
            ->all();

        return array_values(array_unique([
            $request->string('primary_role')->toString(),
            ...$additionalRoles,
        ]));
    }
}
