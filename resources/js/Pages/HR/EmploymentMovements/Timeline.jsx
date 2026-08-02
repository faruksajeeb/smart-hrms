import { Link } from '@inertiajs/react';
import { useState } from 'react';
import { getEmploymentMovementTypeLabel, employmentMovementTypeOptions } from '@/Enums/EmploymentMovementType';

export default function Timeline({ employee, history, filters = {}, eventTypes = [] }) {
    const [movementType, setMovementType] = useState(filters.movement_type ?? '');

    const filteredHistory = history.filter((item) => {
        if (!movementType) return true;
        return item.event_type === movementType;
    });
    const fieldLabels = {
        company_id: 'Company',
        branch_id: 'Branch',
        cluster_id: 'Cluster',
        division_id: 'Division',
        department_id: 'Department',
        section_id: 'Section',
        unit_id: 'Unit',
        designation_id: 'Designation',
        employment_type_id: 'Employment Type',
        reporting_manager_id: 'Reporting Manager',
    };

    const renderChanges = (item) => {
        if (!item.changes || item.changes.length === 0) {
            return (
                <p className="mt-2 text-sm text-slate-600">
                    No field changes recorded.
                </p>
            );
        }

        return (
            <div className="mt-3">
                <p className="text-xs font-medium text-slate-500 uppercase tracking-wider">
                    Changed Fields
                </p>
                <div className="mt-2 flex flex-wrap gap-2">
                    {item.changes.map((change, idx) => (
                        <span
                            key={idx}
                            className="inline-flex items-center rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-700"
                        >
                            {change.label}:{' '}
                            <span className="text-red-600 line-through">
                                {change.old_value || '-'}
                            </span>{' '}
                            →{' '}
                            <span className="text-green-600">
                                {change.new_value || '-'}
                            </span>
                        </span>
                    ))}
                </div>
            </div>
        );
    };

    return (
        <div className="space-y-6">
            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-200 px-6 py-4">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 className="text-lg font-semibold text-slate-900">
                                Employee
                            </h3>
                            <p className="mt-1 text-sm text-slate-500">
                                {employee.employee_id} - {employee.name}
                            </p>
                        </div>
                        <div className="flex flex-wrap items-center gap-3">
                            <div className="w-full sm:w-64">
                                <select
                                    value={movementType}
                                    onChange={(e) => setMovementType(e.target.value)}
                                    className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"
                                >
                                    <option value="">All Movement Types</option>
                                    {employmentMovementTypeOptions().map((option) => (
                                        <option key={option.value} value={option.value}>
                                            {option.label}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <Link
                                href={route('hr.employment-movements.index')}
                                className="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            >
                                Back
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            {filteredHistory.length > 0 ? (
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Employment Timeline
                        </h3>
                    </div>
                    <div className="p-6">
                        <div className="flow-root">
                            <ul className="-mb-8">
                                {filteredHistory.map((item, index) => (
                                    <li key={item.id}>
                                        <div className="relative pb-8">
                                            {index !== history.length - 1 && (
                                                <span
                                                    className="absolute left-5 top-5 -ml-px h-full w-0.5 bg-slate-200"
                                                    aria-hidden="true"
                                                />
                                            )}
                                            <div className="relative flex items-start space-x-3">
                                                <div className="flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sky-700">
                                                    <span className="text-sm font-semibold">
                                                        {index + 1}
                                                    </span>
                                                </div>
                                                <div className="flex-1 rounded-xl border border-slate-200 p-4">
                                                    <div className="flex items-center justify-between">
                                                        <div>
                                                            <p className="text-sm font-medium text-slate-900">
                                                                {getEmploymentMovementTypeLabel(item.event_type)}
                                                            </p>
                                                            <p className="text-sm text-slate-500">
                                                                {item.company?.name ??
                                                                    '-'}{' '}
                                                                →{' '}
                                                                {item.department?.name ??
                                                                    '-'}
                                                            </p>
                                                        </div>
                                                        <div className="text-right text-sm text-slate-500">
                                                            <p>
                                                                From:{' '}
                                                                {item.effective_from}
                                                            </p>
                                                            <p>
                                                                To:{' '}
                                                                {item.effective_to ??
                                                                    'Present'}
                                                            </p>
                                                        </div>
                                                    </div>

                                                    {renderChanges(item)}

                                                    <div className="mt-3 flex flex-wrap items-center gap-4 text-sm text-slate-600">
                                                        {item.creator && (
                                                            <span>
                                                                Changed By:{' '}
                                                                {item.creator.name}
                                                            </span>
                                                        )}
                                                    </div>
                                                    {item.remarks && (
                                                        <p className="mt-2 text-sm text-slate-600">
                                                            Remarks:{' '}
                                                            {item.remarks}
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                </div>
            ) : (
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm py-10 text-center">
                    <p className="text-slate-500">
                        {movementType
                            ? 'No employment history found for the selected movement type.'
                            : 'No employment history found.'}
                    </p>
                </div>
            )}
        </div>
    );
}
