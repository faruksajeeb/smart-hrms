import AdminLayout from '@/Layouts/AdminLayout';
import RoleForm from '@/Pages/Admin/Roles/Partials/RoleForm';
import { Head, useForm } from '@inertiajs/react';

export default function Create({ permissions }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        permissions: [],
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('admin.roles.store'), {
            onSuccess: () => reset('name', 'permissions'),
            preserveScroll: true,
        });
    };

    return (
        <AdminLayout
            heading="Create Role"
            subheading="Create a reusable role bundle and assign the permissions it should carry."
        >
            <Head title="Create Role" />

            <form onSubmit={submit}>
                <RoleForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    permissions={permissions}
                    submitLabel="Create Role"
                />
            </form>
        </AdminLayout>
    );
}
