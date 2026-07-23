import DangerButton from "@/Components/DangerButton";
import DatePickerInput from "@/Components/DatePickerInput";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import Modal from "@/Components/Modal";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import SearchSelect from "@/Components/SearchSelect";
import TextInput from "@/Components/TextInput";
import HRLayout from "@/Layouts/HRLayout";
import { Head, Link, useForm } from "@inertiajs/react";
import { useState } from "react";

const titleCase = (value) =>
    String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (letter) => letter.toUpperCase());

const display = (value) => value || "Not set";

const DAY_NAMES = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

function DetailItem({ label, value, children }) {
    return (
        <div className="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
            <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                {label}
            </p>
            <p className="mt-2 text-sm font-medium text-slate-800">
                {display(value)}
            </p>
            {children}
        </div>
    );
}

export default function Show({ employee, events }) {
    const [terminationOpen, setTerminationOpen] = useState(false);
    const [rejoinOpen, setRejoinOpen] = useState(false);
    const [transferOpen, setTransferOpen] = useState(false);
    const [promoteOpen, setPromoteOpen] = useState(false);
    const [incrementOpen, setIncrementOpen] = useState(false);
    const [pendingAction, setPendingAction] = useState(null);

    const terminationForm = useForm({
        employment_status: "terminated",
        termination_date: "",
        termination_type: "",
        termination_reason: "",
    });

    const rejoinForm = useForm({
        last_rejoined_on: "",
        department: employee.profile.department ?? "",
        designation: employee.profile.designation ?? "",
        notes: "",
    });

    const transferForm = useForm({
        department_master_data_id: "",
        effective_on: "",
        remarks: "",
    });

    const promoteForm = useForm({
        designation_master_data_id: "",
        effective_on: "",
        remarks: "",
    });

    const incrementForm = useForm({
        salary_amount: "",
        effective_on: "",
        remarks: "",
    });

    const requestTerminate = (event) => {
        event.preventDefault();
        setPendingAction("terminate");
    };

    const requestRejoin = (event) => {
        event.preventDefault();
        setPendingAction("rejoin");
    };

    const requestTransfer = () => setTransferOpen(true);

    const requestPromote = () => setPromoteOpen(true);

    const requestIncrement = () => setIncrementOpen(true);

    const confirmAction = () => {
        if (pendingAction === "terminate") {
            terminationForm.post(route("hr.employees.terminate", employee.id), {
                preserveScroll: true,
                onSuccess: () => setTerminationOpen(false),
            });
        } else if (pendingAction === "rejoin") {
            rejoinForm.post(route("hr.employees.rejoin", employee.id), {
                preserveScroll: true,
                onSuccess: () => setRejoinOpen(false),
            });
        }

        setPendingAction(null);
    };

    const closeTermination = () => {
        setTerminationOpen(false);
        setPendingAction(null);
    };

    const closeRejoin = () => {
        setRejoinOpen(false);
        setPendingAction(null);
    };

    const closeTransfer = () => setTransferOpen(false);

    const closePromote = () => setPromoteOpen(false);

    const closeIncrement = () => setIncrementOpen(false);

    return (
        <HRLayout
            heading={employee.name}
            subheading={`${employee.employee_id ?? "No employee ID"} · ${display(employee.profile.department)} · ${titleCase(employee.employment_status)}`}
        >
            <Head title={employee.name} />

            <div className="grid gap-6 xl:grid-cols-[1fr_380px]">
                <div className="space-y-6">
                    <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p className="text-sm font-medium text-slate-500">
                                    {employee.email}
                                </p>
                                <h2 className="mt-1 text-2xl font-semibold tracking-tight text-slate-950">
                                    {display(employee.profile.designation)}
                                </h2>
                            </div>
                            <Link
                                href={route("hr.employees.edit", employee.id)}
                                className="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                            >
                                Edit Setup
                            </Link>
                        </div>

                        <div className="mt-8 grid gap-4 md:grid-cols-3">
                            <DetailItem
                                label="Employment Status"
                                value={titleCase(employee.employment_status)}
                            />
                            <DetailItem
                                label="Account Status"
                                value={titleCase(employee.account_status)}
                            />
                            <DetailItem
                                label="Employment Type"
                                value={titleCase(
                                    employee.profile.employment_type,
                                )}
                            />
                            <DetailItem
                                label="Department"
                                value={display(employee.profile.department)}
                            >
                                <button
                                    type="button"
                                    onClick={requestTransfer}
                                    className="mt-3 inline-flex items-center rounded-xl bg-sky-700 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-sky-800"
                                >
                                    Transfer
                                </button>
                            </DetailItem>
                            <DetailItem
                                label="Designation"
                                value={display(employee.profile.designation)}
                            >
                                <button
                                    type="button"
                                    onClick={requestPromote}
                                    className="mt-3 inline-flex items-center rounded-xl bg-sky-700 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-sky-800"
                                >
                                    Promote
                                </button>
                            </DetailItem>
                            <DetailItem
                                label="Joining Date"
                                value={employee.profile.joining_date}
                            />
                            <DetailItem
                                label="Work Location"
                                value={employee.profile.work_location}
                            />
                            <DetailItem
                                label="Emergency Contact"
                                value={`${display(employee.profile.emergency_contact_name)} · ${display(employee.profile.emergency_contact_phone)}`}
                            />
                        </div>
                    </section>

                    <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 className="text-lg font-semibold text-slate-950">
                            Attendance Setup
                        </h2>
                        <div className="mt-6 grid gap-4 md:grid-cols-2">
                            <DetailItem
                                label="Shift"
                                value={employee.profile.current_shift_name || "Not set"}
                            >
                                {employee.profile.current_shift_details && (
                                    <div className="mt-3 rounded-2xl border border-slate-100 bg-white p-3">
                                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-400">Shift Details</p>
                                        <div className="mt-2 space-y-1 text-sm text-slate-600">
                                            <p><span className="font-medium">Time:</span> {employee.profile.current_shift_details.start_time} - {employee.profile.current_shift_details.end_time}</p>
                                            <p><span className="font-medium">Hours:</span> {employee.profile.current_shift_details.working_hours}h</p>
                                            <p><span className="font-medium">Grace:</span> {employee.profile.current_shift_details.grace_time} min</p>
                                            {employee.profile.current_shift_details.is_flexible ? <p className="text-emerald-700">Flexible shift</p> : null}
                                        </div>
                                    </div>
                                )}
                                {employee.profile.current_shift_assignment_id ? (
                                    <Link
                                        href={route(
                                            "hr.shift-assignments.change",
                                            employee.profile.current_shift_assignment_id,
                                        )}
                                        className="mt-3 inline-flex items-center rounded-xl bg-sky-700 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-sky-800"
                                    >
                                        Change Shift
                                    </Link>
                                ):(
                                    <Link
                                        href={route(
                                            "hr.shift-assignments.create",
                                        )}
                                        className="mt-3 inline-flex items-center rounded-xl bg-sky-700 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-sky-800"
                                    >
                                        Set Shift
                                    </Link>
                                )}
                            </DetailItem>
                            <DetailItem
                                label="Weekly Off"
                                value={
                                    employee.profile
                                        .current_weekly_off_policy_name || "Not set"
                                }
                            >
                                {employee.profile.current_weekly_off_details && (
                                    <div className="mt-3 rounded-2xl border border-slate-100 bg-white p-3">
                                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-400">Weekly Off Details</p>
                                        <div className="mt-2 space-y-1 text-sm text-slate-600">
                                            {employee.profile.current_weekly_off_details.description ? (
                                                <p>{employee.profile.current_weekly_off_details.description}</p>
                                            ) : null}
                                            <p>
                                                <span className="font-medium">Off Days:</span>{' '}
                                                {(employee.profile.current_weekly_off_details.days ?? []).map((d) => DAY_NAMES[d] ?? `Day ${d}`).join(', ')}
                                            </p>
                                        </div>
                                    </div>
                                )}
                                {employee.profile.current_weekly_off_assignment_id ? (
                                    <Link
                                        href={route(
                                            "hr.weekly-off-assignments.change",
                                            employee.profile.current_weekly_off_assignment_id,
                                        )}
                                        className="mt-3 inline-flex items-center rounded-xl bg-sky-700 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-sky-800"
                                    >
                                        Change Weekly Off
                                    </Link>
                                ) : (
                                    <Link
                                        href={route(
                                            "hr.weekly-off-assignments.create"
                                        )}
                                        className="mt-3 inline-flex items-center rounded-xl bg-sky-700 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-sky-800"
                                    >
                                        Set Weekly Off
                                    </Link>
                                )}
                            </DetailItem>
                        </div>
                    </section>

                    <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 className="text-lg font-semibold text-slate-950">
                            Reporting Manager
                        </h2>
                        <div className="mt-6 grid gap-4 md:grid-cols-4">
                            <DetailItem
                                label="Manager"
                                value={employee.profile.current_reporting_manager_details?.manager_name || "Not set"}
                            >
                                {employee.profile.current_reporting_manager_details && (
                                    <div className="mt-3 space-y-1 text-sm text-slate-600">
                                        <p><span className="font-medium">ID:</span> {employee.profile.current_reporting_manager_details.manager_employee_id}</p>
                                        <p><span className="font-medium">Email:</span> {employee.profile.current_reporting_manager_details.manager_email}</p>
                                    </div>
                                )}
                            </DetailItem>
                            <DetailItem
                                label="Effective From"
                                value={employee.profile.current_reporting_manager_details?.effective_from || "Not set"}
                            />
                            <DetailItem
                                label="Effective To"
                                value={employee.profile.current_reporting_manager_details?.effective_to || "Present"}
                            />
                            <DetailItem
                                label="Assignment Type"
                                value={titleCase(employee.profile.current_reporting_manager_details?.assignment_type || "Not set")}
                            />
                        </div>
                        <div className="mt-4 flex gap-3">
                            {employee.profile.current_reporting_manager_assignment_id ? (
                                <Link
                                    href={route(
                                        "hr.reporting-manager-assignments.change",
                                        employee.profile.current_reporting_manager_assignment_id,
                                    )}
                                    className="inline-flex items-center rounded-xl bg-sky-700 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-sky-800"
                                >
                                    Change Manager
                                </Link>
                            ) : (
                                <Link
                                    href={route(
                                        "hr.reporting-manager-assignments.create",
                                        { employee_id: employee.id }
                                    )}
                                    className="inline-flex items-center rounded-xl bg-sky-700 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-sky-800"
                                >
                                    Assign Manager
                                </Link>
                            )}
                            {employee.profile.current_reporting_manager_assignment_id && (
                                <Link
                                    href={route(
                                        "hr.reporting-manager-assignments.history",
                                        employee.profile.current_reporting_manager_assignment_id,
                                    )}
                                    className="inline-flex items-center rounded-xl border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                                >
                                    History
                                </Link>
                            )}
                        </div>
                    </section>

                    <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 className="text-lg font-semibold text-slate-950">
                            Probation, Leave, and Salary
                        </h2>
                        <div className="mt-6 grid gap-4 md:grid-cols-4">
                            <DetailItem
                                label="Probation Window"
                                value={`${display(employee.profile.probation_starts_on)} to ${display(employee.profile.probation_ends_on)}`}
                            />
                            <DetailItem
                                label="Probation Status"
                                value={titleCase(
                                    employee.profile.probation_status,
                                )}
                            />
                            <DetailItem
                                label="Confirmation Date"
                                value={employee.profile.confirmation_date}
                            />
                            <DetailItem
                                label="Salary"
                                value={employee.salary}
                            >
                                <button
                                    type="button"
                                    onClick={requestIncrement}
                                    className="mt-3 inline-flex items-center rounded-xl bg-sky-700 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-sky-800"
                                >
                                    Increment
                                </button>
                            </DetailItem>
                            <DetailItem
                                label="Leave Policy"
                                value={employee.profile.leave_policy_name}
                            />
                            <DetailItem
                                label="Annual Leave"
                                value={employee.profile.annual_leave_days}
                            />
                            <DetailItem
                                label="Sick Leave"
                                value={employee.profile.sick_leave_days}
                            />
                            <DetailItem
                                label="Casual Leave"
                                value={employee.profile.casual_leave_days}
                            />
                            <DetailItem
                                label="Carry Forward"
                                value={
                                    employee.profile.carry_forward_leave_days
                                }
                            />
                            <DetailItem
                                label="Pay Frequency"
                                value={titleCase(
                                    employee.profile.pay_frequency,
                                )}
                            />
                            <DetailItem
                                label="Bank"
                                value={employee.profile.bank_name}
                            />
                            <DetailItem
                                label="Tax ID"
                                value={employee.profile.tax_identifier}
                            />
                        </div>
                    </section>

                    <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 className="text-lg font-semibold text-slate-950">
                            Lifecycle Timeline
                        </h2>
                        <div className="mt-6 space-y-4">
                            {events.map((event) => (
                                <article
                                    key={event.id}
                                    className="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                                >
                                    <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                                                {titleCase(event.type)}
                                            </p>
                                            <h3 className="mt-1 font-semibold text-slate-950">
                                                {event.title}
                                            </h3>
                                        </div>
                                        <p className="text-sm text-slate-500">
                                            {event.effective_on ??
                                                event.created_at}
                                        </p>
                                    </div>
                                    {event.notes && (
                                        <p className="mt-3 text-sm leading-6 text-slate-600">
                                            {event.notes}
                                        </p>
                                    )}
                                    <p className="mt-3 text-xs text-slate-400">
                                        Recorded by{" "}
                                        {event.created_by ?? "System"}
                                    </p>
                                </article>
                            ))}
                        </div>

                        {events.length === 0 && (
                            <div className="mt-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-8 text-center text-sm text-slate-500">
                                No lifecycle events recorded yet.
                            </div>
                        )}
                    </section>
                </div>

                <aside className="space-y-6">
                    <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 className="text-lg font-semibold text-slate-950">
                            Lifecycle Actions
                        </h2>
                        <p className="mt-1 text-sm text-slate-500">
                            Record a separation or rejoin for this employee.
                        </p>
                        <div className="mt-4 flex flex-col gap-3">
                            <button
                                type="button"
                                onClick={() => setTerminationOpen(true)}
                                className="inline-flex w-full items-center justify-center rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-100"
                            >
                                Left / Termination
                            </button>
                            <button
                                type="button"
                                onClick={() => setRejoinOpen(true)}
                                className="inline-flex w-full items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100"
                            >
                                Rejoin Employee
                            </button>
                        </div>
                    </section>

                    <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 className="text-lg font-semibold text-slate-950">
                            Employee Documents
                        </h2>
                        <div className="mt-6 grid gap-3 md:grid-cols-2">
                            {(employee.documents ?? []).map((document) => (
                                <div
                                    key={document.id}
                                    className="rounded-2xl border border-slate-100 bg-slate-50 p-4"
                                >
                                    <div className="flex items-start justify-between gap-3">
                                        <div>
                                            <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                                                {document.label}
                                            </p>
                                            <p className="mt-2 text-sm font-medium text-slate-800">
                                                {document.filename}
                                            </p>
                                        </div>
                                    </div>
                                    {document.mime_type?.startsWith(
                                        "image/",
                                    ) && (
                                        <img
                                            src={document.view_url}
                                            alt={document.filename}
                                            className="mt-3 max-h-56 w-full rounded-xl border border-slate-200 bg-white object-contain"
                                        />
                                    )}
                                    <p className="mt-3 text-xs text-slate-500">
                                        {document.expiry_date
                                            ? `Expires ${document.expiry_date}`
                                            : "No expiry date"}
                                    </p>
                                    {document.remarks && (
                                        <p className="mt-2 text-sm text-slate-600">
                                            {document.remarks}
                                        </p>
                                    )}

                                    <div className="flex shrink-0 gap-2 mt-4">
                                        <a
                                            href={document.view_url}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="rounded-xl border border-slate-200 bg-white px-2 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100"
                                        >
                                            Open
                                        </a>
                                        <a
                                            href={document.download_url}
                                            className="rounded-xl border border-slate-200 bg-white px-2 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100"
                                        >
                                            Download
                                        </a>
                                    </div>
                                </div>
                            ))}
                        </div>
                        {(employee.documents ?? []).length === 0 && (
                            <div className="mt-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-8 text-center text-sm text-slate-500">
                                No employee documents uploaded yet.
                            </div>
                        )}
                    </section>

                    {employee.master_data_tags?.length > 0 && (
                        <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h2 className="text-lg font-semibold text-slate-950">
                                Tagged Master Data
                            </h2>
                            <div className="mt-6 flex flex-wrap gap-2">
                                {employee.master_data_tags.map((tag) => (
                                    <span
                                        key={tag.id}
                                        className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700"
                                    >
                                        {tag.category_label}: {tag.name}
                                    </span>
                                ))}
                            </div>
                        </section>
                    )}
                </aside>
            </div>

            <Modal
                show={terminationOpen}
                onClose={closeTermination}
                maxWidth="2xl"
            >
                <div className="p-6">
                    <h2 className="text-lg font-semibold text-slate-950">
                        Left / Termination
                    </h2>
                    <p className="mt-1 text-sm text-slate-500">
                        Record a separation for {employee.name}.
                    </p>
                    <form
                        onSubmit={requestTerminate}
                        className="mt-6 space-y-4"
                    >
                        <SearchSelect
                            id="employment_status"
                            label="Separation Status"
                            value={terminationForm.data.employment_status}
                            onChange={(value) =>
                                terminationForm.setData(
                                    "employment_status",
                                    value,
                                )
                            }
                            options={["terminated", "left"]}
                            error={terminationForm.errors.employment_status}
                            placeholder="Select separation status..."
                            isClearable={false}
                        />
                        <DatePickerInput
                            id="termination_date"
                            label="Effective Date"
                            value={terminationForm.data.termination_date}
                            onChange={(value) =>
                                terminationForm.setData(
                                    "termination_date",
                                    value,
                                )
                            }
                            error={terminationForm.errors.termination_date}
                        />
                        <div>
                            <InputLabel
                                htmlFor="termination_type"
                                value="Type"
                            />
                            <TextInput
                                id="termination_type"
                                value={terminationForm.data.termination_type}
                                onChange={(event) =>
                                    terminationForm.setData(
                                        "termination_type",
                                        event.target.value,
                                    )
                                }
                                placeholder="Resignation, dismissal, end of contract"
                                className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3"
                            />
                            <InputError
                                className="mt-2"
                                message={
                                    terminationForm.errors.termination_type
                                }
                            />
                        </div>
                        <div>
                            <InputLabel
                                htmlFor="termination_reason"
                                value="Reason"
                            />
                            <textarea
                                id="termination_reason"
                                value={terminationForm.data.termination_reason}
                                onChange={(event) =>
                                    terminationForm.setData(
                                        "termination_reason",
                                        event.target.value,
                                    )
                                }
                                rows="4"
                                className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-slate-400 focus:ring-slate-400"
                            />
                            <InputError
                                className="mt-2"
                                message={
                                    terminationForm.errors.termination_reason
                                }
                            />
                        </div>
                        <div className="flex justify-end gap-3">
                            <SecondaryButton onClick={closeTermination}>
                                Cancel
                            </SecondaryButton>
                            <DangerButton
                                disabled={terminationForm.processing}
                                className="rounded-2xl px-5 py-3 text-sm normal-case tracking-normal"
                            >
                                Record Separation
                            </DangerButton>
                        </div>
                    </form>
                </div>
            </Modal>

            <Modal show={rejoinOpen} onClose={closeRejoin} maxWidth="2xl">
                <div className="p-6">
                    <h2 className="text-lg font-semibold text-slate-950">
                        Rejoin Employee
                    </h2>
                    <p className="mt-1 text-sm text-slate-500">
                        Rejoin and reactivate {employee.name}'s account.
                    </p>
                    <form onSubmit={requestRejoin} className="mt-6 space-y-4">
                        <DatePickerInput
                            id="last_rejoined_on"
                            label="Rejoin Date"
                            value={rejoinForm.data.last_rejoined_on}
                            onChange={(value) =>
                                rejoinForm.setData("last_rejoined_on", value)
                            }
                            error={rejoinForm.errors.last_rejoined_on}
                        />
                        <div>
                            <InputLabel
                                htmlFor="department"
                                value="Department"
                            />
                            <TextInput
                                id="department"
                                value={rejoinForm.data.department}
                                onChange={(event) =>
                                    rejoinForm.setData(
                                        "department",
                                        event.target.value,
                                    )
                                }
                                className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3"
                            />
                            <InputError
                                className="mt-2"
                                message={rejoinForm.errors.department}
                            />
                        </div>
                        <div>
                            <InputLabel
                                htmlFor="designation"
                                value="Designation"
                            />
                            <TextInput
                                id="designation"
                                value={rejoinForm.data.designation}
                                onChange={(event) =>
                                    rejoinForm.setData(
                                        "designation",
                                        event.target.value,
                                    )
                                }
                                className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3"
                            />
                            <InputError
                                className="mt-2"
                                message={rejoinForm.errors.designation}
                            />
                        </div>
                        <div>
                            <InputLabel htmlFor="rejoin_notes" value="Notes" />
                            <textarea
                                id="rejoin_notes"
                                value={rejoinForm.data.notes}
                                onChange={(event) =>
                                    rejoinForm.setData(
                                        "notes",
                                        event.target.value,
                                    )
                                }
                                rows="3"
                                className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-slate-400 focus:ring-slate-400"
                            />
                            <InputError
                                className="mt-2"
                                message={rejoinForm.errors.notes}
                            />
                        </div>
                        <div className="flex justify-end gap-3">
                            <SecondaryButton onClick={closeRejoin}>
                                Cancel
                            </SecondaryButton>
                            <PrimaryButton
                                disabled={rejoinForm.processing}
                                className="rounded-2xl bg-emerald-600 px-5 py-3 hover:bg-emerald-700"
                            >
                                Rejoin and Activate
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>

            <Modal
                show={pendingAction !== null}
                onClose={() => setPendingAction(null)}
                maxWidth="sm"
            >
                <div className="p-6">
                    <h2 className="text-lg font-semibold text-slate-950">
                        {pendingAction === "terminate"
                            ? "Confirm Separation"
                            : "Confirm Rejoin"}
                    </h2>
                    <p className="mt-2 text-sm text-slate-600">
                        {pendingAction === "terminate"
                            ? `Record separation for ${employee.name}? The account will be deactivated.`
                            : `Rejoin and reactivate ${employee.name}'s account?`}
                    </p>
                    <div className="mt-6 flex justify-end gap-3">
                        <SecondaryButton onClick={() => setPendingAction(null)}>
                            Cancel
                        </SecondaryButton>
                        {pendingAction === "terminate" ? (
                            <DangerButton
                                onClick={confirmAction}
                                disabled={terminationForm.processing}
                                className="rounded-2xl px-5 py-3 text-sm normal-case tracking-normal"
                            >
                                Confirm Separation
                            </DangerButton>
                        ) : (
                            <PrimaryButton
                                onClick={confirmAction}
                                disabled={rejoinForm.processing}
                                className="rounded-2xl bg-emerald-600 px-5 py-3 hover:bg-emerald-700"
                            >
                                Confirm Rejoin
                            </PrimaryButton>
                        )}
                    </div>
                </div>
            </Modal>

            <Modal show={transferOpen} onClose={closeTransfer} maxWidth="2xl">
                <div className="p-6">
                    <h2 className="text-lg font-semibold text-slate-950">Transfer Department</h2>
                    <p className="mt-1 text-sm text-slate-500">Transfer {employee.name} to a new department.</p>
                    <form onSubmit={(e) => { e.preventDefault(); transferForm.post(route('hr.employees.transfer', employee.id), { onSuccess: closeTransfer }); }} className="mt-6 space-y-4">
                        <SearchSelect id="department_master_data_id" label="New Department" value={transferForm.data.department_master_data_id} options={employee.transfer_department_options} onChange={(value) => transferForm.setData('department_master_data_id', value)} error={transferForm.errors.department_master_data_id} placeholder="Search department..." />
                        <DatePickerInput id="effective_on" label="Effective Date" value={transferForm.data.effective_on} onChange={(value) => transferForm.setData('effective_on', value)} error={transferForm.errors.effective_on} />
                        <div>
                            <InputLabel htmlFor="transfer_remarks" value="Remarks" />
                            <textarea id="transfer_remarks" value={transferForm.data.remarks} onChange={(e) => transferForm.setData('remarks', e.target.value)} rows="3" className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-slate-400 focus:ring-slate-400" />
                            <InputError className="mt-2" message={transferForm.errors.remarks} />
                        </div>
                        <div className="flex justify-end gap-3">
                            <SecondaryButton onClick={closeTransfer}>Cancel</SecondaryButton>
                            <PrimaryButton disabled={transferForm.processing} className="rounded-2xl bg-sky-700 px-5 py-3 hover:bg-sky-800">Transfer</PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>

            <Modal show={promoteOpen} onClose={closePromote} maxWidth="2xl">
                <div className="p-6">
                    <h2 className="text-lg font-semibold text-slate-950">Promote Employee</h2>
                    <p className="mt-1 text-sm text-slate-500">Promote {employee.name} to a new designation.</p>
                    <form onSubmit={(e) => { e.preventDefault(); promoteForm.post(route('hr.employees.promote', employee.id), { onSuccess: closePromote }); }} className="mt-6 space-y-4">
                        <SearchSelect id="designation_master_data_id" label="New Designation" value={promoteForm.data.designation_master_data_id} options={employee.promote_designation_options} onChange={(value) => promoteForm.setData('designation_master_data_id', value)} error={promoteForm.errors.designation_master_data_id} placeholder="Search designation..." />
                        <DatePickerInput id="effective_on" label="Effective Date" value={promoteForm.data.effective_on} onChange={(value) => promoteForm.setData('effective_on', value)} error={promoteForm.errors.effective_on} />
                        <div>
                            <InputLabel htmlFor="promote_remarks" value="Remarks" />
                            <textarea id="promote_remarks" value={promoteForm.data.remarks} onChange={(e) => promoteForm.setData('remarks', e.target.value)} rows="3" className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-slate-400 focus:ring-slate-400" />
                            <InputError className="mt-2" message={promoteForm.errors.remarks} />
                        </div>
                        <div className="flex justify-end gap-3">
                            <SecondaryButton onClick={closePromote}>Cancel</SecondaryButton>
                            <PrimaryButton disabled={promoteForm.processing} className="rounded-2xl bg-sky-700 px-5 py-3 hover:bg-sky-800">Promote</PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>

            <Modal show={incrementOpen} onClose={closeIncrement} maxWidth="2xl">
                <div className="p-6">
                    <h2 className="text-lg font-semibold text-slate-950">Salary Increment</h2>
                    <p className="mt-1 text-sm text-slate-500">Increment salary for {employee.name}.</p>
                    <form onSubmit={(e) => { e.preventDefault(); incrementForm.post(route('hr.employees.increment', employee.id), { onSuccess: closeIncrement }); }} className="mt-6 space-y-4">
                        <div>
                            <InputLabel htmlFor="salary_amount" value="New Salary Amount" />
                            <TextInput id="salary_amount" type="number" step="0.01" value={incrementForm.data.salary_amount} onChange={(e) => incrementForm.setData('salary_amount', e.target.value)} error={incrementForm.errors.salary_amount} className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3" />
                        </div>
                        <DatePickerInput id="effective_on" label="Effective Date" value={incrementForm.data.effective_on} onChange={(value) => incrementForm.setData('effective_on', value)} error={incrementForm.errors.effective_on} />
                        <div>
                            <InputLabel htmlFor="increment_remarks" value="Remarks" />
                            <textarea id="increment_remarks" value={incrementForm.data.remarks} onChange={(e) => incrementForm.setData('remarks', e.target.value)} rows="3" className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-slate-400 focus:ring-slate-400" />
                            <InputError className="mt-2" message={incrementForm.errors.remarks} />
                        </div>
                        <div className="flex justify-end gap-3">
                            <SecondaryButton onClick={closeIncrement}>Cancel</SecondaryButton>
                            <PrimaryButton disabled={incrementForm.processing} className="rounded-2xl bg-sky-700 px-5 py-3 hover:bg-sky-800">Increment Salary</PrimaryButton>
                        </div>
                    </form>
                </div>
            </Modal>
        </HRLayout>
    );
}
