import { Head } from '@inertiajs/react';

import HRLayout from '@/Layouts/HRLayout';
import Timeline from './Timeline';
export default function History({ employee, history }) {
    return (
        <HRLayout
            heading="Employment History"
            subheading={`Employment history for ${employee.name}`}
        >
            <Head title="Employment History" />
            <Timeline employee={employee} history={history} />
        </HRLayout>
    );
}
