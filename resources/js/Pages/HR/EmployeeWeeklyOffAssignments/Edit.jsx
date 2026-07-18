import { Head } from "@inertiajs/react";

import HRLayout from "@/Layouts/HRLayout";
import Form from "./Form";

export default function Edit({ assignment, policies, employees }) {
    return (
        <HRLayout
            heading="Edit Weekly Off Assignment"
            subheading="Modify the weekly off assignment details."
        >
            <Head title="Edit Weekly Off Assignment" />
            <Form
                assignment={assignment}
                employees={employees}
                policies={policies}
                submitRoute={route(
                    "hr.weekly-off-assignments.update",
                    assignment.id,
                )}
                method="put"
            />
        </HRLayout>
    );
}
