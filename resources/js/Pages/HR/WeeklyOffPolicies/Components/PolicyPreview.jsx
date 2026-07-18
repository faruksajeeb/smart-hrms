const WEEK_TYPE_LABELS = {
    every: "Every Week",
    odd: "Odd Week",
    even: "Even Week",
    specific: "Specific Week",
};

const OFF_TYPE_LABELS = {
    full_day: "Full Day",
    first_half: "First Half",
    second_half: "Second Half",
};

export default function PolicyPreview({ data }) {

    const enabledDays = data.days.filter(day => day.enabled);

    return (

        <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">

            {/* Header */}

            <div className="border-b border-slate-200 px-6 py-4">

                <h2 className="text-lg font-semibold text-slate-900">
                    Live Preview
                </h2>

                <p className="mt-1 text-sm text-slate-500">
                    Review your weekly off configuration.
                </p>

            </div>

            <div className="space-y-6 p-6">

                {/* Company */}

                <InfoRow
                    label="Company"
                    value={
                        data.company_name ||
                        "Not Selected"
                    }
                />

                {/* Policy */}

                <InfoRow
                    label="Policy"
                    value={
                        data.policy_name ||
                        "-"
                    }
                />

                {/* Code */}

                <InfoRow
                    label="Code"
                    value={
                        data.policy_code ||
                        "-"
                    }
                />

                {/* Status */}

                <div>

                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Status
                    </p>

                    <span
                        className={`mt-2 inline-flex rounded-full px-3 py-1 text-xs font-semibold ${
                            data.status
                                ? "bg-green-100 text-green-700"
                                : "bg-red-100 text-red-700"
                        }`}
                    >
                        {data.status
                            ? "Active"
                            : "Inactive"}
                    </span>

                </div>

                {/* Divider */}

                <hr />

                {/* Weekly Off */}

                <div>

                    <h3 className="mb-3 text-sm font-semibold text-slate-900">
                        Weekly Off Days
                    </h3>

                    {enabledDays.length === 0 && (

                        <div className="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-center text-sm text-slate-500">
                            No weekly off configured.
                        </div>

                    )}

                    <div className="space-y-3">

                        {enabledDays.map(day => (

                            <div
                                key={day.day_of_week}
                                className="rounded-xl border border-slate-200 bg-slate-50 p-4"
                            >

                                <div className="flex items-center justify-between">

                                    <h4 className="font-semibold text-slate-800">
                                        {day.label}
                                    </h4>

                                    <span className="rounded-full bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-700">
                                        {OFF_TYPE_LABELS[day.off_type]}
                                    </span>

                                </div>

                                <div className="mt-2 text-sm text-slate-600">

                                    {WEEK_TYPE_LABELS[day.week_type]}

                                    {day.week_type === "specific" &&
                                        day.week_number && (
                                            <>
                                                {" "}
                                                • Week {day.week_number}
                                            </>
                                        )}

                                </div>

                            </div>

                        ))}

                    </div>

                </div>

            </div>

        </div>

    );

}

function InfoRow({ label, value }) {

    return (

        <div>

            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {label}
            </p>

            <p className="mt-1 text-sm font-medium text-slate-800">
                {value}
            </p>

        </div>

    );

}