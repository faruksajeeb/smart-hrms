import { Head } from "@inertiajs/react";

import HRLayout from "@/Layouts/HRLayout";
import Form from "./Form";

export default function Create({
    companies,
    clonePolicy,
}) {

    return (

        <HRLayout
            heading="Create Weekly Off Policy"
            subheading="Create a reusable weekly off policy for shifts and employees."
        >

            <Head
                title="Create Weekly Off Policy"
            />

            <Form
                companies={companies}
                policy={clonePolicy ?? null}
                isEdit={false}
            />

        </HRLayout>

    );

}