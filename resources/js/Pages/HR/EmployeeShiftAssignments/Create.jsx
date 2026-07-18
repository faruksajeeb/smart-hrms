import { Head } from "@inertiajs/react";

import HRLayout from "@/Layouts/HRLayout";
import Form from "./Form";

export default function Create({
    employees,
    shifts,
}) {
    return (
        <HRLayout
            heading="Assign Shift"
            subheading="Assign a shift to an employee."
        >
            <Head title="Assign Shift" />
            <Form
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