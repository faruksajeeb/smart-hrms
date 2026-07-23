import { Head } from "@inertiajs/react";

import HRLayout from "@/Layouts/HRLayout";
import Form from "./Form";

export default function Create({
    employees,
    managers,
    preselectedEmployee,
}) {
    return (
        <HRLayout
            heading="Assign Reporting Manager"
            subheading="Assign a reporting manager to an employee."
        >
            <Head title="Assign Reporting Manager" />
            <Form
                employees={employees}
                managers={managers}
                employee={preselectedEmployee}
                submitRoute={route("hr.reporting-manager-assignments.store")}
                method="post"
                onSuccess={() => {
                    window.visit(
                        route("hr.reporting-manager-assignments.index")
                    );
                }}
            />
        </HRLayout>
    );
}
