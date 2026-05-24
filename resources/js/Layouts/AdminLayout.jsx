import RoleLayout from '@/Layouts/RoleLayout';

const navigation = [
    { label: 'Dashboard', route: 'admin.dashboard', active: 'admin.dashboard' },
    {
        label: 'User Management',
        route: 'admin.users.index',
        permission: 'manage users',
        roles: ['admin'],
        active: 'admin.users.*',
    },
    {
        label: 'Role Management',
        route: 'admin.roles.index',
        permission: 'manage roles',
        roles: ['admin'],
        active: 'admin.roles.*',
    },
    {
        label: 'Permission Management',
        route: 'admin.permissions.index',
        permission: 'manage permissions',
        roles: ['admin'],
        active: 'admin.permissions.*',
    },
    {
        label: 'Reports',
        route: 'admin.reports',
        permission: 'view reports',
        roles: ['admin'],
        active: 'admin.reports',
    },
     {
        label: 'Doc Analyzer',
        route: 'admin.doc-analyzer.index',
        permission: 'doc-analyzer.view-doc-analyzer',
        roles: ['admin'],
        active: 'admin.doc-analyzer.*',
    },
];

export default function AdminLayout({ children, heading, subheading }) {
    return (
        <RoleLayout
            role="admin"
            heading={heading}
            subheading={subheading}
            navigation={navigation}
        >
            {children}
        </RoleLayout>
    );
}
