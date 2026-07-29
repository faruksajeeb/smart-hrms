import { Head } from '@inertiajs/react';

import HRLayout from '@/Layouts/HRLayout';
import Form from './Form';

export default function Create({
    employees,
    preselectedEmployee,
    companies,
    branches,
    clusters,
    divisions,
    departments,
    sections,
    units,
}) {
    return (
        <HRLayout
            heading="Transfer Employee"
            subheading="Transfer an employee to a different organizational unit."
        >
            <Head title="Transfer Employee" />
            <Form
                employees={employees}
                employee={preselectedEmployee}
                companies={companies}
                branches={branches}
                clusters={clusters}
                divisions={divisions}
                departments={departments}
                sections={sections}
                units={units}
                submitRoute={route('hr.transfers.store')}
                method="post"
                onSuccess={() => {
                    window.visit(route('hr.transfers.index'));
                }}
            />
        </HRLayout>
    );
}
