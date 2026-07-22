import { router } from '@inertiajs/react';
import { useState } from 'react';
import SecondaryButton from '@/Components/SecondaryButton';
import SearchSelect from '@/Components/SearchSelect';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';

export default function FilterPanel({ filters = {}, options = {} }) {
    const [company, setCompany] = useState(filters.company ?? '');
    const [branch, setBranch] = useState(filters.branch ?? '');
    const [division, setDivision] = useState(filters.division ?? '');
    const [department, setDepartment] = useState(filters.department ?? '');
    const [designation, setDesignation] = useState(filters.designation ?? '');
    const [employmentType, setEmploymentType] = useState(filters.employment_type ?? '');
    const [status, setStatus] = useState(filters.status ?? '');
    const [employeeId, setEmployeeId] = useState(filters.employee_id ?? '');
    const [search, setSearch] = useState(filters.search ?? '');

    const submitFilters = (event) => {
        event.preventDefault();

        router.get(
            route('hr.bulk-assignments.index'),
            {
                company,
                branch,
                division,
                department,
                designation,
                employment_type: employmentType,
                status,
                employee_id: employeeId,
                search,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    };

    const clearFilters = () => {
        router.get(route('hr.bulk-assignments.index'), {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const hasFilters = [company, branch, division, department, designation, employmentType, status, employeeId, search].some(Boolean);

    return (
        <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 className="text-lg font-semibold text-slate-950">Filters</h2>
            <p className="mt-1 text-sm text-slate-500">
                Filter employees by company, branch, division, department, designation, employment type, status, or search by employee ID.
            </p>

            <form onSubmit={submitFilters} className="mt-6 space-y-4">
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <SearchSelect
                        id="company"
                        label="Company"
                        value={company}
                        onChange={setCompany}
                        options={options.companies ?? []}
                        placeholder="All companies"
                        isClearable
                    />

                    <SearchSelect
                        id="branch"
                        label="Branch"
                        value={branch}
                        onChange={setBranch}
                        options={options.branches ?? []}
                        placeholder="All branches"
                        isClearable
                    />

                    <SearchSelect
                        id="division"
                        label="Division"
                        value={division}
                        onChange={setDivision}
                        options={options.divisions ?? []}
                        placeholder="All divisions"
                        isClearable
                    />

                    <SearchSelect
                        id="department"
                        label="Department"
                        value={department}
                        onChange={setDepartment}
                        options={options.departments ?? []}
                        placeholder="All departments"
                        isClearable
                    />

                    <SearchSelect
                        id="designation"
                        label="Designation"
                        value={designation}
                        onChange={setDesignation}
                        options={options.designations ?? []}
                        placeholder="All designations"
                        isClearable
                    />

                    <SearchSelect
                        id="employment_type"
                        label="Employment Type"
                        value={employmentType}
                        onChange={setEmploymentType}
                        options={options.employmentTypes ?? []}
                        placeholder="All employment types"
                        isClearable
                    />

                    <SearchSelect
                        id="status"
                        label="Employee Status"
                        value={status}
                        onChange={setStatus}
                        options={options.statuses ?? []}
                        placeholder="All statuses"
                        isClearable
                    />

                    <TextInput
                        id="employee_id"
                        label="Employee ID"
                        value={employeeId}
                        onChange={(event) => setEmployeeId(event.target.value)}
                        placeholder="Search by employee ID"
                    />

                    <TextInput
                        id="search"
                        label="Search"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder="Search by name or email"
                    />
                </div>

                <div className="flex items-center justify-end gap-3">
                    {hasFilters && (
                        <button
                            type="button"
                            onClick={clearFilters}
                            className="text-sm font-medium text-slate-500 hover:text-slate-700"
                        >
                            Clear Filters
                        </button>
                    )}
                    <SecondaryButton type="submit">
                        Filter Employees
                    </SecondaryButton>
                </div>
            </form>
        </section>
    );
}
