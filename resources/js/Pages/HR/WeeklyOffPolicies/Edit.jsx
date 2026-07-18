import { Head } from "@inertiajs/react";

import HRLayout from "@/Layouts/HRLayout";
import Form from "./Form";

export default function Edit({
    policy,
    companies,
}) {
    return (
        <HRLayout
            heading="Edit Weekly Off Policy"
            subheading="Modify the weekly off policy configuration."
        >
            <Head title="Edit Weekly Off Policy" />

            <Form
                companies={companies}
                policy={policy}
                isEdit={true}
            />
        </HRLayout>
    );
}