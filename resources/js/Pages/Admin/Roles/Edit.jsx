import AdminLayout from '@/Layouts/AdminLayout';
import RoleForm from '@/Pages/Admin/Roles/Partials/RoleForm';
import { Head, useForm } from '@inertiajs/react';

export default function Edit({ role, permissions }) {
    const { data, setData, put, processing, errors } = useForm({
        name: role.name,
        permissions: role.permissions ?? [],
    });

    const submit = (e) => {
        e.preventDefault();

        put(route('admin.roles.update', role.id), {
            preserveScroll: true,
        });
    };

    return (
        <AdminLayout
            heading="Edit Role"
            subheading={`Adjust permissions and lifecycle settings for the ${role.name} role.`}
        >
            <Head title="Edit Role" />

            <form onSubmit={submit}>
                <RoleForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    permissions={permissions}
                    submitLabel="Save Role"
                    isProtected={role.is_protected}
                />
            </form>
        </AdminLayout>
    );
}
