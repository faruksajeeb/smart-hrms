import HRLayout from '@/Layouts/HRLayout';
import EmployeeForm from '@/Pages/HR/Employees/Partials/EmployeeForm';
import { Head, useForm } from '@inertiajs/react';

export default function Edit({ employee, options }) {
    const { data, setData, put, processing, errors } = useForm({
        name: employee.name,
        email: employee.email,
        employee_id: employee.employee_id ?? '',
        ...employee.profile,
        ...Object.fromEntries(
            Object.entries(employee.master_data_ids ?? {}).map(([key, value]) => [
                key,
                value ?? '',
            ]),
        ),
        password: '',
        password_confirmation: '',
    });

    const submit = (event) => {
        event.preventDefault();

        put(route('hr.employees.update', employee.id), {
            preserveScroll: true,
        });
    };

    return (
        <HRLayout
            heading="Edit Employee"
            subheading={`Update onboarding, probation, leave, salary, and contact setup for ${employee.name}.`}
        >
            <Head title={`Edit ${employee.name}`} />
            <form onSubmit={submit}>
                <EmployeeForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    options={options}
                    submitLabel="Save Employee"
                    isEdit={true}
                />
            </form>
        </HRLayout>
    );
}
