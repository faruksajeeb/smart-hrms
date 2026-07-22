import HRLayout from '@/Layouts/HRLayout';
import EmployeeForm from '@/Pages/HR/Employees/Partials/EmployeeForm';
import { Head, useForm } from '@inertiajs/react';

const defaults = {
    name: '',
    email: '',
    employee_id: '',
    employment_status: 'onboarding',
    department: '',
    designation: '',
    employment_type: 'full_time',
    work_location: '',
    phone: '',
    date_of_birth: '',
    nationality: '',
    address: '',
    religion: '',
    blood_group: '',
    marital_status: '',
    qualification: '',
    skills: '',
    experience_summary: '',
    joining_date: '',
    probation_starts_on: '',
    probation_ends_on: '',
    probation_status: 'pending',
    confirmation_date: '',
    leave_policy_name: '',
    annual_leave_days: 0,
    sick_leave_days: 0,
    casual_leave_days: 0,
    carry_forward_leave_days: 0,
    salary_amount: '',
    salary_currency: 'BDT',
    pay_frequency: 'monthly',
    bank_name: '',
    bank_account_number: '',
    tax_identifier: '',
    emergency_contact_name: '',
    emergency_contact_phone: '',
    notes: '',
    company_master_data_id: '',
    branch_master_data_id: '',
    division_master_data_id: '',
    department_master_data_id: '',
    designation_master_data_id: '',
    employment_type_master_data_id: '',
    bank_master_data_id: '',
    pay_type_master_data_id: '',
    job_grade_master_data_id: '',
    religion_master_data_id: '',
    blood_group_master_data_id: '',
    marital_status_master_data_id: '',
    qualification_master_data_id: '',
    shift_id: '',
    weekly_off_policy_id: '',
    document_photo: null,
    document_cv: null,
    document_nid: null,
    document_passport: null,
    document_certificates: null,
    document_appointment_letter: null,
    document_joining_letter: null,
    password: '',
    password_confirmation: '',
};

export default function Create({ options }) {
    const { data, setData, post, processing, errors } = useForm(defaults);

    const submit = (event) => {
        event.preventDefault();

        post(route('hr.employees.store'), {
            preserveScroll: true,
        });
    };

    return (
        <HRLayout
            heading="Onboard Employee"
            subheading="Create the employee account and complete HR setup in one workflow."
        >
            <Head title="Onboard Employee" />
            <form onSubmit={submit}>
                <EmployeeForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    options={options}
                    submitLabel="Onboard Employee"
                />
            </form>
        </HRLayout>
    );
}
