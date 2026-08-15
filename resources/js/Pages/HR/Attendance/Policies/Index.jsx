import { Head, useForm } from "@inertiajs/react";
import { useState } from "react";
import HRLayout from "@/Layouts/HRLayout";

const defaultRules = {
    expected_working_hours: 8,
    minimum_working_hours: 5,
    late_grace_minutes: 15,
    early_out_grace_minutes: 15,
    half_day_threshold_hours: 4,
    overtime_allowed: false,
    allow_cross_midnight: false,
};

export default function Index({ policies = { data: [] } }) {
    const form = useForm({
        policy_name: "",
        policy_code: "",
        description: "",
        effective_from: new Date().toISOString().slice(0, 10),
        effective_to: "",
        status: "active",
        rules: { ...defaultRules },
    });

    const [selectedPolicy, setSelectedPolicy] = useState(null);

    const rules =
        form.data.rules && typeof form.data.rules === "object"
            ? form.data.rules
            : defaultRules;

    const submit = (e) => {
        e.preventDefault();

        form.post(route("hr.attendance.policies.store"), {
            onSuccess: () => {
                form.reset();
            },
        });
    };

    const updateRule = (key, value) => {
        form.setData("rules", {
            ...form.data.rules,
            [key]: value,
        });
    };

    const formatRuleValue = (value) => {
        if (value === null || value === undefined || value === "") {
            return "Not set";
        }

        if (typeof value === "boolean") {
            return value ? "Yes" : "No";
        }

        if (typeof value === "object") {
            return JSON.stringify(value);
        }

        return String(value);
    };

    return (
        <HRLayout
            heading="Attendance Policies"
            subheading="Effective-dated attendance rules ready for future processing."
        >
            <Head title="Attendance Policies" />

            <div className="space-y-6">
                <form
                    onSubmit={submit}
                    className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <h2 className="font-semibold">Create Attendance Policy</h2>

                    <div className="mt-4 grid gap-4 md:grid-cols-3">
                        {[
                            ["policy_name", "Policy Name"],
                            ["policy_code", "Policy Code"],
                            ["effective_from", "Effective From"],
                            ["effective_to", "Effective To"],
                        ].map(([key, label]) => (
                            <label
                                key={key}
                                className="text-sm font-medium text-slate-700"
                            >
                                {label}

                                <input
                                    className="input mt-2"
                                    type={
                                        key.includes("effective")
                                            ? "date"
                                            : "text"
                                    }
                                    value={form.data[key] ?? ""}
                                    onChange={(e) =>
                                        form.setData(key, e.target.value)
                                    }
                                    required={key !== "effective_to"}
                                />
                            </label>
                        ))}

                        <label className="text-sm font-medium text-slate-700">
                            Status
                            <select
                                className="input mt-2"
                                value={form.data.status}
                                onChange={(e) =>
                                    form.setData("status", e.target.value)
                                }
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </label>
                    </div>

                    <label className="mt-4 block text-sm font-medium text-slate-700">
                        Description
                        <textarea
                            className="input mt-2"
                            value={form.data.description ?? ""}
                            onChange={(e) =>
                                form.setData("description", e.target.value)
                            }
                        />
                    </label>

                    <div className="mt-4 grid gap-4 md:grid-cols-3">
                        {Object.entries(rules).map(([key, value]) => (
                            <label
                                key={key}
                                className="text-sm font-medium text-slate-700"
                            >
                                {key.replaceAll("_", " ")}

                                <input
                                    className="input mt-2"
                                    type={
                                        typeof value === "boolean"
                                            ? "checkbox"
                                            : "number"
                                    }
                                    checked={
                                        typeof value === "boolean"
                                            ? Boolean(value)
                                            : undefined
                                    }
                                    value={
                                        typeof value === "boolean"
                                            ? undefined
                                            : (value ?? "")
                                    }
                                    onChange={(e) =>
                                        updateRule(
                                            key,
                                            typeof value === "boolean"
                                                ? e.target.checked
                                                : Number(e.target.value),
                                        )
                                    }
                                />
                            </label>
                        ))}
                    </div>

                    <button
                        type="submit"
                        disabled={form.processing}
                        className="mt-5 rounded-xl bg-slate-900 px-5 py-2 text-sm font-semibold text-white disabled:opacity-50"
                    >
                        {form.processing ? "Saving..." : "Save Policy"}
                    </button>
                </form>

                <div className="overflow-x-auto rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-slate-50">
                            <tr>
                                <th className="p-4">Policy</th>
                                <th className="p-4">Effective</th>
                                <th className="p-4">Status</th>
                                <th className="p-4">Rules</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {(policies?.data ?? []).map((p) => (
                                <tr key={p.id}>
                                    <td className="p-4">
                                        <button
                                            type="button"
                                            onClick={() => setSelectedPolicy(p)}
                                            className="font-semibold text-blue-600 hover:text-blue-800 hover:underline"
                                        >
                                            {p.policy_name}
                                        </button>

                                        <div className="text-xs text-gray-500">
                                            {p.policy_code}
                                        </div>
                                    </td>
                                    <td className="p-4">
                                        {p.effective_from} →{" "}
                                        {p.effective_to || "Open ended"}
                                    </td>
                                    <td className="p-4 capitalize">
                                        {p.status}
                                    </td>
                                    <td className="p-4">
                                        {p.rules ? "Configured" : "Missing"}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Compact Height & 3-Column Modal */}
            {selectedPolicy && (
                <div
                    className="fixed inset-0 m-2 z-[1050] flex items-center justify-center bg-slate-900/40 p-4 backdrop-blur-xs transition-opacity"
                    onClick={() => setSelectedPolicy(null)}
                >
                    <div
                        className="relative w-full max-w-3xl rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/5 transition-all"
                        onClick={(e) => e.stopPropagation()}
                    >
                        {/* Header */}
                        <div className="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                            <div>
                                <h3 className="text-base font-semibold text-slate-900 leading-tight">
                                    {selectedPolicy.policy_name}
                                </h3>
                                <p className="text-xs text-slate-500">
                                    Code: <span className="font-medium text-slate-700">{selectedPolicy.policy_code}</span>
                                </p>
                            </div>

                            <button
                                type="button"
                                onClick={() => setSelectedPolicy(null)}
                                className="flex h-7 w-7 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors"
                                aria-label="Close"
                            >
                                ✕
                            </button>
                        </div>

                        {/* Body - Compact max-height */}
                        <div className="max-h-[60vh] overflow-y-auto px-5 py-3 space-y-4">
                            {/* General Details */}
                            <div>
                                <h4 className="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                                    General Information
                                </h4>

                                <div className="grid grid-cols-4 gap-2">
                                    <div className="rounded-lg border border-slate-100 bg-slate-50/60 p-2">
                                        <span className="block text-[11px] font-medium text-slate-500">Status</span>
                                        <span
                                            className={`mt-0.5 inline-flex rounded-full px-2 py-0.25 text-[11px] font-semibold capitalize ${
                                                selectedPolicy.status === "active"
                                                    ? "bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20"
                                                    : "bg-slate-100 text-slate-600 ring-1 ring-inset ring-slate-500/10"
                                            }`}
                                        >
                                            {selectedPolicy.status}
                                        </span>
                                    </div>

                                    <div className="rounded-lg border border-slate-100 bg-slate-50/60 p-2">
                                        <span className="block text-[11px] font-medium text-slate-500">Effective From</span>
                                        <span className="mt-0.5 block text-xs font-semibold text-slate-800">
                                            {selectedPolicy.effective_from}
                                        </span>
                                    </div>

                                    <div className="rounded-lg border border-slate-100 bg-slate-50/60 p-2">
                                        <span className="block text-[11px] font-medium text-slate-500">Effective To</span>
                                        <span className="mt-0.5 block text-xs font-semibold text-slate-800">
                                            {selectedPolicy.effective_to || "Open ended"}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {/* Description (Only if exists) */}
                            {selectedPolicy.description && (
                                <div>
                                    <h4 className="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                                        Description
                                    </h4>
                                    <p className="rounded-lg border border-slate-100 bg-slate-50/60 p-2 text-xs leading-normal text-slate-600">
                                        {selectedPolicy.description}
                                    </p>
                                </div>
                            )}

                            {/* Rules Grid - 3 Columns */}
                            <div>
                                <h4 className="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">
                                    Configured Rules
                                </h4>

                                {selectedPolicy.rules &&
                                Object.keys(selectedPolicy.rules).length > 0 ? (
                                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                                        {Object.entries(selectedPolicy.rules).map(([key, value]) => (
                                            <div
                                                key={key}
                                                className="flex flex-col justify-center rounded-lg border border-slate-100 bg-white p-2 shadow-2xs"
                                            >
                                                <span className="text-[11px] font-medium capitalize text-slate-500 truncate">
                                                    {key.replaceAll("_", " ")}
                                                </span>
                                                <span className="text-xs font-semibold text-slate-900 mt-0.5">
                                                    {formatRuleValue(value)}
                                                </span>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="rounded-lg border border-dashed border-slate-200 p-2.5 text-center text-xs text-slate-400">
                                        No specific rules set for this policy.
                                    </p>
                                )}
                            </div>
                        </div>

                        {/* Compact Footer */}
                        <div className="flex items-center justify-end border-t border-slate-100 bg-slate-50/50 px-5 py-2.5 rounded-b-2xl">
                            <button
                                type="button"
                                onClick={() => setSelectedPolicy(null)}
                                className="rounded-lg bg-white px-3.5 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs border border-slate-200 hover:bg-slate-50 hover:text-slate-900 transition-all"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </HRLayout>
    );
}