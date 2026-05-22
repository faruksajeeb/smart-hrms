import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';

export default function RoleForm({
    data,
    setData,
    errors,
    processing,
    permissions,
    submitLabel,
    isProtected = false,
}) {
    const selectedPermissions = data.permissions ?? [];
    const totalPermissions = permissions.length;
    const permissionGroups = Object.values(
        permissions.reduce((groups, permission) => {
            const groupName = permission.group_name ?? 'General';

            if (!groups[groupName]) {
                groups[groupName] = {
                    name: groupName,
                    permissions: [],
                };
            }

            groups[groupName].permissions.push(permission);

            return groups;
        }, {}),
    );

    const togglePermission = (permissionName) => {
        setData(
            'permissions',
            selectedPermissions.includes(permissionName)
                ? selectedPermissions.filter(
                      (permission) => permission !== permissionName,
                  )
                : [...selectedPermissions, permissionName],
        );
    };

    const toggleGroupPermissions = (groupPermissions, shouldSelect) => {
        const names = groupPermissions.map((permission) => permission.name);

        setData(
            'permissions',
            shouldSelect
                ? Array.from(new Set([...selectedPermissions, ...names]))
                : selectedPermissions.filter(
                      (permission) => !names.includes(permission),
                  ),
        );
    };

    const selectAllPermissions = () => {
        setData(
            'permissions',
            permissions.map((permission) => permission.name),
        );
    };

    const deselectAllPermissions = () => {
        setData('permissions', []);
    };

    return (
        <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 className="text-lg font-semibold text-slate-950">
                    Role Configuration
                </h2>
                <p className="mt-2 text-sm text-slate-500">
                    Group permissions into reusable access bundles for your Smart
                    HR team.
                </p>

                <div className="mt-8">
                    <InputLabel htmlFor="name" value="Role Name" />
                    <TextInput
                        id="name"
                        value={data.name}
                        disabled={isProtected}
                        className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 disabled:cursor-not-allowed disabled:bg-slate-100"
                        onChange={(e) => setData('name', e.target.value)}
                    />
                    <InputError className="mt-2" message={errors.name} />
                    {isProtected && (
                        <p className="mt-2 text-sm text-slate-500">
                            System roles keep their fixed names, but you can
                            still adjust permissions.
                        </p>
                    )}
                </div>

                <div className="mt-8">
                    <InputLabel value="Permissions" />
                    <div className="mt-3 rounded-3xl border border-slate-200 bg-slate-50/80 p-4">
                        <div className="flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p className="text-sm font-semibold text-slate-900">
                                    {selectedPermissions.length} of{' '}
                                    {totalPermissions} permissions selected
                                </p>
                                <p className="mt-1 text-xs uppercase tracking-[0.2em] text-slate-400">
                                    {permissionGroups.length} groups available
                                </p>
                            </div>
                            <div className="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    onClick={selectAllPermissions}
                                    className="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                                >
                                    Select All
                                </button>
                                <button
                                    type="button"
                                    onClick={deselectAllPermissions}
                                    className="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                                >
                                    Deselect All
                                </button>
                            </div>
                        </div>

                        <div className="mt-4 space-y-4">
                            {permissionGroups.map((group) => {
                                const groupSelectedCount =
                                    group.permissions.filter((permission) =>
                                        selectedPermissions.includes(
                                            permission.name,
                                        ),
                                    ).length;

                                return (
                                    <section
                                        key={group.name}
                                        className="rounded-2xl border border-slate-200 bg-white p-4"
                                    >
                                        <div className="flex flex-col gap-3 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <h3 className="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">
                                                    {group.name}
                                                </h3>
                                                <p className="mt-1 text-sm text-slate-500">
                                                    {groupSelectedCount} of{' '}
                                                    {
                                                        group.permissions
                                                            .length
                                                    } selected
                                                </p>
                                            </div>
                                            <div className="flex flex-wrap gap-2">
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        toggleGroupPermissions(
                                                            group.permissions,
                                                            true,
                                                        )
                                                    }
                                                    className="rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                                                >
                                                    Select Group
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        toggleGroupPermissions(
                                                            group.permissions,
                                                            false,
                                                        )
                                                    }
                                                    className="rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                                                >
                                                    Deselect Group
                                                </button>
                                            </div>
                                        </div>

                                        <div className="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                            {group.permissions.map(
                                                (permission) => (
                                                    <label
                                                        key={permission.name}
                                                        className="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            checked={selectedPermissions.includes(
                                                                permission.name,
                                                            )}
                                                            onChange={() =>
                                                                togglePermission(
                                                                    permission.name,
                                                                )
                                                            }
                                                            className="rounded border-slate-300 text-slate-950 focus:ring-slate-400"
                                                        />
                                                        <span>
                                                            {permission.name}
                                                        </span>
                                                    </label>
                                                ),
                                            )}
                                        </div>
                                    </section>
                                );
                            })}
                        </div>
                    </div>
                    <InputError
                        className="mt-2"
                        message={errors.permissions}
                    />
                </div>

                <div className="mt-8">
                    <PrimaryButton
                        disabled={processing}
                        className="rounded-2xl bg-slate-950 px-5 py-3"
                    >
                        {processing ? 'Saving role...' : submitLabel}
                    </PrimaryButton>
                </div>
        </section>
    );
}
