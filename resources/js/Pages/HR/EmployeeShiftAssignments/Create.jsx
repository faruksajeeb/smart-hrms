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
                submitRoute={route("hr.shift-assignments.store")}
                method="post"
                onSuccess={() => {
                    window.visit(
                        route("hr.shift-assignments.index")
                    );
                }}
            />
        </HRLayout>
    );
}