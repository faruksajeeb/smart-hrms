import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';
import { Bar, Doughnut, Line } from 'react-chartjs-2';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend
);

const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'];

const commonOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top' },
        tooltip: { mode: 'index', intersect: false },
    },
};

const headcountData = {
    labels: months,
    datasets: [
        {
            label: 'Headcount',
            data: [120, 124, 128, 132, 136, 140, 145, 148, 155],
            borderColor: '#2563EB',
            backgroundColor: 'rgba(37,99,235,0.12)',
            tension: 0.35,
            pointRadius: 3,
        },
    ],
};

const hiresTerminationsData = {
    labels: months,
    datasets: [
        {
            label: 'Hires',
            data: [4, 7, 6, 10, 8, 5, 9, 7, 12],
            backgroundColor: '#10B981',
        },
        {
            label: 'Terminations',
            data: [1, 2, 3, 2, 2, 1, 2, 4, 2],
            backgroundColor: '#EF4444',
        },
    ],
};

const engagementData = {
    labels: ['Male', 'Female', 'Other'],
    datasets: [
        {
            data: [78, 65, 7],
            backgroundColor: ['#6366F1', '#06B6D4', '#FBBF24'],
            hoverOffset: 6,
        },
    ],
};

export default function DashboardCharts() {
    return (
        <div className="space-y-6">
            {/* <div className="grid gap-6 lg:grid-cols-3">
                <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p className="text-sm font-medium text-slate-500">Workforce Trend</p>
                    <p className="mt-3 text-3xl font-semibold text-slate-950">155</p>
                    <p className="mt-2 text-sm text-slate-500">Total employees this month</p>
                </div>
                <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p className="text-sm font-medium text-slate-500">New Hires</p>
                    <p className="mt-3 text-3xl font-semibold text-slate-950">12</p>
                    <p className="mt-2 text-sm text-slate-500">This month</p>
                </div>
                <div className="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p className="text-sm font-medium text-slate-500">Retention</p>
                    <p className="mt-3 text-3xl font-semibold text-slate-950">95%</p>
                    <p className="mt-2 text-sm text-slate-500">Monthly retention rate</p>
                </div>
            </div> */}

            <div className="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
                <section className="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h3 className="mb-4 text-base font-semibold text-slate-900">Headcount Trend</h3>
                    <div className="h-[340px] w-full">
                        <Line data={headcountData} options={commonOptions} />
                    </div>
                </section>

                <section className="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h3 className="mb-4 text-base font-semibold text-slate-900">Gender Breakdown</h3>
                    <div className="h-[340px] w-full">
                        <Doughnut data={engagementData} options={commonOptions} />
                    </div>
                </section>
            </div>

            <section className="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                <h3 className="mb-4 text-base font-semibold text-slate-900">Hires vs Terminations</h3>
                <div className="h-[340px] w-full">
                    <Bar data={hiresTerminationsData} options={commonOptions} />
                </div>
            </section>
        </div>
    );
}
