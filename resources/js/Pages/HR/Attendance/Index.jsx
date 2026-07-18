import { useEffect, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

function formatDuration(minutes) {
    if (minutes == null) return '—';
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    return `${h}h ${m}m`;
}

export default function AttendanceIndex({ openEntry, history, todaysSchedule }) {
    const [now, setNow] = useState(new Date());
    const [working, setWorking] = useState(false);

    useEffect(() => {
        const timer = setInterval(() => setNow(new Date()), 1000);
        return () => clearInterval(timer);
    }, []);

    function withLocation(callback) {
        if (!navigator.geolocation) return callback({});
        navigator.geolocation.getCurrentPosition(
            (pos) => callback({ latitude: pos.coords.latitude, longitude: pos.coords.longitude }),
            () => callback({}),
            { timeout: 3000 }
        );
    }

    function clockIn() {
        setWorking(true);
        withLocation((coords) => {
            router.post(
                route('attendance.clock-in'),
                { shift_schedule_id: todaysSchedule?.id, ...coords },
                { onFinish: () => setWorking(false) }
            );
        });
    }

    function clockOut() {
        setWorking(true);
        withLocation((coords) => {
            router.post(route('attendance.clock-out'), coords, { onFinish: () => setWorking(false) });
        });
    }

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Attendance</h2>}>
            <Head title="Attendance" />

            <div className="py-8">
                <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                    <div className="rounded-lg bg-white p-6 text-center shadow">
                        <p className="text-sm text-gray-500">{now.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric' })}</p>
                        <p className="mt-1 text-3xl font-semibold text-gray-800">{now.toLocaleTimeString()}</p>

                        {todaysSchedule && (
                            <p className="mt-2 text-sm text-gray-500">
                                Today&rsquo;s shift: {todaysSchedule.shift.name} (
                                {todaysSchedule.shift.start_time.slice(0, 5)}&ndash;{todaysSchedule.shift.end_time.slice(0, 5)})
                            </p>
                        )}

                        <div className="mt-6">
                            {openEntry ? (
                                <div>
                                    <p className="mb-3 text-sm text-gray-600">
                                        Clocked in at {new Date(openEntry.clock_in).toLocaleTimeString()}
                                    </p>
                                    <button
                                        onClick={clockOut}
                                        disabled={working}
                                        className="rounded-md bg-red-600 px-6 py-3 text-sm font-medium text-white hover:bg-red-500 disabled:opacity-50"
                                    >
                                        Clock out
                                    </button>
                                </div>
                            ) : (
                                <button
                                    onClick={clockIn}
                                    disabled={working}
                                    className="rounded-md bg-green-600 px-6 py-3 text-sm font-medium text-white hover:bg-green-500 disabled:opacity-50"
                                >
                                    Clock in
                                </button>
                            )}
                        </div>
                    </div>

                    <div className="mt-8 overflow-hidden rounded-lg bg-white shadow">
                        <table className="min-w-full divide-y divide-gray-200 text-sm">
                            <thead className="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                                <tr>
                                    <th className="px-4 py-3">Date</th>
                                    <th className="px-4 py-3">Clock in</th>
                                    <th className="px-4 py-3">Clock out</th>
                                    <th className="px-4 py-3">Total</th>
                                    <th className="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {history.map((a) => (
                                    <tr key={a.id}>
                                        <td className="px-4 py-3">{new Date(a.clock_in).toLocaleDateString()}</td>
                                        <td className="px-4 py-3">{new Date(a.clock_in).toLocaleTimeString()}</td>
                                        <td className="px-4 py-3">{a.clock_out ? new Date(a.clock_out).toLocaleTimeString() : '—'}</td>
                                        <td className="px-4 py-3">{formatDuration(a.total_minutes)}</td>
                                        <td className="px-4 py-3 capitalize">{a.status || '—'}</td>
                                    </tr>
                                ))}
                                {history.length === 0 && (
                                    <tr>
                                        <td colSpan={5} className="px-4 py-6 text-center text-gray-400">
                                            No attendance records yet.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}