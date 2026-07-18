import { Head } from "@inertiajs/react";

import HRLayout from "@/Layouts/HRLayout";
import Form from "./Form";

export default function Edit({
    assignment,
    employees,
    shifts,
}) {
    return (
        <HRLayout
            heading="Edit Shift Assignment"
            subheading="Modify the shift assignment details."
        >
            <Head title="Edit Shift Assignment" />
            <Form
                assignment={assignment}
                employees={employees}
                shifts={shifts}
                onSuccess={() => {
                    window.visit(
                        route("shift-assignments.index")
                    );
                }}
            />
        </HRLayout>
    );
}