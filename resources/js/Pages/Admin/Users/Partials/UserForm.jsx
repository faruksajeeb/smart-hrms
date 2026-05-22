import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';

export default function UserForm({
    data,
    setData,
    errors,
    processing,
    primaryRoles,
    additionalRoles,
    statusOptions,
    submitLabel,
    isEdit = false,
}) {
    const toggleAdditionalRole = (roleName) => {
        const current = data.additional_roles ?? [];

        setData(
            'additional_roles',
            current.includes(roleName)
                ? current.filter((role) => role !== roleName)
                : [...current, roleName],
        );
    };

    return (
        <div className="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 className="text-lg font-semibold text-slate-950">
                    Account Details
                </h2>
                <p className="mt-2 text-sm text-slate-500">
                    Assign one system role for dashboard access, then add any
                    custom roles for extra access patterns.
                </p>

                <div className="mt-8 grid gap-5 md:grid-cols-2">
                    <div className="md:col-span-2">
                        <InputLabel htmlFor="name" value="Full Name" />
                        <TextInput
                            id="name"
                            value={data.name}
                            className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3"
                            onChange={(e) => setData('name', e.target.value)}
                        />
                        <InputError className="mt-2" message={errors.name} />
                    </div>

                    <div className="md:col-span-2">
                        <InputLabel htmlFor="email" value="Work Email" />
                        <TextInput
                            id="email"
                            type="email"
                            value={data.email}
                            className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3"
                            onChange={(e) => setData('email', e.target.value)}
                        />
                        <InputError className="mt-2" message={errors.email} />
                    </div>

                    <div>
                        <InputLabel htmlFor="employee_id" value="Employee ID" />
                        <TextInput
                            id="employee_id"
                            value={data.employee_id}
                            className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3"
                            onChange={(e) =>
                                setData('employee_id', e.target.value)
                            }
                        />
                        <InputError
                            className="mt-2"
                            message={errors.employee_id}
                        />
                    </div>

                    <div>
                        <InputLabel htmlFor="status" value="Status" />
                        <select
                            id="status"
                            value={data.status}
                            onChange={(e) => setData('status', e.target.value)}
                            className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-slate-400 focus:ring-slate-400"
                        >
                            {statusOptions.map((status) => (
                                <option key={status} value={status}>
                                    {status}
                                </option>
                            ))}
                        </select>
                        <InputError className="mt-2" message={errors.status} />
                    </div>

                    <div>
                        <InputLabel
                            htmlFor="primary_role"
                            value="Primary Workspace Role"
                        />
                        <select
                            id="primary_role"
                            value={data.primary_role}
                            onChange={(e) =>
                                setData('primary_role', e.target.value)
                            }
                            className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-slate-400 focus:ring-slate-400"
                        >
                            {primaryRoles.map((role) => (
                                <option key={role} value={role}>
                                    {role}
                                </option>
                            ))}
                        </select>
                        <InputError
                            className="mt-2"
                            message={errors.primary_role}
                        />
                    </div>

                    <div className="md:col-span-2">
                        <InputLabel value="Additional Roles" />
                        <div className="mt-3 grid gap-3 md:grid-cols-2">
                            {additionalRoles.length > 0 ? (
                                additionalRoles.map((role) => (
                                    <label
                                        key={role}
                                        className="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700"
                                    >
                                        <input
                                            type="checkbox"
                                            checked={(
                                                data.additional_roles ?? []
                                            ).includes(role)}
                                            onChange={() =>
                                                toggleAdditionalRole(role)
                                            }
                                            className="rounded border-slate-300 text-slate-950 focus:ring-slate-400"
                                        />
                                        <span>{role}</span>
                                    </label>
                                ))
                            ) : (
                                <div className="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                                    No custom roles created yet.
                                </div>
                            )}
                        </div>
                        <InputError
                            className="mt-2"
                            message={errors.additional_roles}
                        />
                    </div>

                    <div>
                        <InputLabel
                            htmlFor="password"
                            value={
                                isEdit ? 'New Password (Optional)' : 'Temporary Password'
                            }
                        />
                        <TextInput
                            id="password"
                            type="password"
                            value={data.password}
                            className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3"
                            onChange={(e) =>
                                setData('password', e.target.value)
                            }
                        />
                        <InputError className="mt-2" message={errors.password} />
                    </div>

                    <div>
                        <InputLabel
                            htmlFor="password_confirmation"
                            value="Confirm Password"
                        />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            value={data.password_confirmation}
                            className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3"
                            onChange={(e) =>
                                setData(
                                    'password_confirmation',
                                    e.target.value,
                                )
                            }
                        />
                    </div>

                    <div className="md:col-span-2">
                        <PrimaryButton
                            disabled={processing}
                            className="rounded-2xl bg-slate-950 px-5 py-3"
                        >
                            {processing ? 'Saving user...' : submitLabel}
                        </PrimaryButton>
                    </div>
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-slate-950 p-6 text-white shadow-sm">
                <h2 className="text-lg font-semibold">Management Notes</h2>
                <div className="mt-6 space-y-3 text-sm leading-6 text-slate-300">
                    <p className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        One system role controls dashboard routing and base access.
                    </p>
                    <p className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        Custom roles can be layered on top to grant extra permissions.
                    </p>
                    <p className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                        Inactive users stay in the directory but cannot sign in.
                    </p>
                </div>
            </section>
        </div>
    );
}
