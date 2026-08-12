import { Head, router } from '@inertiajs/react';
import HRLayout from '@/Layouts/HRLayout';
import { useEffect, useMemo, useState } from 'react';
import SearchSelect from '@/Components/SearchSelect';
import SecondaryButton from '@/Components/SecondaryButton';
import Modal from '@/Components/Modal';

const WEEK_DAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const STATUS_COLORS = {
    approved: 'bg-emerald-100 text-emerald-800 border-emerald-200',
    pending: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    rejected: 'bg-red-100 text-red-800 border-red-200',
    cancelled: 'bg-red-100 text-red-800 border-red-200',
    withdrawn: 'bg-orange-100 text-orange-800 border-orange-200',
};

function buildCalendarGrid(year, month) {
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startDayOfWeek = firstDay.getDay();

    const days = [];
    for (let i = 0; i < startDayOfWeek; i++) {
        days.push(null);
    }
    for (let d = 1; d <= daysInMonth; d++) {
        days.push(d);
    }

    return {
        label: firstDay.toLocaleString('default', { month: 'long', year: 'numeric' }),
        days,
        year,
        month,
    };
}

export default function IndexComponent({
    events = [],
    summary = {},
    filters = {},
    year,
    month,
    options = {},
}) {
    const today = useMemo(() => {
        const now = new Date();
        return { year: now.getFullYear(), month: now.getMonth() + 1, day: now.getDate() };
    }, []);

    const [selectedEvent, setSelectedEvent] = useState(null);
    const [selectedDay, setSelectedDay] = useState(null);
    const [dayDetails, setDayDetails] = useState(null);
    const [loadingDay, setLoadingDay] = useState(false);

    const [company, setCompany] = useState(filters.company_id ?? '');
    const [branch, setBranch] = useState(filters.branch_id ?? '');
    const [cluster, setCluster] = useState(filters.cluster_id ?? '');
    const [division, setDivision] = useState(filters.division_id ?? '');
    const [department, setDepartment] = useState(filters.department_id ?? '');
    const [section, setSection] = useState(filters.section_id ?? '');
    const [unit, setUnit] = useState(filters.unit_id ?? '');
    const [designation, setDesignation] = useState(filters.designation_id ?? '');
    const [employmentType, setEmploymentType] = useState(filters.employment_type_id ?? '');
    const [leaveType, setLeaveType] = useState(filters.leave_type_id ?? '');
    const [status, setStatus] = useState(filters.status ?? 'approved');
    const [employeeId, setEmployeeId] = useState(filters.employee_id ?? '');
    const [includeNonLeave, setIncludeNonLeave] = useState(filters.include_non_leave ?? false);

    const grid = useMemo(() => buildCalendarGrid(year, month), [year, month]);

    const eventsByDate = useMemo(() => {
        const map = {};
        events.forEach((evt) => {
            if (!map[evt.date]) map[evt.date] = [];
            map[evt.date].push(evt);
        });
        return map;
    }, [events]);

    const hasFilters = [company, branch, cluster, division, department, section, unit, designation, employmentType, leaveType, status, employeeId, includeNonLeave].some(Boolean);

    const navigateToMonth = (newYear, newMonth) => {
        router.get(route('hr.leave.calendar.index'), {
            year: newYear,
            month: newMonth,
            company_id: company || undefined,
            branch_id: branch || undefined,
            cluster_id: cluster || undefined,
            division_id: division || undefined,
            department_id: department || undefined,
            section_id: section || undefined,
            unit_id: unit || undefined,
            designation_id: designation || undefined,
            employment_type_id: employmentType || undefined,
            leave_type_id: leaveType || undefined,
            status: status,
            employee_id: employeeId || undefined,
            include_non_leave: includeNonLeave ? 1 : undefined,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const applyFilters = () => {
        router.get(route('hr.leave.calendar.index'), {
            year,
            month,
            company_id: company || undefined,
            branch_id: branch || undefined,
            cluster_id: cluster || undefined,
            division_id: division || undefined,
            department_id: department || undefined,
            section_id: section || undefined,
            unit_id: unit || undefined,
            designation_id: designation || undefined,
            employment_type_id: employmentType || undefined,
            leave_type_id: leaveType || undefined,
            status: status,
            employee_id: employeeId || undefined,
            include_non_leave: includeNonLeave ? 1 : undefined,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const clearFilters = () => {
        setCompany('');
        setBranch('');
        setCluster('');
        setDivision('');
        setDepartment('');
        setSection('');
        setUnit('');
        setDesignation('');
        setEmploymentType('');
        setLeaveType('');
        setStatus('approved');
        setEmployeeId('');
        setIncludeNonLeave(false);

        router.get(route('hr.leave.calendar.index'), {
            year,
            month,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const openDayDetails = async (dateStr) => {
        setLoadingDay(true);
        setSelectedDay(dateStr);
        try {
            const response = await fetch(
                route('hr.leave.calendar.day-details') + '?' + new URLSearchParams({
                    year: new Date(dateStr + 'T00:00:00').getFullYear(),
                    month: new Date(dateStr + 'T00:00:00').getMonth() + 1,
                    day: new Date(dateStr + 'T00:00:00').getDate(),
                    company_id: company || '',
                    branch_id: branch || '',
                    cluster_id: cluster || '',
                    division_id: division || '',
                    department_id: department || '',
                    section_id: section || '',
                    unit_id: unit || '',
                    designation_id: designation || '',
                    employment_type_id: employmentType || '',
                    leave_type_id: leaveType || '',
                    status: status,
                    employee_id: employeeId || '',
                    include_non_leave: includeNonLeave ? '1' : '0',
                }).toString()
            );
            const data = await response.json();
            setDayDetails(data);
        } catch (e) {
            setDayDetails(null);
        } finally {
            setLoadingDay(false);
        }
    };

    const isToday = (day) => {
        if (!day) return false;
        const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        return dateStr === `${today.year}-${String(today.month).padStart(2, '0')}-${String(today.day).padStart(2, '0')}`;
    };

    const getLeaveColor = (evt) => {
        if (evt.leave_type?.display_color) {
            return evt.leave_type.display_color;
        }
        const code = evt.leave_type?.code?.toUpperCase();
        const colors = {
            AL: 'bg-sky-100 text-sky-800',
            CL: 'bg-emerald-100 text-emerald-800',
            SL: 'bg-rose-100 text-rose-800',
            EL: 'bg-amber-100 text-amber-800',
        };
        return colors[code] || 'bg-slate-100 text-slate-800';
    };

    const formatStatus = (s) => {
        if (!s) return '-';
        return s.charAt(0).toUpperCase() + s.slice(1);
    };

    return (
        <HRLayout
            heading="HR Leave Calendar"
            subheading="View organizational leave schedule and calendar."
        >
            <Head title="HR Leave Calendar" />

            <div className="space-y-6">
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p className="text-sm font-medium text-slate-500">Selected Month</p>
                        <p className="mt-2 text-xl font-semibold text-slate-900">{summary.selected_month}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p className="text-sm font-medium text-slate-500">Employees on Leave</p>
                        <p className="mt-2 text-3xl font-semibold text-slate-900">{summary.employees_on_leave}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p className="text-sm font-medium text-slate-500">Leave Days</p>
                        <p className="mt-2 text-3xl font-semibold text-slate-900">{summary.leave_days}</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p className="text-sm font-medium text-slate-500">Applications</p>
                        <p className="mt-2 text-3xl font-semibold text-slate-900">{summary.applications}</p>
                    </div>
                </div>

                <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-2">
                            <button
                                type="button"
                                onClick={() => {
                                    let m = month - 1;
                                    let y = year;
                                    if (m < 1) { m = 12; y--; }
                                    navigateToMonth(y, m);
                                }}
                                className="rounded-xl border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            >
                                &lt;
                            </button>
                            <h2 className="min-w-[200px] text-center text-lg font-semibold text-slate-900">
                                {grid.label}
                            </h2>
                            <button
                                type="button"
                                onClick={() => {
                                    let m = month + 1;
                                    let y = year;
                                    if (m > 12) { m = 1; y++; }
                                    navigateToMonth(y, m);
                                }}
                                className="rounded-xl border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            >
                                &gt;
                            </button>
                        </div>

                        <div className="flex items-center gap-2">
                            <select
                                value={month}
                                onChange={(e) => navigateToMonth(year, parseInt(e.target.value))}
                                className="rounded-xl border border-slate-300 px-3 py-2 text-sm"
                            >
                                {Array.from({ length: 12 }, (_, i) => (
                                    <option key={i + 1} value={i + 1}>
                                        {new Date(year, i).toLocaleString('default', { month: 'long' })}
                                    </option>
                                ))}
                            </select>
                            <select
                                value={year}
                                onChange={(e) => navigateToMonth(parseInt(e.target.value), month)}
                                className="rounded-xl border border-slate-300 px-3 py-2 text-sm"
                            >
                                {Array.from({ length: 11 }, (_, i) => {
                                    const y = today.year - 5 + i;
                                    return (
                                        <option key={y} value={y}>{y}</option>
                                    );
                                })}
                            </select>
                            <button
                                type="button"
                                onClick={() => navigateToMonth(today.year, today.month)}
                                className="rounded-xl border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            >
                                Today
                            </button>
                        </div>
                    </div>

                    <div className="mt-6 overflow-x-auto">
                        <div className="min-w-[900px]">
                            <div className="grid grid-cols-7 border-b border-slate-200 bg-slate-50">
                                {WEEK_DAYS.map((day) => (
                                    <div key={day} className="px-2 py-2 text-center text-xs font-semibold text-slate-500">
                                        {day}
                                    </div>
                                ))}
                            </div>
                            <div className="grid grid-cols-7">
                                {grid.days.map((day, dIdx) => {
                                    const dateStr = day
                                        ? `${grid.year}-${String(grid.month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`
                                        : null;
                                    const dayEvents = dateStr ? (eventsByDate[dateStr] || []) : [];
                                    const leaveEvents = dayEvents.filter(e => e.counts_as_leave);
                                    const nonLeaveEvents = dayEvents.filter(e => !e.counts_as_leave);
                                    const displayEvents = status === 'all' ? dayEvents : leaveEvents;
                                    const remaining = Math.max(0, displayEvents.length - 3);

                                    return (
                                        <div
                                            key={dIdx}
                                            className={`min-h-[100px] border-b border-r border-slate-100 p-1 ${day ? 'bg-white hover:bg-slate-50 cursor-pointer' : 'bg-slate-50/50'}`}
                                            onClick={() => day && openDayDetails(dateStr)}
                                        >
                                            {day && (
                                                <>
                                                    <div className={`text-xs font-medium ${isToday(day) ? 'rounded-full bg-sky-600 px-1.5 py-0.5 text-white inline-block' : 'text-slate-600'}`}>
                                                        {day}
                                                    </div>
                                                    <div className="mt-1 space-y-1">
                                                        {displayEvents.slice(0, 3).map((evt, eIdx) => (
                                                            <div
                                                                key={eIdx}
                                                                onClick={(e) => { e.stopPropagation(); setSelectedEvent(evt); }}
                                                                className={`truncate rounded-md px-1.5 py-1 text-[10px] font-medium border ${STATUS_COLORS[evt.status] || 'bg-gray-100 text-gray-800'}`}
                                                                style={evt.leave_type?.display_color ? { borderLeft: `3px solid ${evt.leave_type.display_color}` } : {}}
                                                                title={`${evt.employee?.name || evt.employee_name} - ${evt.leave_type?.name || evt.leave_type?.name} - ${formatStatus(evt.status)}`}
                                                            >
                                                                <span className="font-medium">{evt.employee?.name || evt.employee_name}</span>
                                                                <span className="text-slate-500"> - {evt.leave_type?.name || ''}</span>
                                                                {evt.session && <span className="text-[9px] text-slate-500"> ({evt.session})</span>}
                                                                {!evt.counts_as_leave && <span className="text-[9px] text-slate-400"> (Non-leave)</span>}
                                                            </div>
                                                        ))}
                                                        {remaining > 0 && (
                                                            <div className="text-[10px] text-slate-500 px-1.5 font-medium">
                                                                +{remaining} more
                                                            </div>
                                                        )}
                                                        {!leaveEvents.length && dayEvents.length > 0 && status !== 'all' && (
                                                            <div className="text-[10px] text-slate-400 px-1.5">
                                                                {dayEvents.length} non-leave
                                                            </div>
                                                        )}
                                                    </div>
                                                </>
                                            )}
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    </div>

                    {events.length === 0 && (
                        <div className="mt-6 rounded-2xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
                            No leave records found for {grid.label} with the selected filters.
                        </div>
                    )}
                </section>

                <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 className="text-lg font-semibold text-slate-900">Filters</h2>
                    <p className="mt-1 text-sm text-slate-500">
                        Filter by organizational scope, leave type, status, or employee.
                    </p>

                    <form onSubmit={(e) => { e.preventDefault(); applyFilters(); }} className="mt-6 space-y-4">
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <SearchSelect
                                id="company"
                                label="Company"
                                value={company}
                                onChange={(val) => { setCompany(val); setBranch(''); setCluster(''); setDivision(''); setDepartment(''); setSection(''); setUnit(''); }}
                                options={options.companies ?? []}
                                placeholder="All companies"
                                isClearable
                            />
                            <SearchSelect
                                id="branch"
                                label="Branch"
                                value={branch}
                                onChange={(val) => { setBranch(val); setCluster(''); setDivision(''); setDepartment(''); setSection(''); setUnit(''); }}
                                options={options.branches ?? []}
                                placeholder="All branches"
                                isClearable
                            />
                            <SearchSelect
                                id="cluster"
                                label="Cluster"
                                value={cluster}
                                onChange={(val) => { setCluster(val); setDivision(''); setDepartment(''); setSection(''); setUnit(''); }}
                                options={options.clusters ?? []}
                                placeholder="All clusters"
                                isClearable
                            />
                            <SearchSelect
                                id="division"
                                label="Division"
                                value={division}
                                onChange={(val) => { setDivision(val); setDepartment(''); setSection(''); setUnit(''); }}
                                options={options.divisions ?? []}
                                placeholder="All divisions"
                                isClearable
                            />
                            <SearchSelect
                                id="department"
                                label="Department"
                                value={department}
                                onChange={(val) => { setDepartment(val); setSection(''); setUnit(''); }}
                                options={options.departments ?? []}
                                placeholder="All departments"
                                isClearable
                            />
                            <SearchSelect
                                id="section"
                                label="Section"
                                value={section}
                                onChange={(val) => { setSection(val); setUnit(''); }}
                                options={options.sections ?? []}
                                placeholder="All sections"
                                isClearable
                            />
                            <SearchSelect
                                id="unit"
                                label="Unit"
                                value={unit}
                                onChange={setUnit}
                                options={options.units ?? []}
                                placeholder="All units"
                                isClearable
                            />
                            <SearchSelect
                                id="designation"
                                label="Designation"
                                value={designation}
                                onChange={setDesignation}
                                options={options.designations ?? []}
                                placeholder="All designations"
                                isClearable
                            />
                            <SearchSelect
                                id="employment_type"
                                label="Employment Type"
                                value={employmentType}
                                onChange={setEmploymentType}
                                options={options.employmentTypes ?? []}
                                placeholder="All employment types"
                                isClearable
                            />
                            <SearchSelect
                                id="leave_type"
                                label="Leave Type"
                                value={leaveType}
                                onChange={setLeaveType}
                                options={options.leaveTypes ?? []}
                                placeholder="All leave types"
                                isClearable
                            />
                            <div>
                                <label className="block text-sm font-medium text-slate-700">Status</label>
                                <select
                                    value={status}
                                    onChange={(e) => setStatus(e.target.value)}
                                    className="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2"
                                >
                                    {(options.statuses ?? []).map((s) => (
                                        <option key={s.value} value={s.value}>{s.label}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-slate-700">Employee</label>
                                <input
                                    type="text"
                                    value={employeeId}
                                    onChange={(e) => setEmployeeId(e.target.value)}
                                    placeholder="Search by employee ID or name"
                                    className="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2"
                                />
                            </div>
                            <div className="flex items-end">
                                <label className="flex items-center gap-2 rounded-xl border border-slate-300 px-3 py-2">
                                    <input
                                        type="checkbox"
                                        checked={includeNonLeave}
                                        onChange={(e) => setIncludeNonLeave(e.target.checked)}
                                    />
                                    <span className="text-sm text-slate-700">Include holidays/weekly offs</span>
                                </label>
                            </div>
                        </div>

                        <div className="flex items-center justify-end gap-3">
                            {hasFilters && (
                                <button
                                    type="button"
                                    onClick={clearFilters}
                                    className="text-sm font-medium text-slate-500 hover:text-slate-700"
                                >
                                    Clear Filters
                                </button>
                            )}
                            <SecondaryButton type="submit">
                                Apply Filters
                            </SecondaryButton>
                        </div>
                    </form>
                </section>
            </div>

            <Modal show={!!selectedEvent} onClose={() => setSelectedEvent(null)} maxWidth="2xl">
                {selectedEvent && (
                    <div className="p-6">
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-slate-900">Leave Event Details</h3>
                            <button
                                type="button"
                                onClick={() => setSelectedEvent(null)}
                                className="text-slate-400 hover:text-slate-600"
                            >
                                &times;
                            </button>
                        </div>
                        <div className="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <p className="text-sm font-medium text-slate-500">Employee</p>
                                <p className="text-sm text-slate-900">{selectedEvent.employee?.name || selectedEvent.employee_name}</p>
                                <p className="text-xs text-slate-500">{selectedEvent.employee?.employee_id || selectedEvent.employee_code}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-slate-500">Leave Type</p>
                                <p className="text-sm text-slate-900">{selectedEvent.leave_type?.name}</p>
                                <p className="text-xs text-slate-500">{selectedEvent.leave_type?.code}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-slate-500">Application No.</p>
                                <p className="text-sm text-slate-900">{selectedEvent.application_no}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-slate-500">Date</p>
                                <p className="text-sm text-slate-900">{selectedEvent.date}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-slate-500">Status</p>
                                <p className="text-sm text-slate-900">{formatStatus(selectedEvent.status)}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-slate-500">Leave Days</p>
                                <p className="text-sm text-slate-900">{selectedEvent.leave_days}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-slate-500">Day Type</p>
                                <p className="text-sm text-slate-900">{selectedEvent.day_type === 'half_day' ? 'Half Day' : 'Full Day'}</p>
                            </div>
                            {selectedEvent.session && (
                                <div>
                                    <p className="text-sm font-medium text-slate-500">Session</p>
                                    <p className="text-sm text-slate-900">{selectedEvent.session === 'morning' ? 'Morning' : selectedEvent.session === 'afternoon' ? 'Afternoon' : selectedEvent.session}</p>
                                </div>
                            )}
                            <div>
                                <p className="text-sm font-medium text-slate-500">Start Date</p>
                                <p className="text-sm text-slate-900">{selectedEvent.start_date}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-slate-500">End Date</p>
                                <p className="text-sm text-slate-900">{selectedEvent.end_date}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-slate-500">Submitted Date</p>
                                <p className="text-sm text-slate-900">{selectedEvent.submitted_at ? new Date(selectedEvent.submitted_at).toLocaleString() : '-'}</p>
                            </div>
                            <div>
                                <p className="text-sm font-medium text-slate-500">Approved Date</p>
                                <p className="text-sm text-slate-900">{selectedEvent.approved_at ? new Date(selectedEvent.approved_at).toLocaleString() : '-'}</p>
                            </div>
                            {selectedEvent.delegate_user_id && (
                                <div className="sm:col-span-2">
                                    <p className="text-sm font-medium text-slate-500">Acting Person</p>
                                    <p className="text-sm text-slate-900">User ID: {selectedEvent.delegate_user_id}</p>
                                </div>
                            )}
                            {selectedEvent.reason && (
                                <div className="sm:col-span-2">
                                    <p className="text-sm font-medium text-slate-500">Reason</p>
                                    <p className="text-sm text-slate-900">{selectedEvent.reason}</p>
                                </div>
                            )}
                        </div>
                    </div>
                )}
            </Modal>

            <Modal show={!!selectedDay} onClose={() => { setSelectedDay(null); setDayDetails(null); }} maxWidth="3xl">
                {selectedDay && (
                    <div className="p-6">
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-slate-900">
                                {dayDetails ? new Date(dayDetails.date + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) : selectedDay}
                            </h3>
                            <button
                                type="button"
                                onClick={() => { setSelectedDay(null); setDayDetails(null); }}
                                className="text-slate-400 hover:text-slate-600"
                            >
                                &times;
                            </button>
                        </div>

                        {loadingDay && (
                            <div className="mt-6 text-center text-sm text-slate-500">Loading...</div>
                        )}

                        {dayDetails && (
                            <>
                                <div className="mt-4 flex items-center gap-4 text-sm text-slate-600">
                                    <span>Employees on Leave: <strong>{dayDetails.total}</strong></span>
                                </div>

                                {dayDetails.employees.length === 0 ? (
                                    <div className="mt-6 text-center text-sm text-slate-500">
                                        No employees on leave for this date with the selected filters.
                                    </div>
                                ) : (
                                    <div className="mt-4 overflow-x-auto">
                                        <table className="w-full text-left text-sm">
                                            <thead className="bg-slate-50 text-slate-600">
                                                <tr>
                                                    <th className="px-4 py-3 font-medium">Employee</th>
                                                    <th className="px-4 py-3 font-medium">Leave Type</th>
                                                    <th className="px-4 py-3 font-medium">Days</th>
                                                    <th className="px-4 py-3 font-medium">Status</th>
                                                    <th className="px-4 py-3 font-medium">Session</th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-slate-100">
                                                {dayDetails.employees.map((emp, idx) => (
                                                    <tr key={idx} className="hover:bg-slate-50">
                                                        <td className="px-4 py-3">
                                                            <div>
                                                                <p className="font-medium text-slate-900">{emp.employee_name}</p>
                                                                <p className="text-xs text-slate-500">{emp.employee_code}</p>
                                                            </div>
                                                        </td>
                                                        <td className="px-4 py-3 text-slate-600">{emp.leave_type}</td>
                                                        <td className="px-4 py-3 text-slate-900">{emp.leave_days}</td>
                                                        <td className="px-4 py-3">
                                                            <span className={`inline-flex rounded-full px-2 py-1 text-xs font-medium ${STATUS_COLORS[emp.status] || 'bg-gray-100 text-gray-800'}`}>
                                                                {formatStatus(emp.status)}
                                                            </span>
                                                        </td>
                                                        <td className="px-4 py-3 text-slate-600">
                                                            {emp.day_type === 'half_day' ? `${emp.session === 'morning' ? 'Morning' : 'Afternoon'} (½ Day)` : 'Full Day'}
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                )}
                            </>
                        )}
                    </div>
                )}
            </Modal>
        </HRLayout>
    );
}
