import GuestLayout from '@/Layouts/GuestLayout';
import { Head } from '@inertiajs/react';

const titles = {
    403: 'Access denied',
    404: 'Page not found',
    500: 'Server error',
    503: 'Service unavailable',
};

export default function Error({ status, message }) {
    return (
        <GuestLayout>
            <Head title={titles[status] ?? 'Error'} />

            <div>
                <p className="text-sm font-semibold uppercase tracking-[0.3em] text-rose-600">
                    Error {status}
                </p>
                <h2 className="mt-3 text-3xl font-semibold tracking-tight text-slate-950">
                    {titles[status] ?? 'Something went wrong'}
                </h2>
                <p className="mt-4 text-sm leading-7 text-slate-600">
                    {message}
                </p>
            </div>
        </GuestLayout>
    );
}
