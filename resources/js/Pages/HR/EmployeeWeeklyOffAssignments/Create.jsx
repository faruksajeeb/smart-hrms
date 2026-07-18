import { Head } from "@inertiajs/react";

import HRLayout from "@/Layouts/HRLayout";
import Form from "./Form";

export default function Create({ policies, employees }) {
    return (
        <HRLayout
            heading="Assign Weekly Off"
            subheading="Assign a weekly off policy to an employee."
        >
            <Head title="Assign Weekly Off" />
            <Form
                employees={employees}
                policies={policies}
                submitRoute={route("hr.weekly-off-assignments.store")}
                method="post"
            />
        </HRLayout>
    );
}
