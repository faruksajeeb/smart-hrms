import { Head } from "@inertiajs/react";

import HRLayout from "@/Layouts/HRLayout";
import Form from "./Form";

export default function Change({
    currentAssignment,
    employee,
    shifts,
}) {
    return (
        <HRLayout
            heading="Change Shift Assignment"
            subheading="Close the current assignment and start a new one."
        >
            <Head title="Change Shift Assignment" />
            <Form
                currentAssignment={currentAssignment}
                employee={employee}
                shifts={shifts}
                isChange={true}
                submitRoute={route(
                    "hr.shift-assignments.store-change",
                    currentAssignment.id,
                )}
                method="get"
                onSuccess={() => {
                    window.visit(
                        route("hr.shift-assignments.index")
                    );
                }}
            />
        </HRLayout>
    );
}