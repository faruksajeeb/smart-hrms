import RoleLayout from '@/Layouts/RoleLayout';

const navigation = [
    { label: 'Dashboard', route: 'hr.dashboard' },
    {
        label: 'Employees',
        route: 'hr.employees',
        permission: 'manage employees',
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
