import { Head,Link } from "@inertiajs/react";

import HRLayout from '@/Layouts/HRLayout';

export default function ShowComponent({ workflow, branches = [], companyBranchesData = [] }) {
    const branchMap = new Map(
        branches.map((branch) => [branch.id, branch])
    );

    return (
        <HRLayout
                    heading="Approval Workflows"
                    subheading="Manage approval workflows for employee requests."
                >
                    <Head title="Approval Workflows" />
        <div className="space-y-6">
            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-200 px-6 py-4">
                    <div className="flex items-center justify-between">
                        <div>
                            <h3 className="text-lg font-semibold text-slate-900">
                                {workflow.workflow_name}
                            </h3>
                            <p className="mt-1 text-sm text-slate-500">
                                {workflow.module_name}
                            </p>
                        </div>
                        <Link
                            href={route('hr.approval.workflows.index')}
                            className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                        >
                            Back
                        </Link>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                    <div>
                        <h4 className="text-sm font-medium text-slate-500">
                            Workflow Code
                        </h4>
                        <p className="mt-1 font-semibold text-slate-900">
                            {workflow.workflow_code}
                        </p>
                    </div>

                    <div>
                        <h4 className="text-sm font-medium text-slate-500">
                            Status
                        </h4>
                        <p className="mt-1 font-semibold text-slate-900">
                            {workflow.status}
                        </p>
                    </div>

                    <div className="lg:col-span-2">
                        <h4 className="text-sm font-medium text-slate-500">
                            Description
                        </h4>
                        <p className="mt-1 font-semibold text-slate-900">
                            {workflow.description || '-'}
                        </p>
                    </div>

                    {companyBranchesData.length > 0 && (
                        <div className="lg:col-span-2">
                            <h4 className="text-sm font-medium text-slate-500">
                                Companies & Branches
                            </h4>
                            <div className="mt-2 space-y-3">
                                {companyBranchesData.map((item) => {
                                    const company = item.company;
                                    const branchIds = item.branch_ids || [];
                                    const selectedBranches = branchIds
                                        .map((id) => branchMap.get(id))
                                        .filter(Boolean);

                                    return (
                                        <div
                                            key={company.id}
                                            className="rounded-xl border border-slate-200 p-4"
                                        >
                                            <p className="text-sm font-semibold text-slate-900">
                                                {company.code
                                                    ? `${company.code} - ${company.name}`
                                                    : company.name}
                                            </p>
                                            {selectedBranches.length > 0 ? (
                                                <div className="mt-2 flex flex-wrap gap-2">
                                                    {selectedBranches.map((branch) => (
                                                        <span
                                                            key={branch.id}
                                                            className="inline-flex rounded-full bg-sky-50 px-2.5 py-0.5 text-xs font-medium text-sky-700"
                                                        >
                                                            {branch.code
                                                                ? `${branch.code} - ${branch.name}`
                                                                : branch.name}
                                                        </span>
                                                    ))}
                                                </div>
                                            ) : (
                                                <p className="mt-1 text-xs text-slate-500">
                                                    All branches
                                                </p>
                                            )}
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {workflow.levels && workflow.levels.length > 0 && (
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Approval Levels
                        </h3>
                    </div>
                    <div className="p-6">
                        <div className="flow-root">
                            <ul className="-mb-8">
                                {workflow.levels.map((level, index) => (
                                    <li key={level.id}>
                                        <div className="relative pb-8">
                                            {index !== workflow.levels.length - 1 && (
                                                <span
                                                    className="absolute left-5 top-5 -ml-px h-full w-0.5 bg-slate-200"
                                                    aria-hidden="true"
                                                />
                                            )}
                                            <div className="relative flex items-start space-x-3">
                                                <div className="flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sky-700">
                                                    <span className="text-sm font-semibold">
                                                        {level.level_no}
                                                    </span>
                                                </div>
                                                <div className="flex-1 rounded-xl border border-slate-200 p-4">
                                                    <p className="text-sm font-medium text-slate-900">
                                                        {level.approval_type}
                                                    </p>
                                                    <p className="text-sm text-slate-500">
                                                        Minimum Approvals: {level.minimum_approvals}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                </div>
            )}
        </div>
        </HRLayout>
    );
}
