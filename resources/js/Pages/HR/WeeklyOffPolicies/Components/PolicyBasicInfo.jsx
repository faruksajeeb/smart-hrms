export default function PolicyBasicInfo({
    data,
    setData,
    companies,
    errors,
}) {
    return (
        <div className="grid grid-cols-12 gap-6">

            {/* Company */}
            <div className="col-span-12 md:col-span-6">
                <label className="mb-2 block text-sm font-medium text-slate-700">
                    Company
                    <span className="ml-1 text-red-500">*</span>
                </label>

                <select
                    value={data.company_id}
                    onChange={(e) =>
                        setData("company_id", e.target.value)
                    }
                    className={`w-full rounded-xl border px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 ${
                        errors.company_id
                            ? "border-red-500"
                            : "border-slate-300"
                    }`}
                >
                    <option value="">
                        Select Company
                    </option>

                    {companies.map((company) => (
                        <option
                            key={company.id}
                            value={company.id}
                        >
                            {company.name}
                        </option>
                    ))}
                </select>

                {errors.company_id && (
                    <p className="mt-1 text-sm text-red-600">
                        {errors.company_id}
                    </p>
                )}
            </div>

            {/* Policy Name */}
            <div className="col-span-12 md:col-span-6">
                <label className="mb-2 block text-sm font-medium text-slate-700">
                    Policy Name
                    <span className="ml-1 text-red-500">*</span>
                </label>

                <input
                    type="text"
                    value={data.policy_name}
                    onChange={(e) =>
                        setData("policy_name", e.target.value)
                    }
                    placeholder="Corporate Friday"
                    className={`w-full rounded-xl border px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 ${
                        errors.policy_name
                            ? "border-red-500"
                            : "border-slate-300"
                    }`}
                />

                {errors.policy_name && (
                    <p className="mt-1 text-sm text-red-600">
                        {errors.policy_name}
                    </p>
                )}
            </div>

            {/* Policy Code */}
            <div className="col-span-12 md:col-span-6">
                <label className="mb-2 block text-sm font-medium text-slate-700">
                    Policy Code
                </label>

                <input
                    type="text"
                    value={data.policy_code}
                    onChange={(e) =>
                        setData(
                            "policy_code",
                            e.target.value.toUpperCase()
                        )
                    }
                    placeholder="CORP-FRI"
                    className={`w-full rounded-xl border px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 ${
                        errors.policy_code
                            ? "border-red-500"
                            : "border-slate-300"
                    }`}
                />

                {errors.policy_code && (
                    <p className="mt-1 text-sm text-red-600">
                        {errors.policy_code}
                    </p>
                )}
            </div>

            {/* Status */}
            <div className="col-span-12 md:col-span-6">

                <label className="mb-2 block text-sm font-medium text-slate-700">
                    Status
                </label>

                <div className="flex h-[50px] items-center rounded-xl border border-slate-300 px-4">

                    <input
                        id="status"
                        type="checkbox"
                        checked={data.status}
                        onChange={(e) =>
                            setData(
                                "status",
                                e.target.checked
                            )
                        }
                        className="h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    />

                    <label
                        htmlFor="status"
                        className="ml-3 text-sm font-medium text-slate-700"
                    >
                        Active
                    </label>

                </div>

            </div>

            {/* Description */}
            <div className="col-span-12">

                <label className="mb-2 block text-sm font-medium text-slate-700">
                    Description
                </label>

                <textarea
                    rows={4}
                    value={data.description}
                    onChange={(e) =>
                        setData(
                            "description",
                            e.target.value
                        )
                    }
                    placeholder="Optional description..."
                    className={`w-full rounded-xl border px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 ${
                        errors.description
                            ? "border-red-500"
                            : "border-slate-300"
                    }`}
                />

                {errors.description && (
                    <p className="mt-1 text-sm text-red-600">
                        {errors.description}
                    </p>
                )}

            </div>

        </div>
    );
}