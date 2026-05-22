import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
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
import { Line, Bar, Doughnut } from 'react-chartjs-2';

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

export default function Dashboard() {
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'];

    const headcountData = {
        labels: months,
        datasets: [
            {
                label: 'Total Headcount',
                data: [120, 125, 130, 128, 135, 140, 145, 150, 155],
                borderColor: '#2563EB',
                backgroundColor: 'rgba(37,99,235,0.08)',
                tension: 0.3,
                pointRadius: 3,
            },
        ],
    };

    const hiresTerminationsData = {
        labels: months,
        datasets: [
            {
                label: 'Hires',
                data: [5, 8, 6, 7, 9, 4, 10, 6, 8],
                backgroundColor: '#10B981',
            },
            {
                label: 'Terminations',
                data: [2, 1, 3, 2, 1, 4, 2, 3, 1],
                backgroundColor: '#EF4444',
            },
        ],
    };

    const genderData = {
        labels: ['Male', 'Female', 'Other'],
        datasets: [
            {
                data: [80, 70, 5],
                backgroundColor: ['#6366F1', '#06B6D4', '#F59E0B'],
                hoverOffset: 6,
            },
        ],
    };

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top' },
            title: { display: false },
            tooltip: { mode: 'index', intersect: false },
        },
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
            }
        >
            <Head title="Dashboard" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="space-y-6">
                        {/* KPI Cards */}
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div className="rounded-lg bg-white p-4 shadow">
                                <div className="text-sm text-gray-500">Total Employees</div>
                                <div className="mt-2 flex items-baseline justify-between">
                                    <div className="text-2xl font-semibold text-gray-900">155</div>
                                    <div className="text-sm text-green-600">+4.5%</div>
                                </div>
                                <div className="text-xs text-gray-400">As of this month</div>
                            </div>

                            <div className="rounded-lg bg-white p-4 shadow">
                                <div className="text-sm text-gray-500">Active Employees</div>
                                <div className="mt-2 flex items-baseline justify-between">
                                    <div className="text-2xl font-semibold text-gray-900">140</div>
                                    <div className="text-sm text-green-600">+2.9%</div>
                                </div>
                                <div className="text-xs text-gray-400">Excluding on-leave</div>
                            </div>

                            <div className="rounded-lg bg-white p-4 shadow">
                                <div className="text-sm text-gray-500">On Leave</div>
                                <div className="mt-2 flex items-baseline justify-between">
                                    <div className="text-2xl font-semibold text-gray-900">8</div>
                                    <div className="text-sm text-red-600">-1</div>
                                </div>
                                <div className="text-xs text-gray-400">Currently on leave</div>
                            </div>

                            <div className="rounded-lg bg-white p-4 shadow">
                                <div className="text-sm text-gray-500">Open Positions</div>
                                <div className="mt-2 flex items-baseline justify-between">
                                    <div className="text-2xl font-semibold text-gray-900">6</div>
                                    <div className="text-sm text-yellow-600">+1</div>
                                </div>
                                <div className="text-xs text-gray-400">Recruiting</div>
                            </div>
                        </div>

                        {/* Charts Row */}
                        <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                            <div className="col-span-2 rounded-lg bg-white p-4 shadow" style={{ height: '360px' }}>
                                <h3 className="mb-2 text-sm font-medium text-gray-700">Headcount Trend</h3>
                                <Line data={headcountData} options={commonOptions} />
                            </div>

                            <div className="rounded-lg bg-white p-4 shadow" style={{ height: '360px' }}>
                                <h3 className="mb-2 text-sm font-medium text-gray-700">Gender Distribution</h3>
                                <div className="h-64">
                                    <Doughnut data={genderData} options={commonOptions} />
                                </div>
                            </div>
                        </div>

                        <div className="rounded-lg bg-white p-4 shadow" style={{ height: '360px' }}>
                            <h3 className="mb-2 text-sm font-medium text-gray-700">Hires vs Terminations (monthly)</h3>
                            <Bar data={hiresTerminationsData} options={commonOptions} />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
