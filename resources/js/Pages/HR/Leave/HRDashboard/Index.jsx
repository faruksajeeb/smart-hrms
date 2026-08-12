import { Head, Link } from "@inertiajs/react";
import HRLayout from "@/Layouts/HRLayout";
import { Bar, Doughnut, Line } from "react-chartjs-2";
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
} from "chart.js";

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
);

const STATUS_COLORS = {
    approved: "bg-emerald-100 text-emerald-800",
    pending: "bg-yellow-100 text-yellow-800",
    rejected: "bg-red-100 text-red-800",
    cancelled: "bg-red-100 text-red-800",
    withdrawn: "bg-orange-100 text-orange-800",
};

export default function IndexComponent({
    kpis = {},
    leaveTypeDistribution = [],
    monthlyTrend = [],
    departmentAnalysis = [],
    branchAnalysis = [],
    currentlyOnLeave = [],
    upcomingLeaves = [],
    pendingReport = [],
    month,
}) {
    const monthlyTrendData = {
        labels: monthlyTrend.map((item) => item.label),
        datasets: [
            {
                label: "Approved Leave Days",
                data: monthlyTrend.map((item) => item.value),
                borderColor: "#2563EB",
                backgroundColor: "rgba(37,99,235,0.12)",
                tension: 0.35,
                pointRadius: 3,
            },
        ],
    };

    const leaveTypeData = {
        labels: leaveTypeDistribution.map((item) => item.leave_name),
        datasets: [
            {
                data: leaveTypeDistribution.map((item) => item.total_days),
                backgroundColor: [
                    "#2563EB",
                    "#10B981",
                    "#EF4444",
                    "#F59E0B",
                    "#8B5CF6",
                    "#EC4899",
                    "#06B6D4",
                    "#F97316",
                ],
                hoverOffset: 6,
            },
        ],
    };

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: "top" },
            tooltip: { mode: "index", intersect: false },
        },
    };

    const agingBuckets = [
        { label: "0-2 days", count: 0 },
        { label: "3-5 days", count: 0 },
        { label: "6-10 days", count: 0 },
        { label: "10+ days", count: 0 },
    ];

    if (pendingReport.data) {
        pendingReport.data.forEach((item) => {
            const submitted = new Date(item.submitted_at);
            const now = new Date();
            const days = Math.floor((now - submitted) / (1000 * 60 * 60 * 24));
            if (days <= 2) agingBuckets[0].count++;
            else if (days <= 5) agingBuckets[1].count++;
            else if (days <= 10) agingBuckets[2].count++;
            else agingBuckets[3].count++;
        });
    }

    return (
        <HRLayout
            heading="HR Leave Dashboard"
            subheading="Enterprise leave analytics and reporting."
        >
            <Head title="HR Leave Dashboard" />
            <div className="space-y-6">
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p className="text-sm font-medium text-slate-500">
                            Total Employees
                        </p>
                        <p className="mt-2 text-3xl font-semibold text-slate-900">
                            {kpis.total_employees}
                        </p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p className="text-sm font-medium text-slate-500">
                            On Leave Today
                        </p>
                        <p className="mt-2 text-3xl font-semibold text-slate-900">
                            {kpis.on_leave_today}
                        </p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p className="text-sm font-medium text-slate-500">
                            Approved Leaves
                        </p>
                        <p className="mt-2 text-3xl font-semibold text-emerald-700">
                            {kpis.approved_leaves}
                        </p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p className="text-sm font-medium text-slate-500">
                            Pending Leaves
                        </p>
                        <p className="mt-2 text-3xl font-semibold text-amber-700">
                            {kpis.pending_leaves}
                        </p>
                    </div>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p className="text-sm font-medium text-slate-500">
                            Rejected Leaves
                        </p>
                        <p className="mt-2 text-3xl font-semibold text-red-700">
                            {kpis.rejected_leaves}
                        </p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p className="text-sm font-medium text-slate-500">
                            Cancelled Leaves
                        </p>
                        <p className="mt-2 text-3xl font-semibold text-slate-700">
                            {kpis.cancelled_leaves}
                        </p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p className="text-sm font-medium text-slate-500">
                            Total Leave Days
                        </p>
                        <p className="mt-2 text-3xl font-semibold text-slate-900">
                            {kpis.total_leave_days}
                        </p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p className="text-sm font-medium text-slate-500">
                            Applications This Month
                        </p>
                        <p className="mt-2 text-3xl font-semibold text-slate-900">
                            {kpis.applications_this_month}
                        </p>
                    </div>
                </div>

                <div className="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
                    <section className="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                        <h3 className="mb-4 text-base font-semibold text-slate-900">
                            Monthly Leave Trend
                        </h3>
                        <div className="h-[340px] w-full">
                            <Line
                                data={monthlyTrendData}
                                options={chartOptions}
                            />
                        </div>
                    </section>

                    <section className="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                        <h3 className="mb-4 text-base font-semibold text-slate-900">
                            Leave Type Distribution
                        </h3>
                        <div className="h-[340px] w-full">
                            <Doughnut
                                data={leaveTypeData}
                                options={chartOptions}
                            />
                        </div>
                    </section>
                </div>

                {departmentAnalysis.length > 0 && (
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div className="border-b border-slate-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-slate-900">
                                Department-wise Analysis
                            </h3>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-slate-50 text-slate-600">
                                    <tr>
                                        <th className="px-6 py-3 font-medium">
                                            Department
                                        </th>
                                        <th className="px-6 py-3 font-medium">
                                            Employees
                                        </th>
                                        <th className="px-6 py-3 font-medium">
                                            Approved Applications
                                        </th>
                                        <th className="px-6 py-3 font-medium">
                                            Approved Days
                                        </th>
                                        <th className="px-6 py-3 font-medium">
                                            Pending Applications
                                        </th>
                                        <th className="px-6 py-3 font-medium">
                                            On Leave Today
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {departmentAnalysis.map((dept) => (
                                        <tr
                                            key={dept.id}
                                            className="hover:bg-slate-50"
                                        >
                                            <td className="px-6 py-4 font-medium text-slate-900">
                                                {dept.department_name}
                                            </td>
                                            <td className="px-6 py-4 text-slate-600">
                                                {dept.total_employees}
                                            </td>
                                            <td className="px-6 py-4 text-slate-600">
                                                {dept.approved_applications}
                                            </td>
                                            <td className="px-6 py-4 text-slate-900">
                                                {dept.approved_days}
                                            </td>
                                            <td className="px-6 py-4">
                                                <span className="inline-flex rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-800">
                                                    {dept.pending_applications}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-slate-600">
                                                {dept.on_leave_today}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                {branchAnalysis.length > 0 && (
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div className="border-b border-slate-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-slate-900">
                                Branch-wise Analysis
                            </h3>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-slate-50 text-slate-600">
                                    <tr>
                                        <th className="px-6 py-3 font-medium">
                                            Branch
                                        </th>
                                        <th className="px-6 py-3 font-medium">
                                            Employees
                                        </th>
                                        <th className="px-6 py-3 font-medium">
                                            Approved Days
                                        </th>
                                        <th className="px-6 py-3 font-medium">
                                            Pending Applications
                                        </th>
                                        <th className="px-6 py-3 font-medium">
                                            On Leave Today
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {branchAnalysis.map((branch) => (
                                        <tr
                                            key={branch.id}
                                            className="hover:bg-slate-50"
                                        >
                                            <td className="px-6 py-4 font-medium text-slate-900">
                                                {branch.branch_name}
                                            </td>
                                            <td className="px-6 py-4 text-slate-600">
                                                {branch.total_employees}
                                            </td>
                                            <td className="px-6 py-4 text-slate-900">
                                                {branch.approved_days}
                                            </td>
                                            <td className="px-6 py-4">
                                                <span className="inline-flex rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-800">
                                                    {
                                                        branch.pending_applications
                                                    }
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-slate-600">
                                                {branch.on_leave_today}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                <div className="grid gap-6 lg:grid-cols-2">
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div className="border-b border-slate-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-slate-900">
                                Currently on Leave
                            </h3>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-slate-50 text-slate-600">
                                    <tr>
                                        <th className="px-6 py-3 font-medium">
                                            Employee
                                        </th>
                                        <th className="px-6 py-3 font-medium">
                                            Leave Type
                                        </th>
                                        <th className="px-6 py-3 font-medium">
                                            Date
                                        </th>
                                        <th className="px-6 py-3 font-medium">
                                            Days
                                        </th>
                                        <th className="px-6 py-3 font-medium">
                                            Session
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {currentlyOnLeave
                                        .slice(0, 20)
                                        .map((item, idx) => (
                                            <tr
                                                key={idx}
                                                className="hover:bg-slate-50"
                                            >
                                                <td className="px-6 py-4">
                                                    <div>
                                                        <p className="font-medium text-slate-900">
                                                            {item.employee_name}
                                                        </p>
                                                        <p className="text-xs text-slate-500">
                                                            {item.employee_code}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 text-slate-600">
                                                    {item.leave_type}
                                                </td>
                                                <td className="px-6 py-4 text-slate-600">
                                                    {item.date}
                                                </td>
                                                <td className="px-6 py-4 text-slate-900">
                                                    {item.leave_days}
                                                </td>
                                                <td className="px-6 py-4 text-slate-600">
                                                    {item.session || "Full Day"}
                                                </td>
                                            </tr>
                                        ))}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div className="border-b border-slate-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-slate-900">
                                Upcoming Leaves
                            </h3>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-slate-50 text-slate-600">
                                    <tr>
                                        <th className="px-6 py-3 font-medium">
                                            Employee
                                        </th>
                                        <th className="px-6 py-3 font-medium">
                                            Leave Type
                                        </th>
                                        <th className="px-6 py-3 font-medium">
                                            From
                                        </th>
                                        <th className="px-6 py-3 font-medium">
                                            To
                                        </th>
                                        <th className="px-6 py-3 font-medium">
                                            Days
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {upcomingLeaves
                                        .slice(0, 20)
                                        .map((leave, idx) => (
                                            <tr
                                                key={idx}
                                                className="hover:bg-slate-50"
                                            >
                                                <td className="px-6 py-4">
                                                    <div>
                                                        <p className="font-medium text-slate-900">
                                                            {
                                                                leave.employee_name
                                                            }
                                                        </p>
                                                        <p className="text-xs text-slate-500">
                                                            {
                                                                leave.employee_code
                                                            }
                                                        </p>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 text-slate-600">
                                                    {leave.leave_type}
                                                </td>
                                                <td className="px-6 py-4 text-slate-600">
                                                    {leave.start_date}
                                                </td>
                                                <td className="px-6 py-4 text-slate-600">
                                                    {leave.end_date}
                                                </td>
                                                <td className="px-6 py-4 text-slate-900">
                                                    {leave.total_days}
                                                </td>
                                            </tr>
                                        ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Pending Leave Report
                        </h3>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-slate-50 text-slate-600">
                                <tr>
                                    <th className="px-6 py-3 font-medium">
                                        Application
                                    </th>
                                    <th className="px-6 py-3 font-medium">
                                        Employee
                                    </th>
                                    <th className="px-6 py-3 font-medium">
                                        Leave Type
                                    </th>
                                    <th className="px-6 py-3 font-medium">
                                        Days
                                    </th>
                                    <th className="px-6 py-3 font-medium">
                                        Submitted
                                    </th>
                                    <th className="px-6 py-3 font-medium">
                                        Pending Since
                                    </th>
                                    <th className="px-6 py-3 font-medium">
                                        Current Approver
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {pendingReport.data &&
                                    pendingReport.data.map((item, idx) => (
                                        <tr
                                            key={idx}
                                            className="hover:bg-slate-50"
                                        >
                                            <td className="px-6 py-4 font-medium text-slate-900">
                                                {item.reference
                                                    ?.application_no ||
                                                    `#${item.id}`}
                                            </td>
                                            <td className="px-6 py-4">
                                                <div>
                                                    <p className="font-medium text-slate-900">
                                                        {item.requester?.name ||
                                                            "Unknown"}
                                                    </p>
                                                    <p className="text-xs text-slate-500">
                                                        {item.requester
                                                            ?.employee_id || ""}
                                                    </p>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 text-slate-600">
                                                {item.reference?.leave_type
                                                    ?.leave_name || "-"}
                                            </td>
                                            <td className="px-6 py-4 text-slate-900">
                                                {item.reference?.total_days ||
                                                    "-"}
                                            </td>
                                            <td className="px-6 py-4 text-slate-600">
                                                {item.submitted_at
                                                    ? new Date(
                                                          item.submitted_at,
                                                      ).toLocaleDateString()
                                                    : "-"}
                                            </td>
                                            <td className="px-6 py-4">
                                                {item.submitted_at ? (
                                                    <span
                                                        className={`inline-flex rounded-full px-2 py-1 text-xs font-medium ${
                                                            agingBuckets[0]
                                                                .count > 0
                                                                ? "bg-emerald-100 text-emerald-800"
                                                                : agingBuckets[1]
                                                                        .count >
                                                                    0
                                                                  ? "bg-amber-100 text-amber-800"
                                                                  : "bg-red-100 text-red-800"
                                                        }`}
                                                    >
                                                        {Math.floor(
                                                            (new Date() -
                                                                new Date(
                                                                    item.submitted_at,
                                                                )) /
                                                                (1000 *
                                                                    60 *
                                                                    60 *
                                                                    24),
                                                        )}{" "}
                                                        days
                                                    </span>
                                                ) : (
                                                    "-"
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-slate-600">
                                                {item.steps?.find(
                                                    (step) =>
                                                        step.level_no ===
                                                            item.current_level &&
                                                        step.status ===
                                                            "pending",
                                                )?.approver?.name || "-"}
                                            </td>
                                        </tr>
                                    ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </HRLayout>
    );
}
