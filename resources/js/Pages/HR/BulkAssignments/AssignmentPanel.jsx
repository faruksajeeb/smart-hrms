import { useForm } from '@inertiajs/react';
import DatePickerInput from '@/Components/DatePickerInput';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import SearchSelect from '@/Components/SearchSelect';

export default function AssignmentPanel({ options = {}, selectedIds = new Set() }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        shift_id: '',
        weekly_off_policy_id: '',
        effective_from: new Date().toISOString().split('T')[0],
        assignment_type: 'initial',
        remarks: '',
    });

    const submit = (event) => {
        event.preventDefault();

        post(route('hr.bulk-assignments.store'), {
            ...data,
            employee_ids: Array.from(selectedIds),
        }, {
            onSuccess: () => {
                reset('shift_id', 'weekly_off_policy_id', 'effective_from', 'assignment_type', 'remarks');
            },
        });
    };

    return (
        <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 className="text-lg font-semibold text-slate-950">Assignment Details</h2>
            <p className="mt-1 text-sm text-slate-500">
                {selectedIds.size > 0
                    ? `${selectedIds.size} employee${selectedIds.size !== 1 ? 's' : ''} selected. Assign shift and weekly off schedule.`
                    : 'Select employees from the table first.'}
            </p>

            <form onSubmit={submit} className="mt-6 space-y-4">
                <SearchSelect
                    id="shift_id"
                    label="Shift"
                    value={data.shift_id}
                    onChange={(value) => setData('shift_id', value)}
                    options={options.shifts ?? []}
                    error={errors.shift_id}
                    placeholder="Search shift..."
                />

                <SearchSelect
                    id="weekly_off_policy_id"
                    label="Weekly Off Policy"
                    value={data.weekly_off_policy_id}
                    onChange={(value) => setData('weekly_off_policy_id', value)}
                    options={options.weeklyOffPolicies ?? []}
                    error={errors.weekly_off_policy_id}
                    placeholder="Search weekly off policy..."
                />

                <DatePickerInput
                    id="effective_from"
                    label="Effective From"
                    value={data.effective_from}
                    onChange={(value) => setData('effective_from', value)}
                    error={errors.effective_from}
                />

                <div>
                    <InputLabel htmlFor="assignment_type" value="Assignment Type" />
                    <select
                        id="assignment_type"
                        value={data.assignment_type}
                        onChange={(event) => setData('assignment_type', event.target.value)}
                        className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-slate-400 focus:ring-slate-400"
                    >
                        <option value="initial">Initial</option>
                        <option value="transfer">Transfer</option>
                        <option value="promotion">Promotion</option>
                        <option value="temporary">Temporary</option>
                        <option value="manual">Manual</option>
                    </select>
                    <InputError className="mt-2" message={errors.assignment_type} />
                </div>

                <div>
                    <InputLabel htmlFor="remarks" value="Remarks" />
                    <textarea
                        id="remarks"
                        value={data.remarks}
                        onChange={(event) => setData('remarks', event.target.value)}
                        rows="3"
                        className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-slate-400 focus:ring-slate-400"
                        placeholder="Optional remarks for this bulk assignment..."
                    />
                    <InputError className="mt-2" message={errors.remarks} />
                </div>

                <PrimaryButton
                    type="submit"
                    disabled={processing || selectedIds.size === 0}
                    className="w-full rounded-2xl bg-slate-950 px-5 py-3 hover:bg-slate-800 disabled:opacity-50"
                >
                    {processing ? 'Assigning...' : `Assign to ${selectedIds.size} Employee${selectedIds.size !== 1 ? 's' : ''}`}
                </PrimaryButton>
            </form>
        </section>
    );
}
