import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Log in" />

            <div>
                <p className="text-sm font-semibold uppercase tracking-[0.3em] text-emerald-600">
                    Sign in
                </p>
                <h2 className="mt-3 text-3xl font-semibold tracking-tight text-slate-950">
                    Access your Smart HR workspace
                </h2>
                <p className="mt-3 text-sm leading-6 text-slate-600">
                    Use your work email and password to continue to your
                    role-based dashboard.
                </p>
            </div>

            {status && (
                <div className="mt-6 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                    {status}
                </div>
            )}

            <form onSubmit={submit} className="mt-8 space-y-5">
                <div>
                    <InputLabel htmlFor="email" value="Work Email" />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500"
                        autoComplete="username"
                        isFocused={true}
                        onChange={(e) => setData('email', e.target.value)}
                    />

                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="password" value="Password" />

                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-emerald-500 focus:bg-white focus:ring-emerald-500"
                        autoComplete="current-password"
                        onChange={(e) => setData('password', e.target.value)}
                    />

                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="flex items-center justify-between gap-4">
                    <label className="flex items-center gap-3 text-sm text-slate-600">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) =>
                                setData('remember', e.target.checked)
                            }
                        />
                        <span>Remember me</span>
                    </label>

                    {canResetPassword && (
                        <Link
                            href={route('password.request')}
                            className="text-sm font-medium text-slate-600 transition hover:text-slate-900"
                        >
                            Forgot password?
                        </Link>
                    )}
                </div>

                <PrimaryButton
                    className="flex w-full items-center justify-center rounded-2xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 focus:bg-slate-800"
                    disabled={processing}
                >
                    {processing ? 'Signing in...' : 'Log in'}
                </PrimaryButton>
            </form>
        </GuestLayout>
    );
}
