<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRequest;
use App\Support\AccessControl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    /**
     * Display a listing of the permissions.
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));

        $permissions = Permission::query()
            ->withCount('roles')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Permission $permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
                'group_name' => $permission->group_name ?: (
                    str_contains($permission->name, '.')
                        ? explode('.', $permission->name, 2)[0]
                        : null
                ),
                'roles_count' => $permission->roles_count,
                'is_protected' => AccessControl::isProtectedPermission($permission->name),
            ]);

        return Inertia::render('Admin/Permissions/Index', [
            'permissions' => $permissions,
            'filters' => [
                'search' => $search,
            ],
            'stats' => [
                'total' => Permission::count(),
                'system' => Permission::query()->whereIn('name', AccessControl::systemPermissions())->count(),
                'custom' => Permission::query()->whereNotIn('name', AccessControl::systemPermissions())->count(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Permissions/Create');
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $name = $request->string('name')->toString();
        $groupName = $request->input('group_name');

        if (empty($groupName)) {
            $parts = explode('.', $name, 2);
            $groupName = $parts[0] ?? 'General';
        }

        $permission = Permission::create([
            'name' => $name,
            'group_name' => $groupName,
            'guard_name' => 'web',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return to_route('admin.permissions.index')
            ->with('success', "Permission {$permission->name} has been created successfully.");
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(Permission $permission): Response
    {
        return Inertia::render('Admin/Permissions/Edit', [
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
                'group_name' => $permission->group_name,
                'is_protected' => AccessControl::isProtectedPermission($permission->name),
            ],
        ]);
    }

    /**
     * Update the specified permission in storage.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        if (AccessControl::isProtectedPermission($permission->name)) {
            return back()->with('error', 'System permissions cannot be renamed.');
        }

        $permission->name = $request->string('name')->toString();
        $permission->group_name = $request->input('group_name');
        $permission->save();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return to_route('admin.permissions.index')
            ->with('success', "Permission {$permission->name} has been updated successfully.");
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy(Permission $permission): RedirectResponse
    {
        if (AccessControl::isProtectedPermission($permission->name)) {
            return back()->with('error', 'System permissions cannot be deleted.');
        }

        $name = $permission->name;
        $permission->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return to_route('admin.permissions.index')
            ->with('success', "Permission {$name} has been deleted successfully.");
    }
}
