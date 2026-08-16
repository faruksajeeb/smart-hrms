import RoleLayout from "@/Layouts/RoleLayout";

const navigation = [
    { label: "Dashboard", route: "hr.dashboard" },
    {
        label: "Employee Management",
        active: "hr.employees.*",
        permission: "manage employees",
        roles: ["hr"],
        children: [
            {
                label: "Employee List",
                route: "hr.employees.index",
                active: "hr.employees.*",
            },
            {
                label: "Reporting Manager Assignments",
                route: "hr.reporting-manager-assignments.index",
                active: "hr.reporting-manager-assignments.*",
            },
            {
                label: "Employment Movements",
                route: "hr.employment-movements.index",
                active: "hr.employment-movements.*",
            },
            {
                label: "Transfer Employees",
                route: "hr.transfers.index",
                active: "hr.transfers.*",
            },
        ],
    },
    {
        label: "Attendance",
        active: [
            "hr.attendance.*",
            "hr.shifts.*",
            "hr.shift-swap-requests.*",
            "hr.weekly-off-assignments.*",
            "hr.shift-assignments.*",
            "hr.bulk-assignments.*",
        ],
        permission: "manage attendance",
        roles: ["hr"],
        children: [
            {
                label: "Attendance Dashboard",
                route: "hr.attendance.dashboard.index",
                active: "hr.attendance.dashboard.*",
                permission: "attendance.dashboard.view",
            },
            {
                label: "Attendance Calendar",
                route: "hr.attendance.calendar.index",
                active: "hr.attendance.calendar.*",
                permission: "attendance.calendar.view_all",
            },
            {
                label: "Shifts",
                route: "hr.shifts.index",
                active: "hr.shifts.*",
            },
            {
                label: "Swap Requests",
                route: "hr.shift-swap-requests.index",
                active: "hr.shift-swap-requests.*",
            },
            {
                label: "Weekly Off Policies",
                route: "hr.weekly-off-policies.index",
                active: "hr.weekly-off-policies.*",
            },
            {
                label: "Weekly Off Assignments",
                route: "hr.weekly-off-assignments.index",
                active: "hr.weekly-off-assignments.*",
            },
            {
                label: "Shift Assignments",
                route: "hr.shift-assignments.index",
                active: "hr.shift-assignments.*",
            },
            {
                label: "Bulk Assignments",
                route: "hr.bulk-assignments.index",
                active: "hr.bulk-assignments.*",
            },
            {
                label: "Attendance Policies",
                route: "hr.attendance.policies.index",
                active: "hr.attendance.policies.*",
                permission: "attendance.policy.view",
            },
            {
                label: "Policy Assignments",
                route: "hr.attendance.policy-assignments.index",
                active: "hr.attendance.policy-assignments.*",
                permission: "attendance.policy_assignment.view",
            },
            {
                label: "Attendance Statuses",
                route: "hr.attendance.configuration.index",
                active: "hr.attendance.configuration.*",
                permission: "attendance.status.view",
            },
            {
                label: "Attendance Processing",
                route: "hr.attendance.processing.index",
                active: "hr.attendance.processing.*",
                permission: "attendance.view",
            },
            {
                label: "Regularization",
                route: "hr.attendance.regularizations.index",
                active: "hr.attendance.regularizations.*",
                permission: "attendance.regularization.view",
            },
            {
                label: "Attendance Approval",
                route: "hr.approval.requests.pending",
                active: "hr.approval.requests.*",
                permission: "attendance.approval.view",
            },
            {
                label: "Attendance Reports",
                active: "hr.attendance.reports.*",
                permission: "attendance.reports.view",
                roles: ["hr"],
                children: [
                    {
                        label: "Reports — Daily",
                        route: "hr.attendance.reports.index",
                        params: { type: "daily" },
                        active: "hr.attendance.reports.*",
                        permission: "attendance.reports.daily",
                    },
                    {
                        label: "Reports — Monthly",
                        route: "hr.attendance.reports.index",
                        params: { type: "monthly" },
                        active: "hr.attendance.reports.*",
                        permission: "attendance.reports.monthly",
                    },
                    {
                        label: "Reports — Employee",
                        route: "hr.attendance.reports.index",
                        params: { type: "employee" },
                        active: "hr.attendance.reports.*",
                        permission: "attendance.reports.employee",
                    },
                    {
                        label: "Reports — Department",
                        route: "hr.attendance.reports.index",
                        params: { type: "department" },
                        active: "hr.attendance.reports.*",
                        permission: "attendance.reports.department",
                    },
                    {
                        label: "Reports — Late & Early",
                        route: "hr.attendance.reports.index",
                        params: { type: "late-early" },
                        active: "hr.attendance.reports.*",
                        permission: "attendance.reports.late_early",
                    },
                    {
                        label: "Reports — Absenteeism",
                        route: "hr.attendance.reports.index",
                        params: { type: "absenteeism" },
                        active: "hr.attendance.reports.*",
                        permission: "attendance.reports.absenteeism",
                    },
                    {
                        label: "Reports — Overtime",
                        route: "hr.attendance.reports.index",
                        params: { type: "overtime" },
                        active: "hr.attendance.reports.*",
                        permission: "attendance.reports.overtime",
                    },
                    {
                        label: "Reports — Summary",
                        route: "hr.attendance.reports.index",
                        params: { type: "summary" },
                        active: "hr.attendance.reports.*",
                        permission: "attendance.reports.summary",
                    },
                ],
            },
            {
                label: "Payroll Summary",
                route: "hr.attendance.payroll.periods",
                active: "hr.attendance.payroll.*",
                permission: "attendance.payroll.view",
            },
            {
                label: "Payroll Processing Periods",
                route: "hr.attendance.payroll.periods",
                active: "hr.attendance.payroll.*",
                permission: "attendance.payroll.process",
            },
            { label: "Administration — Audit Trail", route: "hr.attendance.administration.audit", active: "hr.attendance.administration.*", permission: "attendance-audit.view" },
            { label: "Administration — Corrections", route: "hr.attendance.administration.corrections", active: "hr.attendance.administration.*", permission: "attendance-correction-history.view" },
            { label: "Administration — Reconciliation", route: "hr.attendance.administration.reconciliation", active: "hr.attendance.administration.*", permission: "attendance-reconciliation.view" },
            { label: "Administration — Exceptions", route: "hr.attendance.administration.exceptions", active: "hr.attendance.administration.*", permission: "attendance-exception.view" },
            { label: "Administration — Imports", route: "hr.attendance.administration.imports", active: "hr.attendance.administration.*", permission: "attendance-import.view" },
            { label: "Administration — Archives", route: "hr.attendance.administration.archive", active: "hr.attendance.administration.*", permission: "attendance-archive.view" },
            { label: "Device Integration — Devices", route: "hr.attendance.device-integration.devices", active: "hr.attendance.device-integration.*", permission: "attendance.device.view" },
            { label: "Device Integration — Employee Mapping", route: "hr.attendance.device-integration.mappings", active: "hr.attendance.device-integration.*", permission: "attendance.device_mapping.view" },
            { label: "Device Integration — Device Logs", route: "hr.attendance.device-integration.logs", active: "hr.attendance.device-integration.*", permission: "attendance.device_log.view" },
            { label: "Device Integration — Sync History", route: "hr.attendance.device-integration.sync-history", active: "hr.attendance.device-integration.*", permission: "attendance.device_sync.view" },
        ],
    },
    {
        label: "Approval Engine",
        active: "approval.*",
        permission: "approval-workflow.manage-approval-workflow",
        roles: ["hr"],
        children: [
            {
                label: "Workflows",
                route: "hr.approval.workflows.index",
                active: "hr.approval.workflows.*",
            },
            {
                label: "Approval Requests",
                route: "hr.approval.requests.index",
                active: "hr.approval.requests.*",
            },
        ],
    },
    {
        label: "Leave Master",
        active: "hr.leave.*",
        permission: "leave.manage-applications",
        roles: ["hr"],
        children: [
            {
                label: "Leave Types",
                route: "hr.leave.types.index",
                active: "hr.leave.types.*",
            },
            {
                label: "Leave Policies",
                route: "hr.leave.policies.index",
                active: "hr.leave.policies.*",
            },
            {
                label: "Policy Assignments",
                route: "hr.leave.assignments.index",
                active: "hr.leave.assignments.*",
            },
            {
                label: "Holiday Calendar",
                route: "hr.leave.holidays.index",
                active: "hr.leave.holidays.*",
            },
            {
                label: "Opening Balance",
                route: "hr.leave.opening-balances.index",
                active: "hr.leave.opening-balances.*",
            },
            {
                label: "Leave Ledger",
                route: "hr.leave.ledgers.index",
                active: "hr.leave.ledgers.*",
            },
            {
                label: "Leave Applications",
                route: "hr.leave.applications.index",
                active: "hr.leave.applications.*",
            },
            {
                label: "Leave Dashboard",
                route: "hr.leave.dashboard.index",
                active: "hr.leave.dashboard.*",
            },
            {
                label: "Leave Calendar",
                route: "hr.leave.calendar.index",
                active: "hr.leave.calendar.*",
            },

            {
                label: "Year-End Processing",
                route: "hr.leave.year-end.index",
                active: "hr.leave.year-end.*",
                permission: "leave.year_end.view",
            },

            {
                label: "Leave Reports",
                route: "hr.leave.reports.index",
                active: "hr.leave.reports.*",
                permission: "leave.reports.view",
            },
            {
                label: "Manager Dashboard",
                route: "hr.leave.manager-dashboard.index",
                active: "hr.leave.manager-dashboard.*",
            },
        ],
    },
    {
        label: "Master Data",
        route: "hr.master-data.index",
        active: "hr.master-data.*",
        permission: "master-data.view-master-data",
        roles: ["hr"],
    },
    {
        label: "Payroll",
        route: "hr.payroll",
        permission: "manage payroll",
        roles: ["hr"],
    },
];

export default function HRLayout({ children, heading, subheading }) {
    return (
        <RoleLayout
            role="hr"
            heading={heading}
            subheading={subheading}
            navigation={navigation}
        >
            {children}
        </RoleLayout>
    );
}
