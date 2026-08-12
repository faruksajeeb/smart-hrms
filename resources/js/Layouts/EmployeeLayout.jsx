import RoleLayout from '@/Layouts/RoleLayout';

const navigation = [
    { label: 'Dashboard', route: 'employee.dashboard' },
    {
        label: 'Attendance',
        route: 'employee.attendance',
        permission: 'manage attendance',
        roles: ['employee'],
    },
    {
        label: 'Leave Center',
        route: 'employee.leave',
        permission: 'manage leave requests',
        roles: ['employee'],
        children: [
            {
                label: 'My Applications',
                route: 'employee.leave.applications.index',
                active: 'employee.leave.applications.*',
            },
            {
                label: 'Apply for Leave',
                route: 'employee.leave.applications.create',
                active: 'employee.leave.applications.create',
            },
             {
                label: 'My Leave Calendar',
                route: 'employee.leave.calendar.index',
                active: 'employee.leave.calendar.*',
            },
             {
                label: 'Team Calendar',
                route: 'employee.leave.team-calendar.index',
                active: 'employee.leave.team-calendar.*',
            },
        ],
    },
];

export default function EmployeeLayout({ children, heading, subheading }) {
    return (
        <RoleLayout
            role="employee"
            heading={heading}
            subheading={subheading}
            navigation={navigation}
        >
            {children}
        </RoleLayout>
    );
}
