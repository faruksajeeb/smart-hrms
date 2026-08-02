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
    designations,
    employmentTypes,
    managers,
    eventTypes,
}) {
    return (
        <HRLayout
            heading="Employment Movement"
            subheading="Record a new employment movement for an employee."
        >
            <Head title="Employment Movement" />
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
                designations={designations}
                employmentTypes={employmentTypes}
                managers={managers}
                eventTypes={eventTypes}
                submitRoute={route('hr.employment-movements.store')}
                method="post"
            />
        </HRLayout>
    );
}
