import { useForm } from "@inertiajs/react";

export default function Form({
    employees = [],
    policies = [],
    assignment = null,
    submitRoute,
    method = "post",
}) {
    const { data, setData, post, put, processing, errors } = useForm({
        employee_id: assignment?.employee_id ?? "",

        weekly_off_policy_id: assignment?.weekly_off_policy_id ?? "",

        effective_from: assignment?.effective_from ?? "",

        effective_to: assignment?.effective_to ?? "",

        assignment_type: assignment?.assignment_type ?? "manual",

        remarks: assignment?.remarks ?? "",
    });

    function submit(e) {
        e.preventDefault();

        if (method === "put") {
            put(submitRoute);
        } else {
            post(submitRoute);
        }
    }

    return (
        <form onSubmit={submit} className="space-y-6">
            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-200 px-6 py-4">
                    <h3 className="text-lg font-semibold text-slate-900">
                        Weekly Off Assignment
                    </h3>

                    <p className="mt-1 text-sm text-slate-500">
                        Select employee and assign a weekly off policy.
                    </p>
                </div>

                <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                    <div>
                        <label className="block text-sm font-medium text-slate-700">
                            Employee
                            <span className="ml-1 text-red-500">*</span>
                        </label>

                        <select
                            value={data.employee_id}
                            onChange={(e) =>
                                setData("employee_id", e.target.value)
                            }
                            className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        >
                            <option value="">Select Employee</option>

                            {employees.map((employee) => (
                                <option key={employee.id} value={employee.id}>
                                    {employee.employee_code}
                                    {" - "}
                                    {employee.name}
                                </option>
                            ))}
                        </select>

                        {errors.employee_id && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.employee_id}
                            </p>
                        )}
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-slate-700">
                            Weekly Off Policy
                            <span className="ml-1 text-red-500">*</span>
                        </label>

                        <select
                            value={data.weekly_off_policy_id}
                            onChange={(e) =>
                                setData("weekly_off_policy_id", e.target.value)
                            }
                            className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        >
                            <option value="">Select Weekly Off Policy</option>

                            {policies.map((policy) => (
                                <option key={policy.id} value={policy.id}>
                                    {policy.policy_code}
                                    {" - "}
                                    {policy.policy_name}
                                </option>
                            ))}
                        </select>

                        {errors.weekly_off_policy_id && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.weekly_off_policy_id}
                            </p>
                        )}
                    </div>
                    {/* Assignment Type */}

                    <div>
                        <label className="block text-sm font-medium text-slate-700">
                            Assignment Type
                            <span className="ml-1 text-red-500">*</span>
                        </label>

                        <select
                            value={data.assignment_type}
                            onChange={(e) =>
                                setData("assignment_type", e.target.value)
                            }
                            className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        >
                            <option value="initial">Initial Assignment</option>

                            <option value="manual">Manual Assignment</option>

                            <option value="transfer">Transfer</option>

                            <option value="promotion">Promotion</option>

                            <option value="temporary">Temporary</option>
                        </select>

                        {errors.assignment_type && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.assignment_type}
                            </p>
                        )}
                    </div>
                    {/* Effective From */}

                    <div>
                        <label className="block text-sm font-medium text-slate-700">
                            Effective From
                            <span className="ml-1 text-red-500">*</span>
                        </label>

                        <input
                            type="date"
                            value={data.effective_from}
                            onChange={(e) =>
                                setData("effective_from", e.target.value)
                            }
                            className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        />

                        {errors.effective_from && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.effective_from}
                            </p>
                        )}
                    </div>
                    {/* Effective To */}

                    <div>
                        <label className="block text-sm font-medium text-slate-700">
                            Effective To
                        </label>

                        <input
                            type="date"
                            value={data.effective_to}
                            onChange={(e) =>
                                setData("effective_to", e.target.value)
                            }
                            className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        />

                        <p className="mt-2 text-xs text-slate-500">
                            Leave blank if this assignment should remain active.
                        </p>

                        {errors.effective_to && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.effective_to}
                            </p>
                        )}
                    </div>
                </div>
            </div>
            {/* ==========================================
                Remarks
            ========================================== */}

            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-200 px-6 py-4">
                    <h3 className="text-lg font-semibold text-slate-900">
                        Remarks
                    </h3>
                </div>

                <div className="p-6">
                    <textarea
                        rows={4}
                        value={data.remarks}
                        onChange={(e) => setData("remarks", e.target.value)}
                        placeholder="Optional remarks..."
                        className="w-full rounded-xl border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                    />

                    {errors.remarks && (
                        <p className="mt-2 text-sm text-red-600">
                            {errors.remarks}
                        </p>
                    )}
                </div>
            </div>
            {/* ==========================================
                Policy Preview
            ========================================== */}

            <div className="rounded-2xl border border-emerald-200 bg-emerald-50 shadow-sm">
                <div className="border-b border-emerald-200 px-6 py-4">
                    <h3 className="text-lg font-semibold text-emerald-800">
                        Assignment Preview
                    </h3>
                </div>

                <div className="grid grid-cols-1 gap-6 p-6 md:grid-cols-2">
                    <div>
                        <label className="text-sm text-slate-500">
                            Selected Employee
                        </label>

                        <p className="mt-1 font-semibold text-slate-900">
                            {employees.find(
                                (employee) => employee.id == data.employee_id,
                            )?.name ?? "-"}
                        </p>
                    </div>

                    <div>
                        <label className="text-sm text-slate-500">
                            Weekly Off Policy
                        </label>

                        <p className="mt-1 font-semibold text-slate-900">
                            {policies.find(
                                (policy) =>
                                    policy.id == data.weekly_off_policy_id,
                            )?.policy_name ?? "-"}
                        </p>
                    </div>

                    <div>
                        <label className="text-sm text-slate-500">
                            Effective From
                        </label>

                        <p className="mt-1 font-semibold">
                            {data.effective_from || "-"}
                        </p>
                    </div>

                    <div>
                        <label className="text-sm text-slate-500">
                            Effective To
                        </label>

                        <p className="mt-1 font-semibold">
                            {data.effective_to || "Until Changed"}
                        </p>
                    </div>
                </div>
            </div>
            <div className="rounded-2xl border border-blue-200 bg-blue-50 p-5">
                <div className="flex gap-3">
                    <div className="text-xl">ℹ️</div>

                    <div>
                        <h4 className="font-semibold text-blue-900">
                            Enterprise Rules
                        </h4>

                        <ul className="mt-3 list-disc space-y-2 pl-5 text-sm text-blue-800">
                            <li>
                                Previous active assignment will be closed
                                automatically.
                            </li>

                            <li>Historical assignments cannot be deleted.</li>

                            <li>
                                Attendance reports will use the assignment
                                effective dates.
                            </li>

                            <li>
                                Payroll calculations also follow assignment
                                history.
                            </li>

                            <li>Future assignments are supported.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div className="flex items-center justify-end gap-3">
                <button
                    type="button"
                    onClick={() => history.back()}
                    className="rounded-xl border border-slate-300 px-5 py-2.5 font-medium text-slate-700 hover:bg-slate-100"
                >
                    Cancel
                </button>

                <button
                    type="submit"
                    disabled={processing}
                    className="rounded-xl bg-emerald-600 px-6 py-2.5 font-semibold text-white hover:bg-emerald-700 disabled:opacity-50"
                >
                    {processing
                        ? "Saving..."
                        : assignment
                          ? "Update Assignment"
                          : "Assign Weekly Off"}
                </button>
            </div>
        </form>
    );
}
