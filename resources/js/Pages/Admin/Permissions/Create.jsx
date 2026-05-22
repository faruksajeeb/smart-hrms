import AdminLayout from '@/Layouts/AdminLayout';
import PermissionForm from '@/Pages/Admin/Permissions/Partials/PermissionForm';
import { Head, useForm } from '@inertiajs/react';

export default function Create() {
    const { data, setData, post, processing, errors, reset } = useForm({
        group_name: '',
        action: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('admin.permissions.store'), {
            onSuccess: () => {
                reset('group_name');
                reset('action');
            },
            preserveScroll: true,
        });
    };

    return (
        <AdminLayout
            heading="Create Permission"
            subheading="Define a new capability that can be attached to roles and protected routes."
        >
            <Head title="Create Permission" />

            <form onSubmit={submit}>
                <PermissionForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    submitLabel="Create Permission"
                />
            </form>
        </AdminLayout>
    );
}
