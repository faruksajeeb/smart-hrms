import { Head, Link, useForm } from '@inertiajs/react';
import EmployeeLayout from '@/Layouts/EmployeeLayout';
import { useMemo } from 'react';

const STATUS_COLORS = {
    draft: 'bg-gray-100 text-gray-800 border-gray-200',
    submitted: 'bg-blue-100 text-blue-800 border-blue-200',
    pending: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    approved: 'bg-emerald-100 text-emerald-800 border-emerald-200',
    rejected: 'bg-red-100 text-red-800 border-red-200',
    cancelled: 'bg-red-100 text-red-800 border-red-200',
    withdrawn: 'bg-orange-100 text-orange-800 border-orange-200',
    expired: 'bg-gray-100 text-gray-800 border-gray-200',
};

const LEAVE_TYPE_COLORS = {
    AL: 'bg-sky-100 text-sky-800',
    CL: 'bg-emerald-100 text-emerald-800',
    SL: 'bg-rose-100 text-rose-800',
    EL: 'bg-amber-100 text-amber-800',
    default: 'bg-slate-100 text-slate-800',
};

function getLeaveTypeColor(code) {
    if (!code) return LEAVE_TYPE_COLORS.default;
    const upper = code.toUpperCase();
    return LEAVE_TYPE_COLORS[upper] || LEAVE_TYPE_COLORS.default;
}

function buildCalendarGrid(from, to) {
    const start = new Date(from + 'T00:00:00');
    const end = new Date(to + 'T00:00:00');
    const current = new Date(start.getFullYear(), start.getMonth(), 1);
    const months = [];

    while (current <= end) {
        const year = current.getFullYear();
        const month = current.getMonth();
        const monthStart = new Date(year, month, 1);
        const monthEnd = new Date(year, month + 1, 0);
        const daysInMonth = monthEnd.getDate();
        const startDayOfWeek = monthStart.getDay();

        const days = [];
        for (let i = 0; i < startDayOfWeek; i++) {
            days.push(null);
        }
        for (let d = 1; d <= daysInMonth; d++) {
            days.push(d);
        }

        months.push({
            label: current.toLocaleString('default', { month: 'long', year: 'numeric' }),
            days,
            year,
            month,
        });

        current.setMonth(month + 1);
    }

    return months;
}

export default function IndexComponent({ events = [], leaveTypes = [], filters = {}, dateRange = {}, currentMonth }) {
    const { data, set } = useForm({
        leave_type_id: filters.leave_type_id || '',
        status: filters.status || '',
        from: dateRange.from || '',
        to: dateRange.to || '',
        include_non_leave: filters.include_non_leave || false,
    });

    const months = useMemo(() => {
        const from = data.from || dateRange.from || new Date().toISOString().split('T')[0];
        const to = data.to || dateRange.to || new Date().toISOString().split('T')[0];
        return buildCalendarGrid(from, to);
    }, [data.from, data.to, dateRange.from, dateRange.to]);

    const eventsByDate = useMemo(() => {
        const map = {};
        events.forEach((evt) => {
            if (!map[evt.date]) map[evt.date] = [];
            map[evt.date].push(evt);
        });
        return map;
    }, [events]);

    const handleFilter = (key, value) => {
        set(key, value);
    };

    const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    return (
        <EmployeeLayout
            heading="My Leave Calendar"
            subheading="View your approved, pending, and upcoming leaves."
        >
            <Head title="My Leave Calendar" />
            <div className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <div>
                            <label className="block text-sm font-medium text-slate-700">From</label>
                            <input
                                type="date"
                                value={data.from}
                                onChange={(e) => handleFilter('from', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-slate-700">To</label>
                            <input
                                type="date"
                                value={data.to}
                                onChange={(e) => handleFilter('to', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-slate-700">Leave Type</label>
                            <select
                                value={data.leave_type_id}
                                onChange={(e) => handleFilter('leave_type_id', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">All Types</option>
                                {leaveTypes.map((type) => (
                                    <option key={type.id} value={type.id}>
                                        {type.leave_name}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-slate-700">Status</label>
                            <select
                                value={data.status}
                                onChange={(e) => handleFilter('status', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">All Statuses</option>
                                <option value="approved">Approved</option>
                                <option value="pending">Pending</option>
                                <option value="rejected">Rejected</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="withdrawn">Withdrawn</option>
                            </select>
                        </div>
                        <div className="flex items-end">
                            <label className="flex items-center gap-2 rounded-xl border border-slate-300 px-3 py-2">
                                <input
                                    type="checkbox"
                                    checked={data.include_non_leave}
                                    onChange={(e) => handleFilter('include_non_leave', e.target.checked)}
                                />
                                <span className="text-sm text-slate-700">Include holidays/weekly offs</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div className="space-y-6">
                    {months.map((month, mIdx) => (
                        <div key={mIdx} className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div className="border-b border-slate-200 px-6 py-3">
                                <h3 className="text-base font-semibold text-slate-900">{month.label}</h3>
                            </div>
                            <div className="overflow-x-auto">
                                <div className="min-w-[700px]">
                                    <div className="grid grid-cols-7 border-b border-slate-200 bg-slate-50">
                                        {weekDays.map((day) => (
                                            <div key={day} className="px-2 py-2 text-center text-xs font-semibold text-slate-500">
                                                {day}
                                            </div>
                                        ))}
                                    </div>
                                    <div className="grid grid-cols-7">
                                        {month.days.map((day, dIdx) => {
                                            const dateStr = day
                                                ? `${month.year}-${String(month.month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`
                                                : null;
                                            const dayEvents = dateStr ? (eventsByDate[dateStr] || []) : [];
                                            const isToday = dateStr === new Date().toISOString().split('T')[0];

                                            return (
                                                <div
                                                    key={dIdx}
                                                    className={`min-h-[80px] border-b border-r border-slate-100 p-1 ${day ? 'bg-white hover:bg-slate-50' : 'bg-slate-50/50'}`}
                                                >
                                                    {day && (
                                                        <>
                                                            <div className={`text-xs font-medium ${isToday ? 'rounded-full bg-sky-600 px-1.5 py-0.5 text-white' : 'text-slate-600'}`}>
                                                                {day}
                                                            </div>
                                                            <div className="mt-1 space-y-1">
                                                                {dayEvents.map((evt, eIdx) => (
                                                                    <div
                                                                        key={eIdx}
                                                                        className={`truncate rounded-md px-1.5 py-1 text-[10px] font-medium ${STATUS_COLORS[evt.status] || 'bg-gray-100 text-gray-800'} border`}
                                                                        title={`${evt.leave_type.name} - ${evt.application_no} - ${evt.status}`}
                                                                    >
                                                                        <span className={`inline-block rounded px-1 py-0.5 mr-1 ${getLeaveTypeColor(evt.leave_type.code)}`}>
                                                                            {evt.leave_type.code || 'LT'}
                                                                        </span>
                                                                        {evt.session && (
                                                                            <span className="text-[9px] text-slate-500">({evt.session})</span>
                                                                        )}
                                                                    </div>
                                                                ))}
                                                            </div>
                                                        </>
                                                    )}
                                                </div>
                                            );
                                        })}
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>

                {events.length === 0 && (
                    <div className="rounded-2xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
                        No leave records found for the selected period.
                    </div>
                )}
            </div>
        </EmployeeLayout>
    );
}