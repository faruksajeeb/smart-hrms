import DangerButton from '@/Components/DangerButton';
import Pagination from '@/Components/Pagination';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ permissions, filters, stats }) {
    const [search, setSearch] = useState(filters.search ?? '');

    const submitSearch = (e) => {
        e.preventDefault();

        router.get(
            route('admin.permissions.index'),
            { search },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const destroyPermission = (permission) => {
        if (
            !window.confirm(`Delete permission "${permission.name}"?`)
        ) {
            return;
        }

        router.delete(route('admin.permissions.destroy', permission.id), {
            preserveScroll: true,
        });
    };

    const cards = [
        { label: 'Total Permissions', value: stats.total },
        { label: 'System Permissions', value: stats.system },
        { label: 'Custom Permissions', value: stats.custom },
    ];

    return (
        <AdminLayout
            heading="Permission Management"
            subheading="Control the atomic capabilities used by roles, middleware, and permission-aware UI."
        >
            <Head title="Permission Management" />

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
                                Search Permissions
                            </label>
                            <TextInput
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Permission name"
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
                        href={route('admin.permissions.create')}
                        className="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                    >
                        Create Permission
                    </Link>
                </div>

                <div className="mt-8 overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200">
                        <thead>
                            <tr className="text-left text-xs uppercase tracking-[0.2em] text-slate-400">
                                <th className="pb-4">Group</th>
                                <th className="pb-4">Permission</th>
                                <th className="pb-4">Assigned Roles</th>
                                <th className="pb-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {permissions.data.map((permission) => (
                                <tr key={permission.id}>
                                    <td className="py-4 text-sm text-slate-600">
                                        {permission.group_name ?? 'General'}
                                    </td>
                                    <td className="py-4">
                                        <div className="flex items-center gap-3">
                                            <p className="font-semibold text-slate-900">
                                                {permission.name}
                                            </p>
                                            {permission.is_protected && (
                                                <span className="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold uppercase text-sky-700">
                                                    system
                                                </span>
                                            )}
                                        </div>
                                    </td>
                                    <td className="py-4 text-sm text-slate-600">
                                        {permission.roles_count}
                                    </td>
                                    <td className="py-4">
                                        <div className="flex justify-end gap-2">
                                            <Link
                                                href={route(
                                                    'admin.permissions.edit',
                                                    permission.id,
                                                )}
                                                className={`inline-flex items-center rounded-xl border px-4 py-2 text-sm font-medium transition ${
                                                    permission.is_protected
                                                        ? 'cursor-not-allowed border-slate-200 bg-slate-50 text-slate-400'
                                                        : 'border-slate-200 text-slate-700 hover:bg-slate-50'
                                                }`}
                                            >
                                                Edit
                                            </Link>
                                            {!permission.is_protected && (
                                                <DangerButton
                                                    type="button"
                                                    onClick={() =>
                                                        destroyPermission(
                                                            permission,
                                                        )
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

                {permissions.data.length === 0 && (
                    <div className="mt-8 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-sm text-slate-500">
                        No permissions matched your current filter.
                    </div>
                )}

                <Pagination links={permissions.links} />
            </section>
        </AdminLayout>
    );
}
