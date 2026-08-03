import RoleLayout from '@/Layouts/RoleLayout';

const navigation = [
    { label: 'Dashboard', route: 'hr.dashboard' },
    {
        label: 'Employee Management',
        active: 'hr.employees.*',
        permission: 'manage employees',
        roles: ['hr'],
        children:[
            {
                label: 'Employee List',
                route: 'hr.employees.index',
                active: 'hr.employees.*',
            },
            {
                label: 'Reporting Manager Assignments',
                route: 'hr.reporting-manager-assignments.index',
                active: 'hr.reporting-manager-assignments.*',
            },
            {
                label: 'Employment Movements',
                route: 'hr.employment-movements.index',
                active: 'hr.employment-movements.*',
            },
            {
                label: 'Transfer Employees',
                route: 'hr.transfers.index',
                active: 'hr.transfers.*',
            }
        ]
    },
    {
        label: 'Attendance',
        active: [
            'hr.attendance.*',
            'hr.shifts.*',
            'hr.shift-swap-requests.*',
            'hr.weekly-off-assignments.*',
            'hr.shift-assignments.*',
            'hr.bulk-assignments.*',

        ],
        permission: 'manage attendance',
        roles: ['hr'],
        children: [
            {
                label: 'Shifts',
                route: 'hr.shifts.index',
                active: 'hr.shifts.*',
            },
            {
                label: 'Swap Requests',
                route: 'hr.shift-swap-requests.index',
                active: 'hr.shift-swap-requests.*',
            },
            {
                label: 'Weekly Off Policies',
                route: 'hr.weekly-off-policies.index',
                active: 'hr.weekly-off-policies.*',
            },
            {
                label: 'Weekly Off Assignments',
                route: 'hr.weekly-off-assignments.index',
                active: 'hr.weekly-off-assignments.*',
            },
            {
                label: 'Shift Assignments',
                route: 'hr.shift-assignments.index',
                active: 'hr.shift-assignments.*',
            },
            {
                label: 'Bulk Assignments',
                route: 'hr.bulk-assignments.index',
                active: 'hr.bulk-assignments.*',
            },
        ],
    },
    {
        label: 'Approval Engine',
        active: 'approval.*',
        permission: 'approval-workflow.manage-approval-workflow',
        roles: ['hr'],
        children: [
            {
                label: 'Workflows',
                route: 'hr.approval.workflows.index',
                active: 'hr.approval.workflows.*',
            },
            {
                label: 'Approval Requests',
                route: 'hr.approval.requests.index',
                active: 'hr.approval.requests.*',
            },
        ],
    },
    {
        label: 'Leave Master',
        active: 'hr.leave.*',
        permission: 'manage leave types',
        roles: ['hr'],
        children: [
            {
                label: 'Leave Types',
                route: 'hr.leave.types.index',
                active: 'hr.leave.types.*',
            },
            {
                label: 'Leave Policies',
                route: 'hr.leave.policies.index',
                active: 'hr.leave.policies.*',
            },
            {
                label: 'Policy Assignments',
                route: 'hr.leave.assignments.index',
                active: 'hr.leave.assignments.*',
            },
            {
                label: 'Holiday Calendar',
                route: 'hr.leave.holidays.index',
                active: 'hr.leave.holidays.*',
            },
            {
                label: 'Opening Balance',
                route: 'hr.leave.opening-balances.index',
                active: 'hr.leave.opening-balances.*',
            },
            {
                label: 'Leave Ledger',
                route: 'hr.leave.ledgers.index',
                active: 'hr.leave.ledgers.*',
            },
            {
                label: 'Leave Applications',
                route: 'hr.leave.applications.index',
                active: 'hr.leave.applications.*',
            },
        ],
    },
    {
        label: 'Master Data',
        route: 'hr.master-data.index',
        active: 'hr.master-data.*',
        permission: 'master-data.view-master-data',
        roles: ['hr'],
    },
    {
        label: 'Payroll',
        route: 'hr.payroll',
        permission: 'manage payroll',
        roles: ['hr'],
    },
    {
        label: 'Leave Requests',
        route: 'hr.leave',
        permission: 'manage leave requests',
        roles: ['hr'],
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