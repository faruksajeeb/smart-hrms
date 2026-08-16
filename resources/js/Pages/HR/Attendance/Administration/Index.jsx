import { Head, Link } from "@inertiajs/react";
import HRLayout from "@/Layouts/HRLayout";
const labels = {
    audit: "Audit Trail",
    corrections: "Correction History",
    reconciliation: "Reconciliation",
    exceptions: "Exceptions",
    imports: "Raw Imports / Device Logs",
    archive: "Archives",
};
export default function Index({ tab = "audit", rows = { data: [] } }) {
    return (
        <HRLayout
            heading={`Attendance ${labels[tab] || "Administration"}`}
            subheading="Compliance and historical controls for attendance data."
        >
            <Head title={labels[tab] || "Attendance Administration"} />
            <div className="mb-5 flex flex-wrap gap-2">
                {Object.entries(labels).map(([key, label]) => (
                    <Link
                        className={`rounded-xl border px-3 py-2 text-sm ${tab === key ? "bg-slate-900 text-white" : ""}`}
                        href={route(`hr.attendance.administration.${key}`)}
                        key={key}
                    >
                        {label}
                    </Link>
                ))}
            </div>
            <div className="overflow-x-auto rounded-2xl border bg-white">
                <table className="w-full text-left text-sm">
                    <thead className="bg-slate-50">
                        <tr>
                            <th className="p-3">Date</th>
                            <th className="p-3">Type / Action</th>
                            <th className="p-3">Status</th>
                            <th className="p-3">Severity / Source</th>
                            <th className="p-3">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        {rows.data.length === 0 ? (
                            <tr>
                                <td
                                    className="p-8 text-center text-slate-500"
                                    colSpan="5"
                                >
                                    No administration records found.
                                </td>
                            </tr>
                        ) : (
                            rows.data.map((r) => (
                                <tr className="border-t" key={r.id}>
                                    <td className="p-3">
                                        {r.performed_at ||
                                            r.corrected_at ||
                                            r.exception_date ||
                                            r.attendance_date ||
                                            r.imported_at ||
                                            "—"}
                                    </td>
                                    <td className="p-3">
                                        {r.action ||
                                            r.exception_type ||
                                            r.issue_type ||
                                            r.correction_type ||
                                            r.processing_status ||
                                            "—"}
                                    </td>
                                    <td className="p-3">
                                        {r.status || r.processing_status || "—"}
                                    </td>
                                    <td className="p-3">
                                        {r.severity || r.source || "—"}
                                    </td>
                                    <td className="max-w-md p-3">
                                        {r.details ||
                                            r.reason ||
                                            r.error_message ||
                                            "—"}
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
        </HRLayout>
    );
}
