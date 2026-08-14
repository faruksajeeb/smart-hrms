import { Head, Link } from "@inertiajs/react";
import HRLayout from "@/Layouts/HRLayout";
export default function Show({ process }) {
    return (
        <HRLayout
            heading={`Year-End ${process.processing_year} → ${process.target_year}`}
            subheading="Auditable year-end processing details and ledger references."
        >
            <Head title="Year-End Process Details" />
            <div className="space-y-6">
                <div className="grid gap-4 sm:grid-cols-3">
                    <Card label="Status" value={process.status} />
                    <Card
                        label="Carry Forward"
                        value={process.total_carry_forward_days}
                    />
                    <Card
                        label="Expired / Encashed"
                        value={`${process.total_expired_days} / ${process.total_encashment_days}`}
                    />
                </div>
                <div className="flex gap-3">
                    <a
                        href={route("hr.leave.year-end.export", process.id)}
                        className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white"
                    >
                        Download CSV
                    </a>
                    <Link
                        href={route("hr.leave.year-end.index")}
                        className="rounded-xl border border-slate-300 px-4 py-2 text-sm text-slate-700"
                    >
                        Back
                    </Link>
                </div>
                <div className="overflow-x-auto rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-slate-50">
                            <tr>
                                {[
                                    "Employee",
                                    "Leave Type",
                                    "Previous",
                                    "Carry Forward",
                                    "Expired",
                                    "Encashed",
                                    "Final",
                                ].map((h) => (
                                    <th className="px-4 py-3" key={h}>
                                        {h}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {process.items.map((item) => (
                                <tr key={item.id}>
                                    <td className="px-4 py-3">
                                        {item.employee?.name}
                                        <div className="text-xs text-slate-500">
                                            {item.employee?.employee_id}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3">
                                        {item.leave_type?.leave_name}
                                    </td>
                                    <td className="px-4 py-3">
                                        {item.previous_balance}
                                    </td>
                                    <td className="px-4 py-3">
                                        {item.eligible_carry_forward}
                                    </td>
                                    <td className="px-4 py-3">
                                        {item.expired_days}
                                    </td>
                                    <td className="px-4 py-3">
                                        {item.encashment_days}
                                    </td>
                                    <td className="px-4 py-3 font-semibold">
                                        {item.closing_balance}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </HRLayout>
    );
}
function Card({ label, value }) {
    return (
        <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p className="text-sm text-slate-500">{label}</p>
            <p className="mt-2 text-2xl font-semibold capitalize">{value}</p>
        </div>
    );
}
