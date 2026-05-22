import UserForm from '@/Pages/Admin/Users/Partials/UserForm';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

export default function Create({
    primaryRoles,
    additionalRoles,
    statusOptions,
}) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        employee_id: '',
        primary_role: 'employee',
        additional_roles: [],
        status: 'active',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('admin.users.store'), {
            onSuccess: () =>
                reset(
                    'name',
                    'email',
                    'employee_id',
                    'primary_role',
                    'additional_roles',
                    'status',
                    'password',
                    'password_confirmation',
                ),
            preserveScroll: true,
        });
    };

    return (
        <AdminLayout
            heading="Create User"
            subheading="Provision workforce accounts with one primary role and optional layered access roles."
        >
            <Head title="Create User" />

            <form onSubmit={submit}>
                <UserForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    primaryRoles={primaryRoles}
                    additionalRoles={additionalRoles}
                    statusOptions={statusOptions}
                    submitLabel="Create User"
                />
            </form>
        </AdminLayout>
    );
}
