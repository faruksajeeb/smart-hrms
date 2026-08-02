import { Link } from '@inertiajs/react';
import { employmentMovementTypeFrom } from '@/Enums/EmploymentMovementType';

export default function ShowComponent({ movement }) {
    const type = movement.event_type
        ? employmentMovementTypeFrom(movement.event_type)
        : null;

    const orgFields = [
        { label: 'Company', value: movement.company?.name },
        { label: 'Branch', value: movement.branch?.name },
        { label: 'Cluster', value: movement.cluster?.name },
        { label: 'Division', value: movement.division?.name },
        { label: 'Department', value: movement.department?.name },
        { label: 'Section', value: movement.section?.name },
        { label: 'Unit', value: movement.unit?.name },
    ];

    const statusBadge = () => {
        if (movement.effective_to) {
            return (
                <span className="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-700">
                    Closed
                </span>
            );
        }

        return (
            <span className="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-700">
                Active
            </span>
        );
    };

    return (
        <div className="space-y-6">
            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-200 px-6 py-4">
                    <div className="flex items-center justify-between">
                        <div>
                            <h3 className="text-lg font-semibold text-slate-900">
                                Movement Information
                            </h3>
                            <p className="mt-1 text-sm text-slate-500">
                                {movement.employee?.employee_id} -{' '}
                                {movement.employee?.name}
                            </p>
                        </div>
                        <div className="flex items-center gap-3">
                            {statusBadge()}
                            <Link
                                href={route('hr.employment-movements.index')}
                                className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            >
                                Back
                            </Link>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                    <div>
                        <h4 className="text-sm font-medium text-slate-500">
                            Movement Type
                        </h4>
                        <p className="mt-1 font-semibold text-slate-900">
                            {type?.label() ?? movement.event_type}
                        </p>
                    </div>

                    <div>
                        <h4 className="text-sm font-medium text-slate-500">
                            Effective From
                        </h4>
                        <p className="mt-1 font-semibold text-slate-900">
                            {movement.effective_from}
                        </p>
                    </div>

                    <div>
                        <h4 className="text-sm font-medium text-slate-500">
                            Effective To
                        </h4>
                        <p className="mt-1 font-semibold text-slate-900">
                            {movement.effective_to ?? '-'}
                        </p>
                    </div>

                    <div>
                        <h4 className="text-sm font-medium text-slate-500">
                            Reason
                        </h4>
                        <p className="mt-1 font-semibold text-slate-900">
                            {movement.reason}
                        </p>
                    </div>

                    <div>
                        <h4 className="text-sm font-medium text-slate-500">
                            Designation
                        </h4>
                        <p className="mt-1 font-semibold text-slate-900">
                            {movement.designation?.name ?? '-'}
                        </p>
                    </div>

                    <div>
                        <h4 className="text-sm font-medium text-slate-500">
                            Employment Type
                        </h4>
                        <p className="mt-1 font-semibold text-slate-900">
                            {movement.employmentType?.name ?? '-'}
                        </p>
                    </div>

                    <div>
                        <h4 className="text-sm font-medium text-slate-500">
                            Reporting Manager
                        </h4>
                        <p className="mt-1 font-semibold text-slate-900">
                            {movement.reportingManager?.name ?? '-'}
                        </p>
                    </div>

                    <div>
                        <h4 className="text-sm font-medium text-slate-500">
                            Remarks
                        </h4>
                        <p className="mt-1 font-semibold text-slate-900">
                            {movement.remarks ?? '-'}
                        </p>
                    </div>

                    {movement.changes && movement.changes.length > 0 && (
                        <div className="lg:col-span-2">
                            <h4 className="text-sm font-medium text-slate-500">
                                Changes
                            </h4>
                            <div className="mt-2 flex flex-wrap gap-2">
                                {movement.changes.map((change, idx) => (
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
                    )}

                    <div>
                        <h4 className="text-sm font-medium text-slate-500">
                            Created By
                        </h4>
                        <p className="mt-1 font-semibold text-slate-900">
                            {movement.creator?.name ?? '-'}
                        </p>
                    </div>

                    <div>
                        <h4 className="text-sm font-medium text-slate-500">
                            Last Updated By
                        </h4>
                        <p className="mt-1 font-semibold text-slate-900">
                            {movement.updater?.name ?? '-'}
                        </p>
                    </div>
                </div>
            </div>

            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-200 px-6 py-4">
                    <h3 className="text-lg font-semibold text-slate-900">
                        Organizational Assignment
                    </h3>
                </div>

                <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                    {orgFields.map((field) => (
                        <div key={field.label}>
                            <h4 className="text-sm font-medium text-slate-500">
                                {field.label}
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {field.value ?? '-'}
                            </p>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}
