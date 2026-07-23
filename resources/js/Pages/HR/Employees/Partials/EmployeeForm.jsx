import DatePickerInput from '@/Components/DatePickerInput';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SearchSelect from '@/Components/SearchSelect';
import TextInput from '@/Components/TextInput';
import { useEffect, useState } from 'react';

const IMAGE_EXTENSION = /\.(jpe?g|png|gif|webp|bmp)$/i;

function DocumentPreview({ file, src, mimeType }) {
    const [preview, setPreview] = useState(null);

    useEffect(() => {
        if (file instanceof File && file.type.startsWith('image/')) {
            const url = URL.createObjectURL(file);
            setPreview(url);
            return () => URL.revokeObjectURL(url);
        }

        if (mimeType?.startsWith('image/') || (src && IMAGE_EXTENSION.test(src))) {
            setPreview(src);
            return;
        }

        setPreview(null);
    }, [file, src, mimeType]);

    if (!preview) {
        return null;
    }

    return (
        <div className="mt-3">
            <p className="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Preview</p>
            <img
                src={preview}
                alt="Selected document preview"
                className="mt-2 max-h-48 w-full rounded-xl border border-slate-200 bg-white object-contain"
            />
        </div>
    );
}

function TextField({ id, label, type = 'text', value, onChange, error, placeholder }) {
    return (
        <div>
            <InputLabel htmlFor={id} value={label} />
            <TextInput
                id={id}
                type={type}
                value={value ?? ''}
                placeholder={placeholder}
                className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3"
                onChange={(event) => onChange(event.target.value)}
            />
            <InputError className="mt-2" message={error} />
        </div>
    );
}

export default function EmployeeForm({
    data,
    setData,
    errors,
    processing,
    options,
    submitLabel,
    isEdit = false,
    documents = [],
}) {
    const masterData = options.masterData ?? {};
    const existingDocuments = Object.fromEntries(
        (documents ?? []).map((document) => [document.type, document]),
    );
    const [cvFile, setCvFile] = useState(null);
    const [cvLoading, setCvLoading] = useState(false);
    const [cvMessage, setCvMessage] = useState('');
    const [cvError, setCvError] = useState('');

    const selectedShift = (options.shifts ?? []).find((s) => String(s.id) === String(data.shift_id)) ?? null;

    const DAY_NAMES = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    const selectedWeeklyOff = (options.weeklyOffPolicies ?? []).find((p) => String(p.id) === String(data.weekly_off_policy_id)) ?? null;
    const weeklyOffDayNames = (selectedWeeklyOff?.days ?? []).map((d) => DAY_NAMES[d] ?? `Day ${d}`).join(', ');

    const selectedManager = (options.managers ?? []).find((m) => String(m.id) === String(data.manager_id)) ?? null;

    const applyCvData = (payload) => {
        Object.entries(payload.data ?? {}).forEach(([field, value]) => {
            if (value !== null && value !== undefined && value !== '' && field in data) {
                setData(field, value);
            }
        });

        Object.entries(payload.master_data ?? {}).forEach(([field, option]) => {
            if (option?.id) {
                setData(field, option.id);
            }
        });
    };

    const extractCv = async () => {
        if (!cvFile) {
            setCvError('Choose a PDF, DOCX, TXT, or MD CV first.');
            return;
        }

        setCvLoading(true);
        setCvError('');
        setCvMessage('Reading CV and finding relevant fields...');

        const body = new FormData();
        body.append('cv', cvFile);

        try {
            const response = await fetch(route('hr.employees.cv-extract'), {
                method: 'POST',
                body,
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
            });
            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload.message ?? payload.errors?.cv?.[0] ?? payload.errors?.ai_provider?.[0] ?? 'CV analysis failed.');
            }

            applyCvData(payload);
            setCvMessage('CV fields were suggested. Review them before saving the employee.');
        } catch (error) {
            setCvMessage('');
            setCvError(error.message);
        } finally {
            setCvLoading(false);
        }
    };

    return (
        <div className="space-y-6">

            
            {!isEdit && (
                <section className="rounded-3xl border border-sky-200 bg-sky-50 p-6 shadow-sm">
                    <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 className="text-lg font-semibold text-slate-950">CV Auto-fill</h2>
                            <p className="mt-1 text-sm text-slate-600">
                                Upload a CV and review the extracted suggestions before submitting onboarding.
                            </p>
                        </div>
                        <span className="rounded-full bg-white px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-sky-700">
                            Optional
                        </span>
                    </div>

                    <div className="mt-6 flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div className="flex-1">
                            <InputLabel htmlFor="cv" value="CV / Resume" />
                            <input
                                id="cv"
                                type="file"
                                accept=".pdf,.docx,.txt,.md,.jpg,.jpeg,.png"
                                onChange={(event) => {
                                    const file = event.target.files?.[0] ?? null;
                                    setCvFile(file);
                                    setData('document_cv', file);
                                }}
                                className="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600"
                            />
                            <DocumentPreview file={cvFile} />
                        </div>
                        <button
                            type="button"
                            disabled={cvLoading}
                            onClick={extractCv}
                            className="rounded-2xl bg-sky-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-sky-800 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {cvLoading ? 'Reading CV...' : 'Read CV & Auto-fill'}
                        </button>
                    </div>
                    {cvMessage && <p className="mt-3 text-sm font-medium text-emerald-700">{cvMessage}</p>}
                    {cvError && <p className="mt-3 text-sm font-medium text-rose-700">{cvError}</p>}
                    <div className="mt-4 grid gap-3 sm:grid-cols-2">
                        <div>
                            <InputLabel htmlFor="document_cv_expiry_date" value="CV Expiry Date" />
                            <TextInput id="document_cv_expiry_date" type="date" value={data.document_cv_expiry_date ?? ''} onChange={(event) => setData('document_cv_expiry_date', event.target.value)} className="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3" />
                            <InputError className="mt-2" message={errors.document_cv_expiry_date} />
                        </div>
                        <div>
                            <InputLabel htmlFor="document_cv_remarks" value="CV Remarks" />
                            <TextInput id="document_cv_remarks" value={data.document_cv_remarks ?? ''} onChange={(event) => setData('document_cv_remarks', event.target.value)} className="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3" />
                            <InputError className="mt-2" message={errors.document_cv_remarks} />
                        </div>
                    </div>
                </section>
            )}

            <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 className="text-lg font-semibold text-slate-950">Employee Documents</h2>
                <p className="mt-1 text-sm text-slate-500">
                    Upload employee documents now or add them later from the edit form.
                </p>
                <div className="mt-6 grid gap-5 md:grid-cols-2">
                    {isEdit && (
                        <div className="rounded-2xl border border-slate-100 bg-slate-50 p-4 md:col-span-2">
                            <InputLabel htmlFor="document_cv" value="CV / Resume" />
                            <input id="document_cv" type="file" accept=".pdf,.docx,.txt,.md" onChange={(event) => setData('document_cv', event.target.files?.[0] ?? null)} className="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600" />
                            <InputError className="mt-2" message={errors.document_cv} />
                            <DocumentPreview
                                file={data.document_cv}
                                src={existingDocuments.cv?.download_url}
                                mimeType={existingDocuments.cv?.mime_type}
                            />
                        </div>
                    )}
                    {[
                        ['photo', 'Profile Photo', '.jpg,.jpeg,.png'],
                        ['nid', 'NID', '.pdf,.jpg,.jpeg,.png'],
                        ['passport', 'Passport', '.pdf,.jpg,.jpeg,.png'],
                        ['certificates', 'Certificates', '.pdf,.jpg,.jpeg,.png,.docx'],
                        ['appointment_letter', 'Appointment Letter', '.pdf,.docx'],
                        ['joining_letter', 'Joining Letter', '.pdf,.docx'],
                    ].map(([type, label, accept]) => (
                        <div key={type} className="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <InputLabel htmlFor={`document_${type}`} value={label} />
                            <input
                                id={`document_${type}`}
                                type="file"
                                accept={accept}
                                onChange={(event) => setData(`document_${type}`, event.target.files?.[0] ?? null)}
                                className="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600"
                            />
                            <InputError className="mt-2" message={errors[`document_${type}`]} />
                            <DocumentPreview
                                file={data[`document_${type}`]}
                                src={existingDocuments[type]?.download_url}
                                mimeType={existingDocuments[type]?.mime_type}
                            />
                            <div className="mt-3 grid gap-3 sm:grid-cols-2">
                                <div>
                                    <InputLabel htmlFor={`document_${type}_expiry_date`} value="Expiry Date" />
                                    <TextInput id={`document_${type}_expiry_date`} type="date" value={data[`document_${type}_expiry_date`] ?? ''} onChange={(event) => setData(`document_${type}_expiry_date`, event.target.value)} className="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3" />
                                    <InputError className="mt-2" message={errors[`document_${type}_expiry_date`]} />
                                </div>
                                <div>
                                    <InputLabel htmlFor={`document_${type}_remarks`} value="Remarks" />
                                    <TextInput id={`document_${type}_remarks`} value={data[`document_${type}_remarks`] ?? ''} onChange={(event) => setData(`document_${type}_remarks`, event.target.value)} className="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-3" />
                                    <InputError className="mt-2" message={errors[`document_${type}_remarks`]} />
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </section>
            
            <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 className="text-lg font-semibold text-slate-950">Onboarding</h2>
                        <p className="mt-1 text-sm text-slate-500">
                            Create the account, assign employee identity, and tag master data for org setup.
                        </p>
                    </div>
                    <span className="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">
                        Employee Role
                    </span>
                </div>

                <div className="mt-8 grid gap-5 md:grid-cols-2">
                    <TextField id="name" label="Full Name" value={data.name} onChange={(value) => setData('name', value)} error={errors.name} />
                    <TextField id="email" label="Work Email" type="email" value={data.email} onChange={(value) => setData('email', value)} error={errors.email} />
                    <TextField id="employee_id" label="Employee ID" value={data.employee_id} onChange={(value) => setData('employee_id', value)} error={errors.employee_id} />
                    <SearchSelect id="employment_status" label="Employment Status" value={data.employment_status} options={options.employmentStatuses} onChange={(value) => setData('employment_status', value)} error={errors.employment_status} placeholder="Search employment status..." isClearable={false} />
                    <SearchSelect id="company_master_data_id" label="Company" value={data.company_master_data_id} options={masterData.company_master_data_id} onChange={(value) => setData('company_master_data_id', value)} error={errors.company_master_data_id} placeholder="Search company..." />
                    <SearchSelect id="branch_master_data_id" label="Branch / Work Location" value={data.branch_master_data_id} options={masterData.branch_master_data_id} onChange={(value) => setData('branch_master_data_id', value)} error={errors.branch_master_data_id} placeholder="Search branch..." />
                    <SearchSelect id="division_master_data_id" label="Division" value={data.division_master_data_id} options={masterData.division_master_data_id} onChange={(value) => setData('division_master_data_id', value)} error={errors.division_master_data_id} placeholder="Search division..." />
                    <SearchSelect id="department_master_data_id" label="Department" value={data.department_master_data_id} options={masterData.department_master_data_id} onChange={(value) => setData('department_master_data_id', value)} error={errors.department_master_data_id} placeholder="Search department..." />
                    <SearchSelect id="designation_master_data_id" label="Designation" value={data.designation_master_data_id} options={masterData.designation_master_data_id} onChange={(value) => setData('designation_master_data_id', value)} error={errors.designation_master_data_id} placeholder="Search designation..." />
                    <SearchSelect id="employment_type_master_data_id" label="Employment Type" value={data.employment_type_master_data_id} options={masterData.employment_type_master_data_id} onChange={(value) => setData('employment_type_master_data_id', value)} error={errors.employment_type_master_data_id} placeholder="Search employment type..." />
                    <SearchSelect id="job_grade_master_data_id" label="Job Grade" value={data.job_grade_master_data_id} options={masterData.job_grade_master_data_id} onChange={(value) => setData('job_grade_master_data_id', value)} error={errors.job_grade_master_data_id} placeholder="Search job grade..." />
                    <DatePickerInput id="joining_date" label="Joining Date" value={data.joining_date} onChange={(value) => setData('joining_date', value)} error={errors.joining_date} />
                    <TextField id="emergency_contact_name" label="Emergency Contact Name" value={data.emergency_contact_name} onChange={(value) => setData('emergency_contact_name', value)} error={errors.emergency_contact_name} />
                    <TextField id="emergency_contact_phone" label="Emergency Contact Phone" value={data.emergency_contact_phone} onChange={(value) => setData('emergency_contact_phone', value)} error={errors.emergency_contact_phone} />
                    <div className="grid gap-5 sm:grid-cols-2 md:col-span-2">
                        <TextField id="password" label={isEdit ? 'New Password' : 'Temporary Password'} type="password" value={data.password} onChange={(value) => setData('password', value)} error={errors.password} />
                        <TextField id="password_confirmation" label="Confirm Password" type="password" value={data.password_confirmation} onChange={(value) => setData('password_confirmation', value)} />
                    </div>
                </div>
            </section>

            {!isEdit && (
                <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 className="text-lg font-semibold text-slate-950">Attendance Setup</h2>
                    <p className="mt-1 text-sm text-slate-500">
                        Assign shift and weekly off schedule for attendance tracking.
                    </p>
                    <div className="mt-6 grid gap-5 md:grid-cols-2">
                        <div>
                            <SearchSelect id="shift_id" label="Shift" value={data.shift_id} options={options.shifts} onChange={(value) => setData('shift_id', value)} error={errors.shift_id} placeholder="Search shift..." />
                            {selectedShift && (
                                <div className="mt-3 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-400">Shift Details</p>
                                    <div className="mt-2 space-y-1 text-sm text-slate-600">
                                        <p><span className="font-medium">Time:</span> {selectedShift.start_time} - {selectedShift.end_time}</p>
                                        <p><span className="font-medium">Hours:</span> {selectedShift.working_hours}h</p>
                                        <p><span className="font-medium">Grace:</span> {selectedShift.grace_time} min</p>
                                        {selectedShift.is_flexible ? <p className="text-emerald-700">Flexible shift</p> : null}
                                    </div>
                                </div>
                            )}
                        </div>
                        <div>
                            <SearchSelect id="weekly_off_policy_id" label="Weekly Off" value={data.weekly_off_policy_id} options={options.weeklyOffPolicies} onChange={(value) => setData('weekly_off_policy_id', value)} error={errors.weekly_off_policy_id} placeholder="Search weekly off policy..." />
                            {selectedWeeklyOff && (
                                <div className="mt-3 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-400">Weekly Off Details</p>
                                    <div className="mt-2 space-y-1 text-sm text-slate-600">
                                        {selectedWeeklyOff.description ? <p>{selectedWeeklyOff.description}</p> : null}
                                        <p><span className="font-medium">Off Days:</span> {weeklyOffDayNames}</p>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </section>
            )}

            <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 className="text-lg font-semibold text-slate-950">Reporting Manager</h2>
                <p className="mt-1 text-sm text-slate-500">
                    Assign a reporting manager for this employee.
                </p>
                <div className="mt-6 grid gap-5 md:grid-cols-2">
                    <div>
                        <SearchSelect id="manager_id" label="Reporting Manager" value={data.manager_id} options={options.managers} onChange={(value) => setData('manager_id', value)} error={errors.manager_id} placeholder="Search manager..." />
                        {selectedManager && (
                            <div className="mt-3 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                <p className="text-xs font-semibold uppercase tracking-wide text-slate-400">Manager Details</p>
                                <div className="mt-2 space-y-1 text-sm text-slate-600">
                                    <p><span className="font-medium">ID:</span> {selectedManager.employee_id ?? '-'}</p>
                                    <p><span className="font-medium">Name:</span> {selectedManager.label}</p>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 className="text-lg font-semibold text-slate-950">Personal Information</h2>
                <div className="mt-6 grid gap-5 md:grid-cols-2">
                    <TextField id="phone" label="Phone" value={data.phone} onChange={(value) => setData('phone', value)} error={errors.phone} />
                    <DatePickerInput id="date_of_birth" label="Date of Birth" value={data.date_of_birth} onChange={(value) => setData('date_of_birth', value)} error={errors.date_of_birth} />
                    <TextField id="nationality" label="Nationality" value={data.nationality} onChange={(value) => setData('nationality', value)} error={errors.nationality} />
                    <SearchSelect id="religion_master_data_id" label="Religion" value={data.religion_master_data_id} options={masterData.religion_master_data_id} onChange={(value) => setData('religion_master_data_id', value)} error={errors.religion_master_data_id} placeholder="Search religion..." />
                    <SearchSelect id="blood_group_master_data_id" label="Blood Group" value={data.blood_group_master_data_id} options={masterData.blood_group_master_data_id} onChange={(value) => setData('blood_group_master_data_id', value)} error={errors.blood_group_master_data_id} placeholder="Search blood group..." />
                    <SearchSelect id="marital_status_master_data_id" label="Marital Status" value={data.marital_status_master_data_id} options={masterData.marital_status_master_data_id} onChange={(value) => setData('marital_status_master_data_id', value)} error={errors.marital_status_master_data_id} placeholder="Search marital status..." />
                    <SearchSelect id="qualification_master_data_id" label="Qualification" value={data.qualification_master_data_id} options={masterData.qualification_master_data_id} onChange={(value) => setData('qualification_master_data_id', value)} error={errors.qualification_master_data_id} placeholder="Search qualification..." />
                    <div className="md:col-span-2">
                        <InputLabel htmlFor="address" value="Address" />
                        <textarea id="address" value={data.address ?? ''} onChange={(event) => setData('address', event.target.value)} rows="3" className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-slate-400 focus:ring-slate-400" />
                        <InputError className="mt-2" message={errors.address} />
                    </div>
                    <div>
                        <InputLabel htmlFor="skills" value="Skills" />
                        <textarea id="skills" value={data.skills ?? ''} onChange={(event) => setData('skills', event.target.value)} rows="3" className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-slate-400 focus:ring-slate-400" />
                        <InputError className="mt-2" message={errors.skills} />
                    </div>
                    <div>
                        <InputLabel htmlFor="experience_summary" value="Experience Summary" />
                        <textarea id="experience_summary" value={data.experience_summary ?? ''} onChange={(event) => setData('experience_summary', event.target.value)} rows="3" className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-slate-400 focus:ring-slate-400" />
                        <InputError className="mt-2" message={errors.experience_summary} />
                    </div>
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 className="text-lg font-semibold text-slate-950">Probation Setup</h2>
                <div className="mt-6 grid gap-5 md:grid-cols-4">
                    <DatePickerInput id="probation_starts_on" label="Starts On" value={data.probation_starts_on} onChange={(value) => setData('probation_starts_on', value)} error={errors.probation_starts_on} />
                    <DatePickerInput id="probation_ends_on" label="Ends On" value={data.probation_ends_on} onChange={(value) => setData('probation_ends_on', value)} error={errors.probation_ends_on} minDate={data.probation_starts_on ? new Date(`${data.probation_starts_on}T00:00:00`) : undefined} />
                    <SearchSelect id="probation_status" label="Probation Status" value={data.probation_status} options={options.probationStatuses} onChange={(value) => setData('probation_status', value)} error={errors.probation_status} placeholder="Search probation status..." isClearable={false} />
                    <DatePickerInput id="confirmation_date" label="Confirmation Date" value={data.confirmation_date} onChange={(value) => setData('confirmation_date', value)} error={errors.confirmation_date} />
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 className="text-lg font-semibold text-slate-950">Leave Setup</h2>
                <div className="mt-6 grid gap-5 md:grid-cols-5">
                    <TextField id="leave_policy_name" label="Policy Name" value={data.leave_policy_name} onChange={(value) => setData('leave_policy_name', value)} error={errors.leave_policy_name} placeholder="Standard Leave" />
                    <TextField id="annual_leave_days" label="Annual" type="number" value={data.annual_leave_days} onChange={(value) => setData('annual_leave_days', value)} error={errors.annual_leave_days} />
                    <TextField id="sick_leave_days" label="Sick" type="number" value={data.sick_leave_days} onChange={(value) => setData('sick_leave_days', value)} error={errors.sick_leave_days} />
                    <TextField id="casual_leave_days" label="Casual" type="number" value={data.casual_leave_days} onChange={(value) => setData('casual_leave_days', value)} error={errors.casual_leave_days} />
                    <TextField id="carry_forward_leave_days" label="Carry Forward" type="number" value={data.carry_forward_leave_days} onChange={(value) => setData('carry_forward_leave_days', value)} error={errors.carry_forward_leave_days} />
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 className="text-lg font-semibold text-slate-950">Salary Setup</h2>
                <div className="mt-6 grid gap-5 md:grid-cols-3">
                    <TextField id="salary_amount" label="Salary Amount" type="number" value={data.salary_amount} onChange={(value) => setData('salary_amount', value)} error={errors.salary_amount} />
                    <TextField id="salary_currency" label="Currency" value={data.salary_currency} onChange={(value) => setData('salary_currency', value.toUpperCase())} error={errors.salary_currency} />
                    <SearchSelect id="pay_type_master_data_id" label="Pay Type" value={data.pay_type_master_data_id} options={masterData.pay_type_master_data_id} onChange={(value) => setData('pay_type_master_data_id', value)} error={errors.pay_type_master_data_id} placeholder="Search pay type..." />
                    <SearchSelect id="bank_master_data_id" label="Bank" value={data.bank_master_data_id} options={masterData.bank_master_data_id} onChange={(value) => setData('bank_master_data_id', value)} error={errors.bank_master_data_id} placeholder="Search bank..." />
                    <TextField id="bank_account_number" label="Bank Account" value={data.bank_account_number} onChange={(value) => setData('bank_account_number', value)} error={errors.bank_account_number} />
                    <TextField id="tax_identifier" label="Tax Identifier" value={data.tax_identifier} onChange={(value) => setData('tax_identifier', value)} error={errors.tax_identifier} />
                </div>
            </section>

            <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <InputLabel htmlFor="notes" value="Other Notes" />
                <textarea
                    id="notes"
                    value={data.notes ?? ''}
                    onChange={(event) => setData('notes', event.target.value)}
                    rows="4"
                    className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-slate-400 focus:ring-slate-400"
                />
                <InputError className="mt-2" message={errors.notes} />

                <div className="mt-6">
                    <PrimaryButton disabled={processing} className="rounded-2xl bg-slate-950 px-5 py-3">
                        {processing ? 'Saving employee...' : submitLabel}
                    </PrimaryButton>
                </div>
            </section>
        </div>
    );
}
