import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    return (
        <div className="relative min-h-screen overflow-hidden bg-slate-950">
            <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.24),_transparent_32%),radial-gradient(circle_at_bottom_right,_rgba(14,165,233,0.18),_transparent_28%)]" />
            <div className="absolute inset-y-0 right-0 hidden w-1/2 bg-white/5 lg:block" />

            <div className="relative mx-auto flex min-h-screen max-w-7xl flex-col justify-center px-4 py-10 sm:px-6 lg:grid lg:grid-cols-[1.05fr_0.95fr] lg:gap-12 lg:px-8">
                <div className="mb-10 hidden text-white lg:block">
                    <Link href="/" className="inline-flex items-center gap-3">
                        <span className="rounded-2xl bg-white/10 p-3 backdrop-blur">
                            <ApplicationLogo className="h-10 w-10 fill-current text-white" />
                        </span>
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-300">
                                Smart HR
                            </p>
                            <p className="text-sm text-slate-300">
                                Secure people operations for growing teams
                            </p>
                        </div>
                    </Link>

                    <div className="mt-16 max-w-xl">
                        <p className="text-sm font-medium uppercase tracking-[0.32em] text-emerald-300">
                            Workforce access
                        </p>
                        <h1 className="mt-5 text-5xl font-semibold leading-tight">
                            Calm, role-aware access for every layer of your HR platform.
                        </h1>
                        <p className="mt-6 text-lg leading-8 text-slate-300">
                            Breeze keeps authentication trustworthy. Your authorization
                            model keeps employee data, payroll workflows, and reports in
                            the right hands.
                        </p>
                    </div>
                </div>

                <div className="w-full lg:justify-self-end">
                    <div className="mx-auto w-full max-w-md overflow-hidden rounded-[28px] border border-white/10 bg-white shadow-2xl shadow-slate-950/25">
                        <div className="border-b border-slate-100 bg-slate-950 px-8 py-7 text-white lg:hidden">
                            <Link href="/" className="inline-flex items-center gap-3">
                                <span className="rounded-2xl bg-white/10 p-3">
                                    <ApplicationLogo className="h-9 w-9 fill-current text-white" />
                                </span>
                                <div>
                                    <p className="text-xs font-semibold uppercase tracking-[0.32em] text-emerald-300">
                                        Smart HR
                                    </p>
                                    <p className="text-sm text-slate-300">
                                        Secure workforce access
                                    </p>
                                </div>
                            </Link>
                        </div>

                        <div className="px-8 py-8 sm:px-10 sm:py-10">
                            {children}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
