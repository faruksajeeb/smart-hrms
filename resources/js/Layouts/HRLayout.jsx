import RoleLayout from '@/Layouts/RoleLayout';

const navigation = [
    { label: 'Dashboard', route: 'hr.dashboard' },
    {
        label: 'Employees',
        route: 'hr.employees.index',
        active: 'hr.employees.*',
        permission: 'manage employees',
        roles: ['hr'],
    },
    {
        label: 'Attendance',
        active: [
            'hr.attendance.*',
            'hr.shifts.*',
            'hr.shift-schedules.*',
            'hr.shift-swap-requests.*',
            'hr.weekly-off-assignments.*',
            'hr.shift-assignments.*',
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
                label: 'Shift & Schedule',
                route: 'hr.shift-schedules.index',
                active: 'hr.shift-schedules.*',
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