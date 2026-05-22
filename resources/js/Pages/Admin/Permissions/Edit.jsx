import AdminLayout from '@/Layouts/AdminLayout';
import PermissionForm from '@/Pages/Admin/Permissions/Partials/PermissionForm';
import { Head, useForm } from '@inertiajs/react';

export default function Edit({ permission }) {
    const [initialGroup, initialAction] = permission.name.includes('.')
        ? permission.name.split('.', 2)
        : ['', permission.name];

    const { data, setData, put, processing, errors } = useForm({
        group_name: initialGroup,
        action: initialAction,
    });

    const submit = (e) => {
        e.preventDefault();

        put(route('admin.permissions.update', permission.id), {
            preserveScroll: true,
        });
    };

    return (
        <AdminLayout
            heading="Edit Permission"
            subheading={`Update the ${permission.name} permission definition.`}
        >
            <Head title="Edit Permission" />

            <form onSubmit={submit}>
                <PermissionForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    submitLabel="Save Permission"
                    isProtected={permission.is_protected}
                />
            </form>
        </AdminLayout>
    );
}
