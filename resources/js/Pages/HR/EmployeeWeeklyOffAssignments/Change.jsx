import { Head } from "@inertiajs/react";

import HRLayout from "@/Layouts/HRLayout";
import Form from "./Form";

export default function Change({ currentAssignment, employee, policies }) {
    return (
        <HRLayout
            heading="Change Weekly Off Assignment"
            subheading="Create a new assignment while preserving history."
        >
            <Head title="Change Weekly Off Assignment" />

            <Form
                employee={employee}
                policies={policies}
                currentAssignment={currentAssignment}
                submitRoute={route(
                    "hr.weekly-off-assignments.store-change",
                    currentAssignment.id,
                )}
                method="get"
                isChange={true}
            />
        </HRLayout>
    );
}
