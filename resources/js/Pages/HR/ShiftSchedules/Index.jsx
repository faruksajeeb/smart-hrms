import { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import HRLayout from '@/Layouts/HRLayout';

function startOfWeek(dateStr) {
    const d = new Date(dateStr);
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1);
    return new Date(d.setDate(diff));
}

function formatDate(d) {
    return d.toISOString().slice(0, 10);
}

function buildWeekDays(startStr) {
    const start = new Date(startStr);
    return Array.from({ length: 7 }, (_, i) => {
        const d = new Date(start);
        d.setDate(start.getDate() + i);
        return d;
    });
}

export default function ScheduleIndex({ schedules, shifts, employees, range, canManage }) {
    const weekDays = buildWeekDays(range.start);
    const [showForm, setShowForm] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        user_id: '',
        shift_id: '',
        work_date: range.start,
        notes: '',
    });

    function submit(e) {
        e.preventDefault();
        post(route('hr.shift-schedules.store'), {
            onSuccess: () => {
                reset('shift_id', 'notes');
                setShowForm(false);
            },
        });
    }

    function changeWeek(offsetDays) {
        const start = formatDate(new Date(new Date(range.start).setDate(new Date(range.start).getDate() + offsetDays)));
        const end = formatDate(new Date(new Date(range.end).setDate(new Date(range.end).getDate() + offsetDays)));
        router.get(route('hr.shift-schedules.index'), { start, end }, { preserveState: true });
    }

    function removeShift(id) {
        if (confirm('Remove this shift from the schedule?')) {
            router.delete(route('hr.shift-schedules.destroy', id));
        }
    }

    const schedulesByDay = weekDays.map((day) => {
        const dayStr = formatDate(day);
        return {
            date: day,
            items: schedules.filter((s) => s.work_date.slice(0, 10) === dayStr),
        };
    });

    return (
        <HRLayout
                    heading="Shift Schedule Management"
                    subheading="Create and manage shift templates for your organization."
                >
                    <Head title="Shift Schedule Management" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-4 flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            <button
                                onClick={() => changeWeek(-7)}
                                className="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                            >
                                &larr; Prev week
                            </button>
                            <span className="px-2 text-sm text-gray-600">
                                {range.start} &ndash; {range.end}
                            </span>
                            <button
                                onClick={() => changeWeek(7)}
                                className="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                            >
                                Next week &rarr;
                            </button>
                        </div>

                        {canManage && (
                            <button
                                onClick={() => setShowForm((s) => !s)}
                                className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
                            >
                                Assign shift
                            </button>
                        )}
                    </div>

                    {showForm && canManage && (
                        <form onSubmit={submit} className="mb-6 grid grid-cols-1 gap-4 rounded-lg bg-white p-4 shadow sm:grid-cols-5">
                            <div>
                                <label className="block text-sm text-gray-600">Employee</label>
                                <select
                                    value={data.user_id}
                                    onChange={(e) => setData('user_id', e.target.value)}
                                    className="mt-1 w-full rounded-md border-gray-300 text-sm"
                                >
                                    <option value="">Select&hellip;</option>
                                    {employees.map((e) => (
                                        <option key={e.id} value={e.id}>
                                            {e.name}
                                        </option>
                                    ))}
                                </select>
                                {errors.user_id && <p className="mt-1 text-xs text-red-600">{errors.user_id}</p>}
                            </div>

                            <div>
                                <label className="block text-sm text-gray-600">Shift</label>
                                <select
                                    value={data.shift_id}
                                    onChange={(e) => setData('shift_id', e.target.value)}
                                    className="mt-1 w-full rounded-md border-gray-300 text-sm"
                                >
                                    <option value="">Select&hellip;</option>
                                    {shifts.map((s) => (
                                        <option key={s.id} value={s.id}>
                                            {s.name} ({s.start_time ? s.start_time.slice(0, 5) : 'Flexible'}&ndash;{s.end_time ? s.end_time.slice(0, 5) : ''})
                                        </option>
                                    ))}
                                </select>
                                {errors.shift_id && <p className="mt-1 text-xs text-red-600">{errors.shift_id}</p>}
                            </div>

                            <div>
                                <label className="block text-sm text-gray-600">Date</label>
                                <input
                                    type="date"
                                    value={data.work_date}
                                    onChange={(e) => setData('work_date', e.target.value)}
                                    className="mt-1 w-full rounded-md border-gray-300 text-sm"
                                />
                                {errors.work_date && <p className="mt-1 text-xs text-red-600">{errors.work_date}</p>}
                            </div>

                            <div className="sm:col-span-2">
                                <label className="block text-sm text-gray-600">Notes</label>
                                <input
                                    type="text"
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    className="mt-1 w-full rounded-md border-gray-300 text-sm"
                                />
                            </div>

                            <div className="sm:col-span-5">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
                                >
                                    Save assignment
                                </button>
                            </div>
                        </form>
                    )}

                    <div className="grid grid-cols-1 gap-3 sm:grid-cols-7">
                        {schedulesByDay.map(({ date, items }) => (
                            <div key={date.toString()} className="rounded-lg bg-white p-3 shadow">
                                <div className="mb-2 text-sm font-medium text-gray-700">
                                    {date.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' })}
                                </div>
                                <div className="space-y-2">
                                    {items.length === 0 && <p className="text-xs text-gray-400">No shifts</p>}
                                    {items.map((item) => (
                                        <div
                                            key={item.id}
                                            className="rounded-md border-l-4 p-2 text-xs"
                                            style={{ borderColor: item.shift.color || '#6366f1', backgroundColor: (item.shift.color || '#6366f1') + '15' }}
                                        >
                                            <div className="font-medium text-gray-800">{item.shift.name}</div>
                                            <div className="text-gray-500">
                                                {item.shift.start_time ? item.shift.start_time.slice(0, 5) : 'Flexible'}
                                                {item.shift.start_time && item.shift.end_time ? '&ndash;' : ''}
                                                {item.shift.end_time ? item.shift.end_time.slice(0, 5) : ''}
                                            </div>
                                            {canManage && <div className="text-gray-600">{item.user?.name}</div>}
                                            <div className="mt-1 text-gray-400 capitalize">{item.status}</div>
                                            {canManage && (
                                                <button
                                                    onClick={() => removeShift(item.id)}
                                                    className="mt-1 text-red-500 hover:underline"
                                                >
                                                    Remove
                                                </button>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </HRLayout>
    );
}