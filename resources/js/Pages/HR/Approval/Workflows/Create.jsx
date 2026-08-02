import { Head, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { approvalTypeOptions, getApprovalTypeLabel } from '@/Enums/ApprovalType';

import HRLayout from '@/Layouts/HRLayout';

const emptyLevel = () => ({
    level_no: 1,
    approval_type: 'REPORTING_MANAGER',
    role_id: '',
    specific_user_id: '',
    minimum_approvals: 1,
    can_reject: true,
    can_delegate: false,
    can_skip: false,
    is_final_level: false,
});

export default function Create({
    companies = [],
    branches = [],
    branchesByCompany = {},
    roles = [],
    users = [],
    errors = {},
}) {
    const [levels, setLevels] = useState([emptyLevel()]);
    const [selectedCompanies, setSelectedCompanies] = useState([]);
    const [selectedBranches, setSelectedBranches] = useState({});

    const { data, setData, post, processing } = useForm({
        workflow_name: '',
        workflow_code: '',
        module_name: '',
        description: '',
        status: 'active',
        company_branches: [],
    });

    useEffect(() => {
        setData('levels', levels);
    }, [levels]);

    useEffect(() => {
        const companyBranches = [];
        selectedCompanies.forEach((companyId) => {
            const branchIds = selectedBranches[companyId] || [];
            if (branchIds.length === 0) {
                companyBranches.push({ company_id: companyId, branch_id: null });
            } else {
                branchIds.forEach((branchId) => {
                    companyBranches.push({ company_id: companyId, branch_id: branchId });
                });
            }
        });
        setData('company_branches', companyBranches);
    }, [selectedCompanies, selectedBranches]);

    function addLevel() {
        const nextLevelNo = levels.length + 1;
        setLevels([...levels, { ...emptyLevel(), level_no: nextLevelNo }]);
    }

    function removeLevel(index) {
        if (levels.length === 1) {
            return;
        }

        const updated = levels.filter((_, i) => i !== index);
        updated.forEach((level, i) => {
            level.level_no = i + 1;
        });
        setLevels(updated);
    }

    function updateLevel(index, field, value) {
        const updated = [...levels];
        updated[index] = { ...updated[index], [field]: value };
        setLevels(updated);
    }

    function handleCompanyToggle(companyId) {
        const isSelected = selectedCompanies.includes(companyId);
        let newSelectedCompanies;

        if (isSelected) {
            newSelectedCompanies = selectedCompanies.filter((id) => id !== companyId);
            const newSelectedBranches = { ...selectedBranches };
            delete newSelectedBranches[companyId];
            setSelectedBranches(newSelectedBranches);
        } else {
            newSelectedCompanies = [...selectedCompanies, companyId];
        }

        setSelectedCompanies(newSelectedCompanies);
    }

    function handleBranchToggle(companyId, branchId) {
        setSelectedBranches((prev) => {
            const current = prev[companyId] || [];
            const updated = current.includes(branchId)
                ? current.filter((id) => id !== branchId)
                : [...current, branchId];

            return {
                ...prev,
                [companyId]: updated,
            };
        });
    }

    function submit(e) {
        e.preventDefault();
        post(route('hr.approval.workflows.store'));
    }

    const renderLevelFields = (level, index) => {
        const approvalType = level.approval_type;

        return (
            
            <div className="rounded-xl border border-slate-200 p-4">
                <div className="flex items-center justify-between mb-4">
                    <h4 className="text-sm font-semibold text-slate-900">
                        Level {level.level_no}
                    </h4>
                    {levels.length > 1 && (
                        <button
                            type="button"
                            onClick={() => removeLevel(index)}
                            className="text-sm text-red-600 hover:text-red-800"
                        >
                            Remove
                        </button>
                    )}
                </div>

                <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div>
                        <label className="block text-sm font-medium text-slate-700">
                            Level No
                        </label>
                        <input
                            type="number"
                            value={level.level_no}
                            onChange={(e) =>
                                updateLevel(index, 'level_no', parseInt(e.target.value) || 1)
                            }
                            className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            min="1"
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700">
                            Approval Type
                        </label>
                        <select
                            value={approvalType}
                            onChange={(e) =>
                                updateLevel(index, 'approval_type', e.target.value)
                            }
                            className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                        >
                            {approvalTypeOptions().map((option) => (
                                <option key={option.value} value={option.value}>
                                    {option.label}
                                </option>
                            ))}
                        </select>
                    </div>

                    {approvalType === 'ROLE' && (
                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Role
                            </label>
                            <select
                                value={level.role_id ?? ''}
                                onChange={(e) =>
                                    updateLevel(index, 'role_id', e.target.value ? parseInt(e.target.value) : '')
                                }
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">Select Role</option>
                                {roles.map((role) => (
                                    <option key={role.id} value={role.id}>
                                        {role.name}
                                    </option>
                                ))}
                            </select>
                        </div>
                    )}

                    {approvalType === 'SPECIFIC_USER' && (
                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Specific User
                            </label>
                            <select
                                value={level.specific_user_id ?? ''}
                                onChange={(e) =>
                                    updateLevel(index, 'specific_user_id', e.target.value ? parseInt(e.target.value) : '')
                                }
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">Select User</option>
                                {users.map((user) => (
                                    <option key={user.id} value={user.id}>
                                        {user.employee_id} - {user.name}
                                    </option>
                                ))}
                            </select>
                        </div>
                    )}

                    <div>
                        <label className="block text-sm font-medium text-slate-700">
                            Minimum Approvals
                        </label>
                        <input
                            type="number"
                            value={level.minimum_approvals}
                            onChange={(e) =>
                                updateLevel(index, 'minimum_approvals', parseInt(e.target.value) || 1)
                            }
                            className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            min="1"
                        />
                    </div>

                    <div className="flex items-center gap-6">
                        <label className="flex items-center gap-2">
                            <input
                                type="checkbox"
                                checked={level.can_reject}
                                onChange={(e) =>
                                    updateLevel(index, 'can_reject', e.target.checked)
                                }
                                className="rounded border-slate-300"
                            />
                            <span className="text-sm text-slate-700">Can Reject</span>
                        </label>

                        <label className="flex items-center gap-2">
                            <input
                                type="checkbox"
                                checked={level.can_delegate}
                                onChange={(e) =>
                                    updateLevel(index, 'can_delegate', e.target.checked)
                                }
                                className="rounded border-slate-300"
                            />
                            <span className="text-sm text-slate-700">Can Delegate</span>
                        </label>

                        <label className="flex items-center gap-2">
                            <input
                                type="checkbox"
                                checked={level.can_skip}
                                onChange={(e) =>
                                    updateLevel(index, 'can_skip', e.target.checked)
                                }
                                className="rounded border-slate-300"
                            />
                            <span className="text-sm text-slate-700">Can Skip</span>
                        </label>
                    </div>

                    <div>
                        <label className="flex items-center gap-2">
                            <input
                                type="checkbox"
                                checked={level.is_final_level}
                                onChange={(e) =>
                                    updateLevel(index, 'is_final_level', e.target.checked)
                                }
                                className="rounded border-slate-300"
                            />
                            <span className="text-sm font-medium text-slate-700">
                                Is Final Level
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        );
    };

    return (
        <HRLayout
            heading="Create Approval Workflow"
            subheading="Configure a new approval workflow."
        >
            <Head title="Create Approval Workflow" />
            <form onSubmit={submit} className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Workflow Information
                        </h3>
                        <p className="mt-1 text-sm text-slate-500">
                            Basic details for the approval workflow.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Workflow Name <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                value={data.workflow_name}
                                onChange={(e) => setData('workflow_name', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.workflow_name && (
                                <p className="mt-1 text-sm text-red-600">{errors.workflow_name}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Workflow Code <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                value={data.workflow_code}
                                onChange={(e) => setData('workflow_code', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.workflow_code && (
                                <p className="mt-1 text-sm text-red-600">{errors.workflow_code}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Module <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                value={data.module_name}
                                onChange={(e) => setData('module_name', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                                placeholder="e.g. employment_movement"
                            />
                            {errors.module_name && (
                                <p className="mt-1 text-sm text-red-600">{errors.module_name}</p>
                            )}
                        </div>

                        <div className="lg:col-span-2">
                            <label className="block text-sm font-medium text-slate-700">
                                Companies & Branches
                            </label>
                            <p className="mt-1 text-sm text-slate-500">
                                Select companies. For each selected company, choose the branches this workflow applies to. Leave branches empty to apply to all branches of that company.
                            </p>

                            <div className="mt-4 space-y-4">
                                {companies.map((company) => {
                                    const isChecked = selectedCompanies.includes(company.id);
                                    const companyBranches = branchesByCompany[company.id] || [];
                                    const selectedBranchIds = selectedBranches[company.id] || [];

                                    return (
                                        <div
                                            key={company.id}
                                            className="rounded-xl border border-slate-200 p-4"
                                        >
                                            <div className="flex items-center gap-3">
                                                <input
                                                    type="checkbox"
                                                    id={`company-${company.id}`}
                                                    checked={isChecked}
                                                    onChange={() => handleCompanyToggle(company.id)}
                                                    className="h-4 w-4 rounded border-slate-300"
                                                />
                                                <label
                                                    htmlFor={`company-${company.id}`}
                                                    className="text-sm font-semibold text-slate-900"
                                                >
                                                    {company.code
                                                        ? `${company.code} - ${company.name}`
                                                        : company.name}
                                                </label>
                                            </div>

                                            {isChecked && companyBranches.length > 0 && (
                                                <div className="mt-3 ml-7 grid grid-cols-2 gap-2 md:grid-cols-3 lg:grid-cols-4">
                                                    {companyBranches.map((branch) => (
                                                        <div
                                                            key={branch.id}
                                                            className="flex items-center gap-2"
                                                        >
                                                            <input
                                                                type="checkbox"
                                                                id={`branch-${company.id}-${branch.id}`}
                                                                checked={selectedBranchIds.includes(branch.id)}
                                                                onChange={() => handleBranchToggle(company.id, branch.id)}
                                                                className="h-4 w-4 rounded border-slate-300"
                                                            />
                                                            <label
                                                                htmlFor={`branch-${company.id}-${branch.id}`}
                                                                className="text-sm text-slate-700"
                                                            >
                                                                {branch.code
                                                                    ? `${branch.code} - ${branch.name}`
                                                                    : branch.name}
                                                            </label>
                                                        </div>
                                                    ))}
                                                </div>
                                            )}

                                            {isChecked && companyBranches.length === 0 && (
                                                <p className="mt-2 ml-7 text-xs text-slate-500">
                                                    No branches configured for this company.
                                                </p>
                                            )}
                                        </div>
                                    );
                                })}
                            </div>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Status <span className="text-red-500">*</span>
                            </label>
                            <select
                                value={data.status}
                                onChange={(e) => setData('status', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            {errors.status && (
                                <p className="mt-1 text-sm text-red-600">{errors.status}</p>
                            )}
                        </div>

                        <div className="lg:col-span-2">
                            <label className="block text-sm font-medium text-slate-700">
                                Description
                            </label>
                            <textarea
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                                rows="3"
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                                placeholder="Describe the purpose of this workflow..."
                            />
                            {errors.description && (
                                <p className="mt-1 text-sm text-red-600">{errors.description}</p>
                            )}
                        </div>
                    </div>
                </div>

                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="text-lg font-semibold text-slate-900">
                                    Approval Levels
                                </h3>
                                <p className="mt-1 text-sm text-slate-500">
                                    Define the sequence of approvers for this workflow.
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={addLevel}
                                className="inline-flex items-center rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800"
                            >
                                + Add Level
                            </button>
                        </div>
                    </div>

                    <div className="p-6 space-y-4">
                        {levels.map((level, index) => (
                            <div key={index}>
                                {renderLevelFields(level, index)}
                            </div>
                        ))}
                    </div>
                </div>

                <div className="flex items-center justify-end gap-3">
                    <a
                        href={route('hr.approval.workflows.index')}
                        className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800 disabled:opacity-50"
                    >
                        {processing ? 'Saving...' : 'Create Workflow'}
                    </button>
                </div>
            </form>
        </HRLayout>
    );
}
