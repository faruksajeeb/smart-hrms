import { Head, Link } from "@inertiajs/react";

import HRLayout from "@/Layouts/HRLayout";
import Timeline from "@/Components/Timeline/Timeline";

export default function History({ employee, history }) {
    return (
        <HRLayout
            heading="Weekly Off Assignment History"
            subheading="Complete assignment timeline"
        >
            <Head title="Weekly Off History" />

            {/* Employee Card */}

            <div className="mb-6 rounded-xl border bg-white p-6 shadow-sm">
                <h3 className="text-lg font-semibold">Employee Information</h3>

                <div className="mt-4 grid grid-cols-2 gap-6">
                    <div>
                        <label className="text-sm text-gray-500">
                            Employee ID
                        </label>

                        <p className="font-semibold">{employee.employee_id}</p>
                    </div>

                    <div>
                        <label className="text-sm text-gray-500">
                            Employee Name
                        </label>

                        <p className="font-semibold">{employee.name}</p>
                    </div>
                </div>
            </div>

            {/* Timeline */}

            <div className="rounded-xl border bg-white shadow-sm">
                <div className="border-b px-6 py-4">
                    <h3 className="font-semibold">Assignment Timeline</h3>
                </div>

                <Timeline
                    items={history}
                    title={(item) => item.weekly_off_policy.policy_name}
                    subtitle={(item) => item.assignment_type}
                    actor={(item) => item.creator?.name}
                    startDate={(item) => item.effective_from}
                    endDate={(item) => item.effective_to}
                    remarks={(item) => item.remarks}
                />
            </div>

            <div className="mt-6 flex justify-end gap-3">
                <Link
                    href={route(
                        "hr.weekly-off-assignments.change",
                        history.find((x) => x.effective_to === null)?.id,
                    )}
                    className="rounded-lg bg-indigo-600 px-5 py-2 text-white hover:bg-indigo-700"
                >
                    Change Weekly Off
                </Link>

                <Link
                    href={route("hr.weekly-off-assignments.index")}
                    className="rounded-lg border px-5 py-2 hover:bg-gray-100"
                >
                    Back
                </Link>
            </div>
        </HRLayout>
    );
}
