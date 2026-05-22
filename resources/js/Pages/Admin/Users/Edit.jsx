import AdminLayout from '@/Layouts/AdminLayout';
import UserForm from '@/Pages/Admin/Users/Partials/UserForm';
import { Head, useForm } from '@inertiajs/react';

export default function Edit({
    user,
    primaryRoles,
    additionalRoles,
    statusOptions,
}) {
    const { data, setData, put, processing, errors } = useForm({
        name: user.name,
        email: user.email,
        employee_id: user.employee_id ?? '',
        primary_role: user.primary_role,
        additional_roles: user.additional_roles ?? [],
        status: user.status,
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();

        put(route('admin.users.update', user.id), {
            preserveScroll: true,
        });
    };

    return (
        <AdminLayout
            heading="Edit User"
            subheading={`Adjust account details, roles, and status for ${user.name}.`}
        >
            <Head title="Edit User" />

            <form onSubmit={submit}>
                <UserForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    primaryRoles={primaryRoles}
                    additionalRoles={additionalRoles}
                    statusOptions={statusOptions}
                    submitLabel="Save Changes"
                    isEdit={true}
                />
            </form>
        </AdminLayout>
    );
}
