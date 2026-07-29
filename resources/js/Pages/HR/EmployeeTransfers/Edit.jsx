import { Head } from '@inertiajs/react';

import HRLayout from '@/Layouts/HRLayout';
import Form from './Form';

export default function Edit({ transfer, companies, branches, clusters, divisions, departments, sections, units }) {
    return (
        <HRLayout
            heading="Edit Transfer"
            subheading="Update employee transfer details."
        >
            <Head title="Edit Transfer" />
            <Form
                transfer={transfer}
                companies={companies}
                branches={branches}
                clusters={clusters}
                divisions={divisions}
                departments={departments}
                sections={sections}
                units={units}
                submitRoute={route('hr.transfers.update', transfer.id)}
                method="put"
                onSuccess={() => {
                    window.visit(route('hr.transfers.index'));
                }}
            />
        </HRLayout>
    );
}
