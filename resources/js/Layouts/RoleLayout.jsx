import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import { Link, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';

const roleStyles = {
    admin: 'bg-sky-100 text-sky-700 ring-sky-200',
    hr: 'bg-emerald-100 text-emerald-700 ring-emerald-200',
    employee: 'bg-amber-100 text-amber-700 ring-amber-200',
};

export default function RoleLayout({
    role,
    heading,
    subheading,
    navigation,
    children,
}) {
    const { auth, authRoles = [], authPermissions = [], flash = {} } = usePage().props;
    const [sidebarOpen, setSidebarOpen] = useState(false);

    const items = useMemo(
        () =>
            navigation.filter((item) => {
                if (item.permission && !authPermissions.includes(item.permission)) {
                    return false;
                }

                if (item.roles && !item.roles.some((value) => authRoles.includes(value))) {
                    return false;
                }

                return true;
            }),
        [navigation, authPermissions, authRoles],
    );

    const badgeClass = roleStyles[role] ?? roleStyles.employee;

    return (
        <div className="min-h-screen bg-slate-100 text-slate-900">
            <div className="flex min-h-screen">
                <aside
                    className={`fixed inset-y-0 left-0 z-40 w-72 transform border-r border-slate-200 bg-slate-950 px-6 py-8 text-white transition duration-200 md:static md:translate-x-0 ${
                        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                    }`}
                >
                    <div className="flex items-center justify-between">
                        <Link href={route('dashboard')} className="flex items-center gap-3">
                            <span className="rounded-2xl bg-white/10 p-2.5">
                                <ApplicationLogo className="h-9 w-9 fill-current text-white" />
                            </span>
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">
                                    Smart HR
                                </p>
                                <p className="text-sm text-slate-200">
                                    Access Console
                                </p>
                            </div>
                        </Link>

                        <button
                            type="button"
                            onClick={() => setSidebarOpen(false)}
                            className="rounded-xl border border-white/10 px-3 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-300 md:hidden"
                        >
                            Close
                        </button>
                    </div>

                    <div className="mt-10 rounded-3xl border border-white/10 bg-white/5 p-5">
                        <p className="text-xs font-semibold uppercase tracking-[0.28em] text-slate-400">
                            Signed in as
                        </p>
                        <div className="mt-3 flex items-center justify-between gap-3">
                            <div>
                                <p className="font-semibold">{auth.user?.name}</p>
                                <p className="text-sm text-slate-400">{auth.user?.email}</p>
                            </div>
                            <span
                                className={`rounded-full px-3 py-1 text-xs font-semibold uppercase ring-1 ${badgeClass}`}
                            >
                                {role}
                            </span>
                        </div>
                    </div>

                    <nav className="mt-8 space-y-2">
                        {items.map((item) => {
                            const active = route().current(item.active ?? item.route);

                            return (
                                <Link
                                    key={item.route}
                                    href={route(item.route)}
                                    className={`flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium transition ${
                                        active
                                            ? 'bg-white text-slate-950 shadow-lg shadow-slate-950/10'
                                            : 'text-slate-300 hover:bg-white/10 hover:text-white'
                                    }`}
                                >
                                    <span>{item.label}</span>
                                    {item.permission && (
                                        <span className="rounded-full bg-slate-900/5 px-2 py-1 text-[10px] uppercase tracking-[0.2em]">
                                            secure
                                        </span>
                                    )}
                                </Link>
                            );
                        })}
                    </nav>
                </aside>

                <div className="flex min-w-0 flex-1 flex-col">
                    <header className="border-b border-slate-200 bg-white/90 px-4 py-4 backdrop-blur sm:px-6 lg:px-8">
                        <div className="flex items-center justify-between gap-4">
                            <div className="flex items-center gap-3">
                                <button
                                    type="button"
                                    onClick={() => setSidebarOpen(true)}
                                    className="rounded-2xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 md:hidden"
                                >
                                    Menu
                                </button>
                                <div>
                                    <h1 className="text-2xl font-semibold tracking-tight text-slate-950">
                                        {heading}
                                    </h1>
                                    <p className="text-sm text-slate-500">{subheading}</p>
                                </div>
                            </div>

                            <div className="flex items-center gap-3">
                                <div className="hidden text-right sm:block">
                                    <p className="text-sm font-semibold text-slate-900">
                                        {auth.user?.employee_id ?? 'No employee ID'}
                                    </p>
                                    <p className="text-xs uppercase tracking-[0.24em] text-slate-400">
                                        {auth.user?.status}
                                    </p>
                                </div>

                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <button
                                            type="button"
                                            className="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm"
                                        >
                                            <span className="flex h-9 w-9 items-center justify-center rounded-full bg-slate-950 text-xs font-semibold uppercase text-white">
                                                {auth.user?.name?.slice(0, 2)}
                                            </span>
                                            <span className="hidden sm:inline">Account</span>
                                        </button>
                                    </Dropdown.Trigger>

                                    <Dropdown.Content align="right" width="48">
                                        <Dropdown.Link href={route('profile.edit')}>
                                            Profile
                                        </Dropdown.Link>
                                        <Dropdown.Link href={route('logout')} method="post" as="button">
                                            Log Out
                                        </Dropdown.Link>
                                    </Dropdown.Content>
                                </Dropdown>
                            </div>
                        </div>
                    </header>

                    <main className="flex-1 px-4 py-8 sm:px-6 lg:px-8">
                        {flash.success && (
                            <div className="mb-6 rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700">
                                {flash.success}
                            </div>
                        )}

                        {flash.error && (
                            <div className="mb-6 rounded-2xl border border-rose-100 bg-rose-50 px-5 py-4 text-sm font-medium text-rose-700">
                                {flash.error}
                            </div>
                        )}

                        {children}
                    </main>
                </div>
            </div>
        </div>
    );
}
