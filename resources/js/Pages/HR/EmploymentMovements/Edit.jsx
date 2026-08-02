import { Head } from '@inertiajs/react';

import HRLayout from '@/Layouts/HRLayout';
import Form from './Form';

export default function Edit({
    movement,
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
            heading="Edit Employment Movement"
            subheading="Update employment movement details."
        >
            <Head title="Edit Employment Movement" />
            <Form
                movement={movement}
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
                submitRoute={route('hr.employment-movements.update', movement.id)}
                method="put"
            />
        </HRLayout>
    );
}
