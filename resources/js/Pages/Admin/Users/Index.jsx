import DangerButton from '@/Components/DangerButton';
import Pagination from '@/Components/Pagination';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ users, filters, stats }) {
    const [search, setSearch] = useState(filters.search ?? '');

    const submitSearch = (e) => {
        e.preventDefault();

        router.get(
            route('admin.users.index'),
            { search },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const destroyUser = (user) => {
        if (!window.confirm(`Delete ${user.name}? This action cannot be undone.`)) {
            return;
        }

        router.delete(route('admin.users.destroy', user.id), {
            preserveScroll: true,
        });
    };

    const cards = [
        { label: 'Total Users', value: stats.total },
        { label: 'Active Users', value: stats.active },
        { label: 'Inactive Users', value: stats.inactive },
    ];

    return (
        <AdminLayout
            heading="User Management"
            subheading="Manage workforce accounts, system roles, and account status from one place."
        >
            <Head title="User Management" />

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
                                Search Users
                            </label>
                            <TextInput
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Name, email, or employee ID"
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
                        href={route('admin.users.create')}
                        className="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                    >
                        Create User
                    </Link>
                </div>

                <div className="mt-8 overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200">
                        <thead>
                            <tr className="text-left text-xs uppercase tracking-[0.2em] text-slate-400">
                                <th className="pb-4">User</th>
                                <th className="pb-4">Roles</th>
                                <th className="pb-4">Status</th>
                                <th className="pb-4">Employee ID</th>
                                <th className="pb-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {users.data.map((user) => (
                                <tr key={user.id}>
                                    <td className="py-4">
                                        <p className="font-semibold text-slate-900">
                                            {user.name}
                                        </p>
                                        <p className="text-sm text-slate-500">
                                            {user.email}
                                        </p>
                                    </td>
                                    <td className="py-4">
                                        <div className="flex flex-wrap gap-2">
                                            {user.roles.map((role) => (
                                                <span
                                                    key={role}
                                                    className={`rounded-full px-3 py-1 text-xs font-semibold uppercase ${
                                                        role === user.primary_role
                                                            ? 'bg-slate-950 text-white'
                                                            : 'bg-slate-100 text-slate-700'
                                                    }`}
                                                >
                                                    {role}
                                                </span>
                                            ))}
                                        </div>
                                    </td>
                                    <td className="py-4">
                                        <span
                                            className={`rounded-full px-3 py-1 text-xs font-semibold uppercase ${
                                                user.status === 'active'
                                                    ? 'bg-emerald-100 text-emerald-700'
                                                    : 'bg-rose-100 text-rose-700'
                                            }`}
                                        >
                                            {user.status}
                                        </span>
                                    </td>
                                    <td className="py-4 text-sm text-slate-600">
                                        {user.employee_id ?? 'Not assigned'}
                                    </td>
                                    <td className="py-4">
                                        <div className="flex justify-end gap-2">
                                            <Link
                                                href={route('admin.users.edit', user.id)}
                                                className="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                            >
                                                Edit
                                            </Link>
                                            {user.can_delete && (
                                                <DangerButton
                                                    type="button"
                                                    onClick={() =>
                                                        destroyUser(user)
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

                {users.data.length === 0 && (
                    <div className="mt-8 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-sm text-slate-500">
                        No users matched your current filter.
                    </div>
                )}

                <Pagination links={users.links} />
            </section>
        </AdminLayout>
    );
}
