import InputError from "@/Components/InputError";

export default function ShiftForm({
    data,
    setData,
    errors,
    processing,
    editing,
    onSubmit,
    onCancel,
}) {
    return (
        <form
            onSubmit={onSubmit}
            className="flex max-h-[85vh] flex-col bg-white"
        >
            {/* Header */}

            <div className="border-b border-slate-200 px-8 py-6">

                <h2 className="text-xl font-semibold text-slate-900">
                    {editing ? "Edit Shift" : "Create New Shift"}
                </h2>

                <p className="mt-2 text-sm text-slate-500">
                    Configure working hours, attendance rules and shift settings.
                </p>

            </div>

            <div className="flex-1 space-y-10 overflow-y-auto p-8">

                {/* ==========================================================
                    BASIC INFORMATION
                =========================================================== */}

                <section>

                    <h3 className="mb-6 text-lg font-semibold text-slate-900">
                        Basic Information
                    </h3>

                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">

                        {/* Shift Name */}

                        <div>

                            <label className="mb-2 block text-sm font-medium text-slate-700">
                                Shift Name
                            </label>

                            <input
                                type="text"
                                value={data.shift_name}
                                onChange={(e) =>
                                    setData("shift_name", e.target.value)
                                }
                                placeholder="General Shift"
                                className="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring-emerald-500"
                            />

                            <InputError
                                message={errors.shift_name}
                                className="mt-2"
                            />

                        </div>

                        {/* Shift Code */}

                        <div>

                            <label className="mb-2 block text-sm font-medium text-slate-700">
                                Shift Code
                            </label>

                            <input
                                type="text"
                                value={data.shift_code}
                                onChange={(e) =>
                                    setData(
                                        "shift_code",
                                        e.target.value.toUpperCase()
                                    )
                                }
                                placeholder="GEN"
                                className="w-full rounded-xl border border-slate-300 px-4 py-2.5 uppercase focus:border-emerald-500 focus:ring-emerald-500"
                            />

                            <InputError
                                message={errors.shift_code}
                                className="mt-2"
                            />

                        </div>

                        {/* Description */}

                        <div className="lg:col-span-2">

                            <label className="mb-2 block text-sm font-medium text-slate-700">
                                Description
                            </label>

                            <textarea
                                rows={4}
                                value={data.description}
                                onChange={(e) =>
                                    setData("description", e.target.value)
                                }
                                placeholder="General office working shift..."
                                className="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-emerald-500 focus:ring-emerald-500"
                            />

                            <InputError
                                message={errors.description}
                                className="mt-2"
                            />

                        </div>

                        {/* Shift Color */}

                        <div>

                            <label className="mb-2 block text-sm font-medium text-slate-700">
                                Shift Color
                            </label>

                            <div className="flex items-center gap-3">

                                <input
                                    type="color"
                                    value={data.color}
                                    onChange={(e) =>
                                        setData("color", e.target.value)
                                    }
                                    className="h-12 w-20 cursor-pointer rounded-lg border border-slate-300"
                                />

                                <input
                                    type="text"
                                    value={data.color}
                                    onChange={(e) =>
                                        setData("color", e.target.value)
                                    }
                                    className="flex-1 rounded-xl border border-slate-300 px-4 py-2.5"
                                />

                            </div>

                            <InputError
                                message={errors.color}
                                className="mt-2"
                            />

                        </div>

                    </div>

                </section>

                {/* ==========================================================
                    WORKING SCHEDULE
                    (Next Part)
                =========================================================== */}
                <section>

    <h3 className="mb-6 text-lg font-semibold text-slate-900">
        Working Schedule
    </h3>

    <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">

        {/* Start Time */}

        <div>

            <label className="mb-2 block text-sm font-medium text-slate-700">
                Start Time
            </label>

            <input
                type="time"
                value={data.start_time}
                onChange={(e) =>
                    setData("start_time", e.target.value)
                }
                className="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring-emerald-500"
            />

            <InputError
                message={errors.start_time}
                className="mt-2"
            />

        </div>

        {/* End Time */}

        <div>

            <label className="mb-2 block text-sm font-medium text-slate-700">
                End Time
            </label>

            <input
                type="time"
                value={data.end_time}
                onChange={(e) =>
                    setData("end_time", e.target.value)
                }
                className="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring-emerald-500"
            />

            <InputError
                message={errors.end_time}
                className="mt-2"
            />

        </div>

        {/* Break Start */}

        <div>

            <label className="mb-2 block text-sm font-medium text-slate-700">
                Break Start
            </label>

            <input
                type="time"
                value={data.break_start}
                onChange={(e) =>
                    setData("break_start", e.target.value)
                }
                className="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring-emerald-500"
            />

            <InputError
                message={errors.break_start}
                className="mt-2"
            />

        </div>

        {/* Break End */}

        <div>

            <label className="mb-2 block text-sm font-medium text-slate-700">
                Break End
            </label>

            <input
                type="time"
                value={data.break_end}
                onChange={(e) =>
                    setData("break_end", e.target.value)
                }
                className="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring-emerald-500"
            />

            <InputError
                message={errors.break_end}
                className="mt-2"
            />

        </div>

    </div>

    {/* Shift Summary */}

    <div className="mt-8 rounded-2xl border border-blue-100 bg-blue-50 p-5">

        <h4 className="font-semibold text-blue-900">
            Shift Preview
        </h4>

        <div className="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">

            <div>
                <p className="text-xs uppercase tracking-wide text-slate-500">
                    Shift
                </p>

                <p className="mt-1 font-semibold text-slate-900">
                    {data.shift_name || "-"}
                </p>
            </div>

            <div>
                <p className="text-xs uppercase tracking-wide text-slate-500">
                    Working Time
                </p>

                <p className="mt-1 font-semibold text-slate-900">
                    {data.start_time || "--:--"} - {data.end_time || "--:--"}
                </p>
            </div>

            <div>
                <p className="text-xs uppercase tracking-wide text-slate-500">
                    Break Time
                </p>

                <p className="mt-1 font-semibold text-slate-900">
                    {data.break_start || "--:--"} - {data.break_end || "--:--"}
                </p>
            </div>

            <div>
                <p className="text-xs uppercase tracking-wide text-slate-500">
                    Shift Type
                </p>

                <div className="mt-1 flex flex-wrap gap-2">

                    {data.is_flexible && (
                        <span className="rounded-full bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-700">
                            Flexible
                        </span>
                    )}

                    {data.is_night_shift && (
                        <span className="rounded-full bg-purple-100 px-2 py-1 text-xs font-medium text-purple-700">
                            Night Shift
                        </span>
                    )}

                    {!data.is_flexible && !data.is_night_shift && (
                        <span className="rounded-full bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
                            Regular
                        </span>
                    )}

                </div>
            </div>

        </div>

    </div>

</section>

{/* ==========================================================
    ATTENDANCE RULES
    (Next Part)
========================================================== */}
<section>

    <div className="mb-6">

        <h3 className="text-lg font-semibold text-slate-900">
            Attendance Rules
        </h3>

        <p className="mt-1 text-sm text-slate-500">
            Configure attendance calculation rules for this shift.
        </p>

    </div>

    <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-5">

        {/* Grace Time */}

        <div>

            <label className="mb-2 block text-sm font-medium text-slate-700">
                Grace Time (Minutes)
            </label>

            <input
                type="number"
                min="0"
                value={data.grace_time}
                onChange={(e) =>
                    setData("grace_time", Number(e.target.value))
                }
                className="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring-emerald-500"
            />

            <InputError
                message={errors.grace_time}
                className="mt-2"
            />

            <p className="mt-2 text-xs text-slate-500">
                Employees can arrive this many minutes late without being marked late.
            </p>

        </div>

        {/* Working Hours */}

        <div>

            <label className="mb-2 block text-sm font-medium text-slate-700">
                Working Hours
            </label>

            <input
                type="number"
                step="0.5"
                min="0"
                value={data.working_hours}
                onChange={(e) =>
                    setData("working_hours", Number(e.target.value))
                }
                className="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring-emerald-500"
            />

            <InputError
                message={errors.working_hours}
                className="mt-2"
            />

            <p className="mt-2 text-xs text-slate-500">
                Expected working hours for this shift.
            </p>

        </div>

        {/* Late After */}

        <div>

            <label className="mb-2 block text-sm font-medium text-slate-700">
                Late After (Minutes)
            </label>

            <input
                type="number"
                min="0"
                value={data.late_after}
                onChange={(e) =>
                    setData("late_after", Number(e.target.value))
                }
                className="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring-emerald-500"
            />

            <InputError
                message={errors.late_after}
                className="mt-2"
            />

            <p className="mt-2 text-xs text-slate-500">
                Employee will be marked late after this limit.
            </p>

        </div>

        {/* Half Day */}

        <div>

            <label className="mb-2 block text-sm font-medium text-slate-700">
                Half Day After (Minutes)
            </label>

            <input
                type="number"
                min="0"
                value={data.half_day_after}
                onChange={(e) =>
                    setData("half_day_after", Number(e.target.value))
                }
                className="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring-emerald-500"
            />

            <InputError
                message={errors.half_day_after}
                className="mt-2"
            />

            <p className="mt-2 text-xs text-slate-500">
                Employee becomes Half Day after this many late minutes.
            </p>

        </div>

        {/* Minimum Working Hours */}

        <div>

            <label className="mb-2 block text-sm font-medium text-slate-700">
                Minimum Work Hours
            </label>

            <input
                type="number"
                step="0.5"
                min="0"
                value={data.minimum_work_hours}
                onChange={(e) =>
                    setData(
                        "minimum_work_hours",
                        Number(e.target.value)
                    )
                }
                className="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:border-emerald-500 focus:ring-emerald-500"
            />

            <InputError
                message={errors.minimum_work_hours}
                className="mt-2"
            />

            <p className="mt-2 text-xs text-slate-500">
                Minimum hours required to count attendance as Present.
            </p>

        </div>

    </div>

    {/* Attendance Summary */}

    <div className="mt-8 rounded-2xl border border-amber-200 bg-amber-50 p-5">

        <h4 className="font-semibold text-amber-900">
            Attendance Summary
        </h4>

        <div className="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-5">

            <div>
                <p className="text-xs uppercase tracking-wide text-slate-500">
                    Grace
                </p>

                <p className="mt-1 font-semibold text-slate-900">
                    {data.grace_time} Minutes
                </p>
            </div>

            <div>
                <p className="text-xs uppercase tracking-wide text-slate-500">
                    Work Hours
                </p>

                <p className="mt-1 font-semibold text-slate-900">
                    {data.working_hours} Hours
                </p>
            </div>

            <div>
                <p className="text-xs uppercase tracking-wide text-slate-500">
                    Late After
                </p>

                <p className="mt-1 font-semibold text-slate-900">
                    {data.late_after} Minutes
                </p>
            </div>

            <div>
                <p className="text-xs uppercase tracking-wide text-slate-500">
                    Half Day
                </p>

                <p className="mt-1 font-semibold text-slate-900">
                    {data.half_day_after} Minutes
                </p>
            </div>

            <div>
                <p className="text-xs uppercase tracking-wide text-slate-500">
                    Minimum Hours
                </p>

                <p className="mt-1 font-semibold text-slate-900">
                    {data.minimum_work_hours} Hours
                </p>
            </div>

        </div>

    </div>

</section>

{/* ==========================================================
    SHIFT SETTINGS
    (Next Part)
========================================================== */}
<section>

    <div className="mb-6">

        <h3 className="text-lg font-semibold text-slate-900">
            Shift Settings
        </h3>

        <p className="mt-1 text-sm text-slate-500">
            Configure additional behavior for this shift.
        </p>

    </div>

    <div className="grid gap-6 md:grid-cols-3">

        {/* Flexible Shift */}

        <div className="rounded-2xl border border-slate-200 p-5">

            <div className="flex items-center justify-between">

                <div>

                    <h4 className="font-semibold text-slate-900">
                        Flexible Shift
                    </h4>

                    <p className="mt-1 text-sm text-slate-500">
                        Employees are not bound to fixed start and end times.
                    </p>

                </div>

                <label className="relative inline-flex cursor-pointer items-center">
                    <input
                        type="checkbox"
                        checked={data.is_flexible}
                        onChange={(e) =>
                            setData("is_flexible", e.target.checked)
                        }
                        className="peer sr-only"
                    />

                    <div className="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-emerald-600"></div>

                    <div className="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-5"></div>
                </label>

            </div>

            <InputError
                message={errors.is_flexible}
                className="mt-3"
            />

        </div>

        {/* Night Shift */}

        <div className="rounded-2xl border border-slate-200 p-5">

            <div className="flex items-center justify-between">

                <div>

                    <h4 className="font-semibold text-slate-900">
                        Night Shift
                    </h4>

                    <p className="mt-1 text-sm text-slate-500">
                        Enable if the shift spans overnight.
                    </p>

                </div>

                <label className="relative inline-flex cursor-pointer items-center">
                    <input
                        type="checkbox"
                        checked={data.is_night_shift}
                        onChange={(e) =>
                            setData("is_night_shift", e.target.checked)
                        }
                        className="peer sr-only"
                    />

                    <div className="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-indigo-600"></div>

                    <div className="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-5"></div>
                </label>

            </div>

            <InputError
                message={errors.is_night_shift}
                className="mt-3"
            />

        </div>

        {/* Status */}

        <div className="rounded-2xl border border-slate-200 p-5">

            <div className="flex items-center justify-between">

                <div>

                    <h4 className="font-semibold text-slate-900">
                        Active Status
                    </h4>

                    <p className="mt-1 text-sm text-slate-500">
                        Only active shifts can be assigned to employees.
                    </p>

                </div>

                <label className="relative inline-flex cursor-pointer items-center">
                    <input
                        type="checkbox"
                        checked={data.status}
                        onChange={(e) =>
                            setData("status", e.target.checked)
                        }
                        className="peer sr-only"
                    />

                    <div className="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-green-600"></div>

                    <div className="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-5"></div>
                </label>

            </div>

            <InputError
                message={errors.status}
                className="mt-3"
            />

        </div>

    </div>

</section>

</div>

{/* ==========================================================
    FOOTER
========================================================== */}

<div className="flex items-center justify-between border-t border-slate-200 px-8 py-6">

    <div className="text-sm text-slate-500">
        Fields marked in this form are used for attendance, roster, and payroll calculations.
    </div>

    <div className="flex items-center gap-3">

        <button
            type="button"
            onClick={onCancel}
            className="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
        >
            Cancel
        </button>

        <button
            type="submit"
            disabled={processing}
            className="rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
        >
            {processing
                ? "Saving..."
                : editing
                ? "Update Shift"
                : "Create Shift"}
        </button>

    </div>

</div>

</form>
);
}