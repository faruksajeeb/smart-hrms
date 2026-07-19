import { Head } from "@inertiajs/react";

import HRLayout from "@/Layouts/HRLayout";
import Form from "./Form";

export default function Edit({
    assignment,
    employee,
    policies,
}) {
    return (
        <HRLayout
            heading="Edit Weekly Off Assignment"
            subheading="Update the employee weekly off assignment."
        >
            <Head title="Edit Weekly Off Assignment" />

            <Form
                assignment={assignment}
                employee={employee}
                policies={policies}
                submitRoute={route(
                    "hr.weekly-off-assignments.update",
                    assignment.id
                )}
                method="put"
            />
        </HRLayout>
    );
}