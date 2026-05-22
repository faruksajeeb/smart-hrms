import DangerButton from '@/Components/DangerButton';
import Pagination from '@/Components/Pagination';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ roles, filters, stats }) {
    const [search, setSearch] = useState(filters.search ?? '');

    const submitSearch = (e) => {
        e.preventDefault();

        router.get(
            route('admin.roles.index'),
            { search },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const destroyRole = (role) => {
        if (
            !window.confirm(
                `Delete role "${role.name}"? Users must be removed from it first.`,
            )
        ) {
            return;
        }

        router.delete(route('admin.roles.destroy', role.id), {
            preserveScroll: true,
        });
    };

    const cards = [
        { label: 'Total Roles', value: stats.total },
        { label: 'System Roles', value: stats.system },
        { label: 'Custom Roles', value: stats.custom },
    ];

    return (
        <AdminLayout
            heading="Role Management"
            subheading="Define reusable role bundles and control which permissions travel with each one."
        >
            <Head title="Role Management" />

            <div className="grid gap-6 lg:grid-cols-3">
                {cards.map((card) => (
                    <section
                        key={card.label}
                        className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <p className="text-sm font-medium text-slate-500">{card.label}</p>
                        <p className="mt-4 text-4xl font-semibold tracking-tight text-slate-950">
                            {card.value}
                        </p>
                    </section>
                ))}
            </div>

            <section className="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <form
                        onSubmit={submitSearch}
                        className="flex flex-col gap-3 sm:flex-row"
                    >
                        <div className="w-full sm:w-80">
                            <label className="text-sm font-medium text-slate-700">
                                Search Roles
                            </label>
                            <TextInput
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Role name"
                                className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3"
                            />
                        </div>
                        <SecondaryButton
                            type="submit"
                            className="h-fit rounded-2xl px-5 py-3"
                        >
                            Filter
                        </SecondaryButton>
                    </form>

                    <Link
                        href={route('admin.roles.create')}
                        className="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                    >
                        Create Role
                    </Link>
                </div>

                <div className="mt-8 overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200">
                        <thead>
                            <tr className="text-left text-xs uppercase tracking-[0.2em] text-slate-400">
                                <th className="pb-4">Role</th>
                                <th className="pb-4">Permissions</th>
                                <th className="pb-4">Users</th>
                                <th className="pb-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {roles.data.map((role) => (
                                <tr key={role.id}>
                                    <td className="py-4">
                                        <div className="flex items-center gap-3">
                                            <p className="font-semibold text-slate-900">
                                                {role.name}
                                            </p>
                                            {role.is_protected && (
                                                <span className="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold uppercase text-sky-700">
                                                    system
                                                </span>
                                            )}
                                        </div>
                                    </td>
                                    <td className="py-4">
                                        <div className="flex flex-wrap gap-2">
                                            {role.permissions.map((permission) => (
                                                <span
                                                    key={permission}
                                                    className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700"
                                                >
                                                    {permission}
                                                </span>
                                            ))}
                                        </div>
                                    </td>
                                    <td className="py-4 text-sm text-slate-600">
                                        {role.users_count}
                                    </td>
                                    <td className="py-4">
                                        <div className="flex justify-end gap-2">
                                            <Link
                                                href={route('admin.roles.edit', role.id)}
                                                className="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                            >
                                                Edit
                                            </Link>
                                            {!role.is_protected && (
                                                <DangerButton
                                                    type="button"
                                                    onClick={() =>
                                                        destroyRole(role)
                                                    }
                                                    className="rounded-xl px-4 py-2 text-sm normal-case tracking-normal"
                                                >
                                                    Delete
                                                </DangerButton>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {roles.data.length === 0 && (
                    <div className="mt-8 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-sm text-slate-500">
                        No roles matched your current filter.
                    </div>
                )}

                <Pagination links={roles.links} />
            </section>
        </AdminLayout>
    );
}
