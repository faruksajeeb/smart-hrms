import DangerButton from '@/Components/DangerButton';
import DatePickerInput from '@/Components/DatePickerInput';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SearchSelect from '@/Components/SearchSelect';
import TextInput from '@/Components/TextInput';
import HRLayout from '@/Layouts/HRLayout';
import { Head, Link, useForm } from '@inertiajs/react';

const titleCase = (value) =>
    String(value ?? '')
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());

const display = (value) => value || 'Not set';

function DetailItem({ label, value }) {
    return (
        <div className="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
            <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">{label}</p>
            <p className="mt-2 text-sm font-medium text-slate-800">{display(value)}</p>
        </div>
    );
}

export default function Show({ employee, events }) {
    const terminationForm = useForm({
        employment_status: 'terminated',
        termination_date: '',
        termination_type: '',
        termination_reason: '',
    });

    const rejoinForm = useForm({
        last_rejoined_on: '',
        department: employee.profile.department ?? '',
        designation: employee.profile.designation ?? '',
        notes: '',
    });

    const terminate = (event) => {
        event.preventDefault();

        if (!window.confirm(`Record separation for ${employee.name}? The account will be deactivated.`)) {
            return;
        }

        terminationForm.post(route('hr.employees.terminate', employee.id), {
            preserveScroll: true,
        });
    };

    const rejoin = (event) => {
        event.preventDefault();

        rejoinForm.post(route('hr.employees.rejoin', employee.id), {
            preserveScroll: true,
        });
    };

    return (
        <HRLayout
            heading={employee.name}
            subheading={`${employee.employee_id ?? 'No employee ID'} · ${display(employee.profile.department)} · ${titleCase(employee.employment_status)}`}
        >
            <Head title={employee.name} />

            <div className="grid gap-6 xl:grid-cols-[1fr_380px]">
                <div className="space-y-6">
                    <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p className="text-sm font-medium text-slate-500">{employee.email}</p>
                                <h2 className="mt-1 text-2xl font-semibold tracking-tight text-slate-950">
                                    {display(employee.profile.designation)}
                                </h2>
                            </div>
                            <Link href={route('hr.employees.edit', employee.id)} className="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                                Edit Setup
                            </Link>
                        </div>

                        <div className="mt-8 grid gap-4 md:grid-cols-3">
                            <DetailItem label="Employment Status" value={titleCase(employee.employment_status)} />
                            <DetailItem label="Account Status" value={titleCase(employee.account_status)} />
                            <DetailItem label="Employment Type" value={titleCase(employee.profile.employment_type)} />
                            <DetailItem label="Joining Date" value={employee.profile.joining_date} />
                            <DetailItem label="Work Location" value={employee.profile.work_location} />
                            <DetailItem label="Emergency Contact" value={`${display(employee.profile.emergency_contact_name)} · ${display(employee.profile.emergency_contact_phone)}`} />
                        </div>
                    </section>

                    {employee.master_data_tags?.length > 0 && (
                        <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h2 className="text-lg font-semibold text-slate-950">Tagged Master Data</h2>
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

                    <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 className="text-lg font-semibold text-slate-950">Employee Documents</h2>
                        <div className="mt-6 grid gap-3 md:grid-cols-2">
                            {(employee.documents ?? []).map((document) => (
                                <div key={document.id} className="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                    <div className="flex items-start justify-between gap-3">
                                        <div>
                                            <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">{document.label}</p>
                                            <p className="mt-2 text-sm font-medium text-slate-800">{document.filename}</p>
                                        </div>
                                        <a href={document.download_url} target="_blank" rel="noreferrer" className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                            Open
                                        </a>
                                    </div>
                                    <p className="mt-3 text-xs text-slate-500">
                                        {document.expiry_date ? `Expires ${document.expiry_date}` : 'No expiry date'}
                                    </p>
                                    {document.remarks && <p className="mt-2 text-sm text-slate-600">{document.remarks}</p>}
                                </div>
                            ))}
                        </div>
                        {(employee.documents ?? []).length === 0 && (
                            <div className="mt-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-8 text-center text-sm text-slate-500">
                                No employee documents uploaded yet.
                            </div>
                        )}
                    </section>

                    <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 className="text-lg font-semibold text-slate-950">Probation, Leave, and Salary</h2>
                        <div className="mt-6 grid gap-4 md:grid-cols-4">
                            <DetailItem label="Probation Window" value={`${display(employee.profile.probation_starts_on)} to ${display(employee.profile.probation_ends_on)}`} />
                            <DetailItem label="Probation Status" value={titleCase(employee.profile.probation_status)} />
                            <DetailItem label="Confirmation Date" value={employee.profile.confirmation_date} />
                            <DetailItem label="Salary" value={employee.salary} />
                            <DetailItem label="Leave Policy" value={employee.profile.leave_policy_name} />
                            <DetailItem label="Annual Leave" value={employee.profile.annual_leave_days} />
                            <DetailItem label="Sick Leave" value={employee.profile.sick_leave_days} />
                            <DetailItem label="Casual Leave" value={employee.profile.casual_leave_days} />
                            <DetailItem label="Carry Forward" value={employee.profile.carry_forward_leave_days} />
                            <DetailItem label="Pay Frequency" value={titleCase(employee.profile.pay_frequency)} />
                            <DetailItem label="Bank" value={employee.profile.bank_name} />
                            <DetailItem label="Tax ID" value={employee.profile.tax_identifier} />
                        </div>
                    </section>

                    <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 className="text-lg font-semibold text-slate-950">Lifecycle Timeline</h2>
                        <div className="mt-6 space-y-4">
                            {events.map((event) => (
                                <article key={event.id} className="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                    <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">{titleCase(event.type)}</p>
                                            <h3 className="mt-1 font-semibold text-slate-950">{event.title}</h3>
                                        </div>
                                        <p className="text-sm text-slate-500">{event.effective_on ?? event.created_at}</p>
                                    </div>
                                    {event.notes && <p className="mt-3 text-sm leading-6 text-slate-600">{event.notes}</p>}
                                    <p className="mt-3 text-xs text-slate-400">Recorded by {event.created_by ?? 'System'}</p>
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
                    <section className="rounded-3xl border border-rose-100 bg-white p-6 shadow-sm">
                        <h2 className="text-lg font-semibold text-slate-950">Left / Termination</h2>
                        <form onSubmit={terminate} className="mt-6 space-y-4">
                            <SearchSelect
                                id="employment_status"
                                label="Separation Status"
                                value={terminationForm.data.employment_status}
                                onChange={(value) => terminationForm.setData('employment_status', value)}
                                options={['terminated', 'left']}
                                error={terminationForm.errors.employment_status}
                                placeholder="Select separation status..."
                                isClearable={false}
                            />
                            <DatePickerInput
                                id="termination_date"
                                label="Effective Date"
                                value={terminationForm.data.termination_date}
                                onChange={(value) => terminationForm.setData('termination_date', value)}
                                error={terminationForm.errors.termination_date}
                            />
                            <div>
                                <InputLabel htmlFor="termination_type" value="Type" />
                                <TextInput id="termination_type" value={terminationForm.data.termination_type} onChange={(event) => terminationForm.setData('termination_type', event.target.value)} placeholder="Resignation, dismissal, end of contract" className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3" />
                                <InputError className="mt-2" message={terminationForm.errors.termination_type} />
                            </div>
                            <div>
                                <InputLabel htmlFor="termination_reason" value="Reason" />
                                <textarea id="termination_reason" value={terminationForm.data.termination_reason} onChange={(event) => terminationForm.setData('termination_reason', event.target.value)} rows="4" className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-slate-400 focus:ring-slate-400" />
                                <InputError className="mt-2" message={terminationForm.errors.termination_reason} />
                            </div>
                            <DangerButton disabled={terminationForm.processing} className="rounded-2xl px-5 py-3 text-sm normal-case tracking-normal">
                                Record Separation
                            </DangerButton>
                        </form>
                    </section>

                    <section className="rounded-3xl border border-emerald-100 bg-white p-6 shadow-sm">
                        <h2 className="text-lg font-semibold text-slate-950">Rejoin Employee</h2>
                        <form onSubmit={rejoin} className="mt-6 space-y-4">
                            <DatePickerInput
                                id="last_rejoined_on"
                                label="Rejoin Date"
                                value={rejoinForm.data.last_rejoined_on}
                                onChange={(value) => rejoinForm.setData('last_rejoined_on', value)}
                                error={rejoinForm.errors.last_rejoined_on}
                            />
                            <div>
                                <InputLabel htmlFor="department" value="Department" />
                                <TextInput id="department" value={rejoinForm.data.department} onChange={(event) => rejoinForm.setData('department', event.target.value)} className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3" />
                                <InputError className="mt-2" message={rejoinForm.errors.department} />
                            </div>
                            <div>
                                <InputLabel htmlFor="designation" value="Designation" />
                                <TextInput id="designation" value={rejoinForm.data.designation} onChange={(event) => rejoinForm.setData('designation', event.target.value)} className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3" />
                                <InputError className="mt-2" message={rejoinForm.errors.designation} />
                            </div>
                            <div>
                                <InputLabel htmlFor="rejoin_notes" value="Notes" />
                                <textarea id="rejoin_notes" value={rejoinForm.data.notes} onChange={(event) => rejoinForm.setData('notes', event.target.value)} rows="3" className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-slate-400 focus:ring-slate-400" />
                                <InputError className="mt-2" message={rejoinForm.errors.notes} />
                            </div>
                            <PrimaryButton disabled={rejoinForm.processing} className="rounded-2xl bg-emerald-600 px-5 py-3 hover:bg-emerald-700">
                                Rejoin and Activate
                            </PrimaryButton>
                        </form>
                    </section>
                </aside>
            </div>
        </HRLayout>
    );
}
