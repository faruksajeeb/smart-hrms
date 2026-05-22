<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Support\AccessControl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));

        $roles = Role::query()
            ->with('permissions:id,name')
            ->withCount('users')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->values()->all(),
                'users_count' => $role->users_count,
                'is_protected' => AccessControl::isProtectedRole($role->name),
            ]);

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'filters' => [
                'search' => $search,
            ],
            'stats' => [
                'total' => Role::count(),
                'system' => Role::query()->whereIn('name', AccessControl::systemRoles())->count(),
                'custom' => Role::query()->whereNotIn('name', AccessControl::systemRoles())->count(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Roles/Create', [
            'permissions' => $this->permissions(),
        ]);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create([
            'name' => $request->string('name')->toString(),
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($request->input('permissions', []));
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return to_route('admin.roles.index')
            ->with('success', "Role {$role->name} has been created successfully.");
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role): Response
    {
        $role->load('permissions:id,name');

        return Inertia::render('Admin/Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name')->values()->all(),
                'is_protected' => AccessControl::isProtectedRole($role->name),
            ],
            'permissions' => $this->permissions(),
        ]);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        if (! AccessControl::isProtectedRole($role->name)) {
            $role->name = $request->string('name')->toString();
            $role->save();
        }

        $role->syncPermissions($request->input('permissions', []));
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return to_route('admin.roles.index')
            ->with('success', "Role {$role->name} has been updated successfully.");
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if (AccessControl::isProtectedRole($role->name)) {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Remove users from this role before deleting it.');
        }

        $name = $role->name;
        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return to_route('admin.roles.index')
            ->with('success', "Role {$name} has been deleted successfully.");
    }

    /**
     * Return the permission options for role management.
     *
     * @return array<int, array{name: string, group_name: string}>
     */
    protected function permissions(): array
    {
        return Permission::query()
            ->orderByRaw('CASE WHEN group_name IS NULL OR group_name = "" THEN 1 ELSE 0 END')
            ->orderBy('group_name')
            ->orderBy('name')
            ->get(['name', 'group_name'])
            ->map(fn (Permission $permission) => [
                'name' => $permission->name,
                'group_name' => $permission->group_name ?: (
                    str_contains($permission->name, '.')
                        ? str($permission->name)->before('.')->replace('-', ' ')->title()->toString()
                        : 'General'
                ),
            ])
            ->values()
            ->all();
    }
}
