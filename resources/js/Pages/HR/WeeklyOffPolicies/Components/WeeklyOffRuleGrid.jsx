const WEEK_TYPES = [
    { value: "every", label: "Every Week" },
    { value: "odd", label: "Odd Weeks" },
    { value: "even", label: "Even Weeks" },
    { value: "specific", label: "Specific Week" },
];

const OFF_TYPES = [
    { value: "full_day", label: "Full Day" },
    { value: "first_half", label: "First Half" },
    { value: "second_half", label: "Second Half" },
];

export default function WeeklyOffRuleGrid({ data, setData, errors }) {
    function updateDay(index, field, value) {
        const days = [...data.days];

        days[index] = {
            ...days[index],
            [field]: value,
        };

        setData("days", days);
    }

    return (
        <div className="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-2">
            {data.days.map((day, index) => (
                <div
                    key={day.day_of_week}
                    className={`rounded-2xl border transition-all duration-200 ${
                        day.enabled
                            ? "border-indigo-500 bg-indigo-50 shadow-md"
                            : "border-slate-200 bg-white"
                    }`}
                >
                    {/* Header */}

                    <div className="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                        <div>
                            <h3 className="text-lg font-semibold text-slate-900">
                                {day.label}
                            </h3>

                            <p className="text-sm text-slate-500">
                                Weekly Off Configuration
                            </p>
                        </div>

                        <label className="inline-flex cursor-pointer items-center">
                            <input
                                type="checkbox"
                                checked={day.enabled}
                                onChange={(e) =>
                                    updateDay(
                                        index,
                                        "enabled",
                                        e.target.checked,
                                    )
                                }
                                className="h-5 w-5 rounded border-slate-300 text-indigo-600"
                            />
                        </label>
                    </div>

                    {/* Configuration */}

                    {day.enabled && (
                        <div className="space-y-4 p-5">
                            <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                                {/* Week Type */}

                                <div>
                                    <label className="mb-2 block text-sm font-medium text-slate-700">
                                        Week Type
                                    </label>

                                    <select
                                        value={day.week_type}
                                        onChange={(e) => {
                                            updateDay(
                                                index,
                                                "week_type",
                                                e.target.value,
                                            );

                                            if (e.target.value !== "specific") {
                                                updateDay(
                                                    index,
                                                    "week_number",
                                                    null,
                                                );
                                            }
                                        }}
                                        className="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    >
                                        {WEEK_TYPES.map((type) => (
                                            <option
                                                key={type.value}
                                                value={type.value}
                                            >
                                                {type.label}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                {/* Off Type */}

                                <div>
                                    <label className="mb-2 block text-sm font-medium text-slate-700">
                                        Off Type
                                    </label>

                                    <select
                                        value={day.off_type}
                                        onChange={(e) =>
                                            updateDay(
                                                index,
                                                "off_type",
                                                e.target.value,
                                            )
                                        }
                                        className="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    >
                                        {OFF_TYPES.map((type) => (
                                            <option
                                                key={type.value}
                                                value={type.value}
                                            >
                                                {type.label}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                {/* Specific Week */}

                                {day.week_type === "specific" && (
                                    <div className="md:col-span-2">
                                        <label className="mb-2 block text-sm font-medium text-slate-700">
                                            Week Number
                                        </label>

                                        <select
                                            value={day.week_number ?? ""}
                                            onChange={(e) =>
                                                updateDay(
                                                    index,
                                                    "week_number",
                                                    Number(e.target.value),
                                                )
                                            }
                                            className="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        >
                                            <option value="">
                                                Select Week
                                            </option>

                                            <option value={1}>
                                                First Week
                                            </option>

                                            <option value={2}>
                                                Second Week
                                            </option>

                                            <option value={3}>
                                                Third Week
                                            </option>

                                            <option value={4}>
                                                Fourth Week
                                            </option>

                                            <option value={5}>
                                                Fifth Week
                                            </option>
                                        </select>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}
                </div>
            ))}
        </div>
    );
}
