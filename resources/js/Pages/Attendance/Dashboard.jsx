import { Head, Link, useForm } from '@inertiajs/react';
import HRLayout from '@/Layouts/HRLayout';
import EmployeeLayout from '@/Layouts/EmployeeLayout';

const labels = { present:'Present Days', absent:'Absent Days', late:'Late Days', early_out:'Early-Out Days', late_early_out:'Late + Early-Out Days', half_day:'Half-Day Records', leave:'Leave Days', weekly_off:'Weekly-Off Days', holiday:'Holiday Days', missing_punch:'Missing-Punch Days', overtime_minutes:'Overtime Minutes' };
const statusColors = { present:'bg-emerald-500', absent:'bg-rose-500', late:'bg-amber-500', early_out:'bg-orange-500', late_early_out:'bg-orange-600', half_day:'bg-indigo-500', leave:'bg-sky-500', weekly_off:'bg-slate-400', holiday:'bg-purple-500', missing_punch:'bg-red-700' };

export default function Dashboard({ role='hr', period, definition, kpis={}, status_distribution={}, daily_trend=[], department_summary=[], overtime_summary={}, filters={}, options={}}) {
    const Layout = role === 'employee' ? EmployeeLayout : HRLayout;
    const form = useForm({ from: filters.from || period?.from || '', to: filters.to || period?.to || '', status: filters.status || '', employee: filters.employee || '' });
    const submit = e => { e.preventDefault(); form.get(role === 'employee' ? route('attendance.dashboard.own') : route('hr.attendance.dashboard.index'), { preserveState: true }); };
    const recordsUrl = status => role === 'employee' ? route('attendance.calendar.own') : route('hr.attendance.processing.index', { status, from: period?.from, to: period?.to });
    return <Layout heading="Attendance Dashboard" subheading="Analytics from processed attendance records only.">
        <Head title="Attendance Dashboard" />
        <div className="space-y-6">
            <form onSubmit={submit} className="grid gap-3 rounded-2xl border border-slate-200 bg-white p-5 md:grid-cols-5">
                <input className="input" type="date" value={form.data.from} onChange={e=>form.setData('from',e.target.value)} />
                <input className="input" type="date" value={form.data.to} onChange={e=>form.setData('to',e.target.value)} />
                {role === 'hr' && <select className="input" value={form.data.employee} onChange={e=>form.setData('employee',e.target.value)}><option value="">All employees</option>{options.employees?.map(e=><option key={e.id} value={e.id}>{e.name} ({e.employee_id})</option>)}</select>}
                <select className="input" value={form.data.status} onChange={e=>form.setData('status',e.target.value)}><option value="">All statuses</option>{options.statuses?.map(s=><option key={s} value={s}>{s.replaceAll('_',' ')}</option>)}</select>
                <button className="rounded-xl bg-slate-900 px-4 py-2 font-semibold text-white">Apply Filters</button>
            </form>
            <p className="text-xs text-slate-500">{definition}</p>
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
                {Object.entries(labels).map(([key,label]) => <Link key={key} href={key==='overtime_minutes' ? recordsUrl('') : recordsUrl(key)} className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-slate-400"><div className="text-xs font-semibold uppercase text-slate-500">{label}</div><div className="mt-2 text-2xl font-bold text-slate-900">{key==='overtime_minutes' ? Math.round((kpis[key]||0)/60*100)/100+' h' : (kpis[key+'_days'] ?? 0)}</div></Link>)}
            </div>
            <div className="grid gap-6 lg:grid-cols-2">
                <section className="rounded-2xl border bg-white p-5"><h2 className="font-semibold">Status Distribution</h2><div className="mt-4 space-y-3">{Object.entries(status_distribution).map(([key,value])=><div key={key}><div className="mb-1 flex justify-between text-sm"><span>{key.replaceAll('_',' ')}</span><span>{value}</span></div><div className="h-2 rounded bg-slate-100"><div className={`h-2 rounded ${statusColors[key]||'bg-slate-500'}`} style={{width:`${Math.min(100, value / Math.max(1, Math.max(...Object.values(status_distribution))) * 100)}%`}} /></div></div>)}</div></section>
                <section className="rounded-2xl border bg-white p-5"><h2 className="font-semibold">Daily Attendance Trend</h2><div className="mt-4 max-h-72 space-y-2 overflow-y-auto">{daily_trend.map(day=><div key={day.date} className="grid grid-cols-[7rem_1fr] items-center gap-3 text-xs"><span>{day.date}</span><div className="flex h-5 overflow-hidden rounded bg-slate-100">{[['present','bg-emerald-500'],['absent','bg-rose-500'],['leave','bg-sky-500'],['late','bg-amber-500'],['missing_punch','bg-red-700']].map(([key,color])=><div key={key} className={color} style={{width:`${day[key] / Math.max(1, Object.values(day).filter(v=>typeof v==='number').reduce((a,b)=>a+b,0))*100}%`}} />)}</div></div>)}</div></section>
            </div>
            <div className="grid gap-6 lg:grid-cols-[1fr_20rem]"><section className="overflow-x-auto rounded-2xl border bg-white p-5"><h2 className="font-semibold">Department Summary</h2><table className="mt-4 w-full text-left text-sm"><thead><tr className="border-b text-xs uppercase text-slate-500"><th className="p-2">Department</th><th className="p-2">Days</th><th className="p-2">Present</th><th className="p-2">Absent</th><th className="p-2">Leave</th><th className="p-2">Attendance %</th></tr></thead><tbody>{department_summary.map((row,i)=><tr className="border-b" key={i}><td className="p-2">{row.department}</td><td className="p-2">{row.total_days}</td><td className="p-2">{row.present_days}</td><td className="p-2">{row.absent_days}</td><td className="p-2">{row.leave_days}</td><td className="p-2">{row.attendance_percentage}%</td></tr>)}</tbody></table></section><section className="rounded-2xl border bg-white p-5"><h2 className="font-semibold">Overtime</h2><div className="mt-5 text-3xl font-bold">{overtime_summary.hours || 0} h</div><p className="mt-2 text-sm text-slate-500">{overtime_summary.employees || 0} employees with calculated overtime</p></section></div>
        </div>
    </Layout>;
}
