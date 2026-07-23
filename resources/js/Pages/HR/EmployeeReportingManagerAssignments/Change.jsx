import { Head } from "@inertiajs/react";

import HRLayout from "@/Layouts/HRLayout";
import Form from "./Form";

export default function Change({
    currentAssignment,
    employee,
    managers,
}) {
    return (
        <HRLayout
            heading="Change Reporting Manager"
            subheading="Close the current assignment and start a new one."
        >
            <Head title="Change Reporting Manager" />
            <Form
                currentAssignment={currentAssignment}
                employee={employee}
                managers={managers}
                isChange={true}
                submitRoute={route(
                    "hr.reporting-manager-assignments.store-change",
                    currentAssignment.id,
                )}
                method="get"
                onSuccess={() => {
                    window.visit(
                        route("hr.reporting-manager-assignments.index")
                    );
                }}
            />
        </HRLayout>
    );
}
