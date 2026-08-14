import { Head, router } from '@inertiajs/react';
import HRLayout from '@/Layouts/HRLayout';
import { useMemo, useState } from 'react';

const labels = { employees: 'Employees', leave_types: 'Leave Types', closing_balance: 'Total Closing Balance', carry_forward: 'Total Carry Forward', expired: 'Total Expired', encashment: 'Total Encashment' };

export default function Index({ filters = {}, years = [], options = {}, recentProcesses = [] }) {
    const [form, setForm] = useState({ processing_year: filters.processing_year || new Date().getFullYear(), target_year: filters.target_year || new Date().getFullYear() + 1, auto_encashment: false, ...filters });
    const [preview, setPreview] = useState(null); const [busy, setBusy] = useState(false); const [error, setError] = useState('');
    const set = (key, value) => setForm((current) => ({ ...current, [key]: value }));
    const child = (key, parent) => (options[key] || []).filter((item) => !parent || String(item.parent_id || '') === String(parent));
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;
    const request = async (url) => { setBusy(true); setError(''); try { const response = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() }, body: JSON.stringify(form) }); const data = await response.json(); if (!response.ok) throw new Error(data.message || Object.values(data.errors || {}).flat().join(' ') || 'Request failed.'); return data; } catch (e) { setError(e.message); } finally { setBusy(false); } };
    const doPreview = async (e) => { e.preventDefault(); setPreview(await request(route('hr.leave.year-end.preview'))); };
    const doProcess = async () => { if (!window.confirm('Create the year-end ledger transactions for this preview?')) return; const data = await request(route('hr.leave.year-end.process')); if (data?.id) router.visit(route('hr.leave.year-end.show', data.id)); };
    const summary = preview?.summary || {};
    return <HRLayout heading="Leave Year-End Processing" subheading="Review and post policy-driven carry forward, expiry, and encashment transactions.">
        <Head title="Leave Year-End Processing" />
        <div className="space-y-6">
            <form onSubmit={doPreview} className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Field label="Processing Year"><select value={form.processing_year} onChange={(e) => { set('processing_year', e.target.value); set('target_year', Number(e.target.value) + 1); }} className="input">{years.map((year) => <option key={year}>{year}</option>)}</select></Field>
                    <Field label="Target Year"><select value={form.target_year} onChange={(e) => set('target_year', e.target.value)} className="input">{years.map((year) => <option key={year}>{year}</option>)}</select></Field>
                    <Select label="Company" value={form.company_id} onChange={(v) => { set('company_id', v); set('branch_id', ''); }} options={options.companies} />
                    <Select label="Branch" value={form.branch_id} onChange={(v) => set('branch_id', v)} options={child('branches', form.company_id)} />
                    <Select label="Division" value={form.division_id} onChange={(v) => set('division_id', v)} options={child('divisions', form.branch_id || form.company_id)} />
                    <Select label="Department" value={form.department_id} onChange={(v) => set('department_id', v)} options={child('departments', form.division_id || form.branch_id || form.company_id)} />
                    <Select label="Section" value={form.section_id} onChange={(v) => set('section_id', v)} options={child('sections', form.department_id || form.division_id || form.branch_id || form.company_id)} />
                    <Select label="Unit" value={form.unit_id} onChange={(v) => set('unit_id', v)} options={child('units', form.section_id || form.department_id || form.division_id || form.branch_id || form.company_id)} />
                    <Select label="Leave Type" value={form.leave_type_id} onChange={(v) => set('leave_type_id', v)} options={options.leaveTypes} leave />
                    <Field label="Employee"><input value={form.employee || ''} onChange={(e) => set('employee', e.target.value)} placeholder="Name or employee ID" className="input" /></Field>
                    <label className="flex items-end gap-2 pb-2 text-sm text-slate-700"><input type="checkbox" checked={!!form.auto_encashment} onChange={(e) => set('auto_encashment', e.target.checked)} /> Enable policy-allowed automatic encashment</label>
                </div>
                <div className="mt-6 flex items-center gap-3"><button disabled={busy} className="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white disabled:opacity-50">{busy ? 'Calculating...' : 'Preview'}</button>{preview && <button type="button" disabled={busy} onClick={doProcess} className="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white disabled:opacity-50">Start Processing</button>}</div>
                {error && <p className="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-700">{error}</p>}
            </form>
            {preview && <><div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">{Object.entries(labels).map(([key, label]) => <div key={key} className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p className="text-sm text-slate-500">{label}</p><p className="mt-2 text-2xl font-semibold text-slate-900">{Number(summary[key] || 0).toFixed(key === 'employees' || key === 'leave_types' ? 0 : 2)}</p></div>)}</div><div className="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"><div className="border-b border-slate-200 p-5"><h2 className="font-semibold text-slate-900">Read-only Preview: {preview.processing_year} → {preview.target_year}</h2></div><div className="overflow-x-auto"><table className="w-full text-left text-sm"><thead className="bg-slate-50 text-slate-600"><tr>{['Employee', 'Leave Type', 'Closing Balance', 'Carry Forward', 'Expiry', 'Encashment', 'Final Balance'].map((h) => <th key={h} className="px-4 py-3 font-medium">{h}</th>)}</tr></thead><tbody className="divide-y divide-slate-100">{preview.rows.map((row) => <tr key={`${row.user_id}-${row.leave_type_id}`}><td className="px-4 py-3"><b>{row.employee_name}</b><div className="text-xs text-slate-500">{row.employee_id}</div></td><td className="px-4 py-3">{row.leave_type}<div className="text-xs text-slate-500">{row.policy || 'No policy'}</div></td><td className="px-4 py-3">{Number(row.previous_balance).toFixed(2)}</td><td className="px-4 py-3 text-emerald-700">{Number(row.eligible_carry_forward).toFixed(2)}</td><td className="px-4 py-3 text-amber-700">{Number(row.expired_days).toFixed(2)}</td><td className="px-4 py-3 text-sky-700">{Number(row.encashment_days).toFixed(2)}</td><td className="px-4 py-3 font-semibold">{Number(row.closing_balance).toFixed(2)}</td></tr>)}</tbody></table></div>{!preview.rows.length && <p className="p-8 text-center text-sm text-slate-500">No eligible balances found for the selected scope.</p>}</div></>}
            {recentProcesses.length > 0 && <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><h2 className="font-semibold text-slate-900">Recent Year-End Processes</h2><div className="mt-4 space-y-2">{recentProcesses.map((process) => <a key={process.id} href={route('hr.leave.year-end.show', process.id)} className="flex justify-between rounded-xl border border-slate-100 p-3 text-sm hover:bg-slate-50"><span>{process.processing_year} → {process.target_year}</span><span className="capitalize text-slate-500">{process.status} · {process.items_count} items</span></a>)}</div></div>}
        </div>
    </HRLayout>;
}
function Field({ label, children }) { return <div><label className="block text-sm font-medium text-slate-700">{label}</label><div className="mt-2">{children}</div></div>; }
function Select({ label, value, onChange, options = [], leave }) { return <Field label={label}><select value={value || ''} onChange={(e) => onChange(e.target.value)} className="input"><option value="">All {label}s</option>{options.map((item) => <option key={item.id} value={item.id}>{leave ? item.leave_name : item.name}</option>)}</select></Field>; }
