import { useEffect, useState } from 'react';
import HRLayout from '@/Layouts/HRLayout';
import { Head, usePage } from '@inertiajs/react';
import AssignmentPanel from './AssignmentPanel';
import EmployeeTable from './EmployeeTable';
import FilterPanel from './FilterPanel';
import SummaryModal from './SummaryModal';

export default function Index({ employees, filters, options }) {
    const [selectedIds, setSelectedIds] = useState(() => new Set());
    const [showSummary, setShowSummary] = useState(() => false);
    const { flash } = usePage().props;
    const summary = flash?.bulk_assignment_summary;

    useEffect(() => {
        if (summary) {
            setShowSummary(true);
            setSelectedIds(new Set());
        }
    }, [summary]);

    return (
        <HRLayout
            heading="Bulk Shift & Weekly Off Assignment"
            subheading="Assign shift and weekly off schedule to multiple employees at once."
        >
            <Head title="Bulk Shift & Weekly Off Assignment" />

            <div className="grid gap-6 lg:grid-cols-3">
                <div className="space-y-6 lg:col-span-2">
                    <FilterPanel filters={filters} options={options} />

                    <EmployeeTable
                        employees={employees}
                        filters={filters}
                        selectedIds={selectedIds}
                        onSelectionChange={setSelectedIds}
                    />
                </div>

                <div className="lg:col-span-1">
                    <AssignmentPanel
                        options={options}
                        selectedCount={selectedIds.size}
                        selectedIds={selectedIds}
                    />
                </div>
            </div>

            {showSummary && summary && (
                <SummaryModal
                    summary={summary}
                    onClose={() => {
                        setShowSummary(false);
                        setSelectedIds(new Set());
                    }}
                />
            )}
        </HRLayout>
    );
}
