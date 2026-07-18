import { useMemo, useState } from "react";

export default function ShiftTable({
    shifts,
    onEdit,
    onDelete,
    onToggleStatus,
}) {
    const [search, setSearch] = useState("");

    const filteredShifts = useMemo(() => {
        if (!search.trim()) {
            return shifts;
        }

        const keyword = search.toLowerCase();

        return shifts.filter((shift) => {
            return (
                shift.shift_name?.toLowerCase().includes(keyword) ||
                shift.shift_code?.toLowerCase().includes(keyword) ||
                shift.description?.toLowerCase().includes(keyword)
            );
        });
    }, [search, shifts]);

    return (
        <div className="rounded-3xl border border-slate-200 bg-white shadow-sm">
            {/* Header */}

            <div className="flex flex-col gap-4 border-b border-slate-200 p-6 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 className="text-lg font-semibold text-slate-900">
                        Shift Templates
                    </h2>

                    <p className="mt-1 text-sm text-slate-500">
                        {filteredShifts.length} shift(s) available.
                    </p>
                </div>

                {/* Search */}

                <div className="w-full md:w-80">
                    <input
                        type="text"
                        placeholder="Search shift..."
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring-emerald-500"
                    />
                </div>
            </div>

            <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-slate-200">
                    <thead className="bg-slate-50">
                        <tr>
                            <th className="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Shift
                            </th>

                            <th className="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Working Hours
                            </th>

                            <th className="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Rules
                            </th>

                            <th className="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Status
                            </th>

                            <th className="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody className="divide-y divide-slate-100 bg-white">
                        {filteredShifts.length > 0 ? (
                            filteredShifts.map((shift) => (
                                <tr
                                    key={shift.id}
                                    className="transition hover:bg-slate-50"
                                >
                                    {/* =====================================
                SHIFT INFORMATION
            ===================================== */}

                                    <td className="px-6 py-5">
                                        <div className="flex items-start gap-4">
                                            {/* Color */}

                                            <div
                                                className="mt-1 h-5 w-5 rounded-full border border-slate-200"
                                                style={{
                                                    backgroundColor:
                                                        shift.color ||
                                                        "#3b82f6",
                                                }}
                                            />

                                            <div>
                                                <div className="flex items-center gap-2">
                                                    <h4 className="font-semibold text-slate-900">
                                                        {shift.shift_name}
                                                    </h4>

                                                    <span className="rounded bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600">
                                                        {shift.shift_code}
                                                    </span>
                                                </div>

                                                {shift.description && (
                                                    <p className="mt-1 text-sm text-slate-500">
                                                        {shift.description}
                                                    </p>
                                                )}
                                            </div>
                                        </div>
                                    </td>

                                    {/* =====================================
                WORKING HOURS
            ===================================== */}

                                    <td className="px-6 py-5">
                                        <div className="space-y-2 text-sm">
                                            <div>
                                                <span className="font-medium text-slate-700">
                                                    Shift
                                                </span>

                                                <div className="text-slate-500">
                                                    {shift.start_time?.substring(
                                                        0,
                                                        5,
                                                    )}
                                                    {" - "}
                                                    {shift.end_time?.substring(
                                                        0,
                                                        5,
                                                    )}
                                                </div>
                                            </div>

                                            <div>
                                                <span className="font-medium text-slate-700">
                                                    Break
                                                </span>

                                                <div className="text-slate-500">
                                                    {shift.break_start?.substring(
                                                        0,
                                                        5,
                                                    )}
                                                    {" - "}
                                                    {shift.break_end?.substring(
                                                        0,
                                                        5,
                                                    )}
                                                </div>
                                            </div>

                                            <div className="text-xs text-slate-400">
                                                {shift.working_hours} hrs /
                                                Minimum{" "}
                                                {shift.minimum_work_hours} hrs
                                            </div>
                                        </div>
                                    </td>

                                    {/* =====================================
                RULES
            ===================================== */}

                                    <td className="px-6 py-5">
                                        <div className="flex flex-wrap gap-2">
                                            <span className="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">
                                                Grace {shift.grace_time}m
                                            </span>

                                            <span className="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700">
                                                Late {shift.late_after}m
                                            </span>

                                            <span className="rounded-full bg-rose-100 px-3 py-1 text-xs font-medium text-rose-700">
                                                Half {shift.half_day_after}m
                                            </span>

                                            {shift.is_flexible && (
                                                <span className="rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700">
                                                    Flexible
                                                </span>
                                            )}

                                            {shift.is_night_shift && (
                                                <span className="rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-700">
                                                    Night
                                                </span>
                                            )}
                                        </div>
                                    </td>

                                    {/* =====================================
                STATUS
            ===================================== */}

                                    <td className="px-6 py-5">
                                        <button
                                            onClick={() =>
                                                onToggleStatus(shift)
                                            }
                                            className={`rounded-full px-4 py-2 text-xs font-semibold transition ${
                                                shift.status
                                                    ? "bg-emerald-100 text-emerald-700 hover:bg-emerald-200"
                                                    : "bg-slate-200 text-slate-600 hover:bg-slate-300"
                                            }`}
                                        >
                                            {shift.status
                                                ? "Active"
                                                : "Inactive"}
                                        </button>
                                    </td>

                                    {/* =====================================
                ACTIONS
            ===================================== */}

                                    <td className="px-6 py-5 text-right">
                                        <div className="flex justify-end gap-2">
                                            <button
                                                onClick={() => onEdit(shift)}
                                                className="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-100"
                                            >
                                                Edit
                                            </button>

                                            <button
                                                onClick={() =>
                                                    onDelete(shift.id)
                                                }
                                                className="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-700 transition hover:bg-red-100"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td
                                    colSpan={5}
                                    className="px-6 py-16 text-center"
                                >
                                    <div className="mx-auto max-w-md">
                                        <div className="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-100">
                                            <svg
                                                className="h-10 w-10 text-slate-400"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth="1.8"
                                                    d="M9 17V7m6 10V7M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"
                                                />
                                            </svg>
                                        </div>

                                        <h3 className="mt-5 text-lg font-semibold text-slate-900">
                                            No Shift Found
                                        </h3>

                                        <p className="mt-2 text-sm text-slate-500">
                                            No shift templates match your
                                            current search.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            {/* Footer */}

            <div className="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-6 py-4">
                <div className="text-sm text-slate-500">
                    Showing
                    <span className="mx-1 font-semibold text-slate-700">
                        {filteredShifts.length}
                    </span>
                    of
                    <span className="mx-1 font-semibold text-slate-700">
                        {shifts.length}
                    </span>
                    shift(s).
                </div>

                <div className="text-sm text-slate-400">
                    Enterprise HRMS • Shift Management
                </div>
            </div>
        </div>
    );
}
