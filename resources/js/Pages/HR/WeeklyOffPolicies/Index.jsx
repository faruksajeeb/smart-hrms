import { useEffect, useState } from "react";
import { Head, Link, router } from "@inertiajs/react";

import HRLayout from "@/Layouts/HRLayout";

import PolicyList from "./Components/PolicyList";

export default function Index({
    policies,

    companies,

    filters,
}) {
    const [search, setSearch] = useState(filters.search ?? "");

    const [company, setCompany] = useState(filters.company ?? "");

    const [status, setStatus] = useState(filters.status ?? "");

    /*
    |--------------------------------------------------------------------------
    | Auto Search
    |--------------------------------------------------------------------------
    */

    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(
                route("hr.weekly-off-policies.index"),
                {
                    search,
                    company,
                    status,
                },
                {
                    preserveState: true,
                    replace: true,
                    preserveScroll: true,
                },
            );
        }, 400);

        return () => clearTimeout(timer);
    }, [search, company, status]);

    function resetFilters() {
        setSearch("");

        setCompany("");

        setStatus("");
    }

    return (
        <HRLayout
            heading="Weekly Off Policies"
            subheading="Manage reusable weekly off policies."
        >
            <Head title="Weekly Off Policies" />

            <div className="space-y-6">
                {/* Toolbar */}
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="flex flex-col gap-4 p-6 xl:flex-row xl:items-end xl:justify-between">
                        {/* Filters */}

                        <div className="grid flex-1 grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                            {/* Search */}

                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-700">
                                    Search
                                </label>

                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Policy name or code..."
                                    className="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                />
                            </div>

                            {/* Company */}

                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-700">
                                    Company
                                </label>

                                <select
                                    value={company}
                                    onChange={(e) => setCompany(e.target.value)}
                                    className="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option value="">All Companies</option>

                                    {companies.map((companyItem) => (
                                        <option
                                            key={companyItem.id}
                                            value={companyItem.id}
                                        >
                                            {companyItem.name}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            {/* Status */}

                            <div>
                                <label className="mb-2 block text-sm font-medium text-slate-700">
                                    Status
                                </label>

                                <select
                                    value={status}
                                    onChange={(e) => setStatus(e.target.value)}
                                    className="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option value="">All Status</option>

                                    <option value="1">Active</option>

                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            {/* Reset */}

                            <div className="flex items-end">
                                <button
                                    type="button"
                                    onClick={resetFilters}
                                    className="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                                >
                                    Reset Filters
                                </button>
                            </div>
                        </div>

                        {/* New Policy */}

                        <div>
                            <Link
                                href={route("hr.weekly-off-policies.create")}
                                className="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700"
                            >
                                + New Weekly Off Policy
                            </Link>
                        </div>
                    </div>
                </div>

                {/* Flash Message */}

                {policies?.data?.length > 0 && (
                    <div className="flex items-center justify-between">
                        <div>
                            <h2 className="text-lg font-semibold text-slate-900">
                                Weekly Off Policies
                            </h2>

                            <p className="mt-1 text-sm text-slate-500">
                                Total Policies:{" "}
                                <span className="font-semibold">
                                    {policies.total}
                                </span>
                            </p>
                        </div>
                    </div>
                )}

                {/* Policy List */}

                <PolicyList policies={policies} />

                {/* Empty State */}

                {policies.data.length === 0 && (
                    <div className="rounded-2xl border border-dashed border-slate-300 bg-white py-16 text-center">
                        <div className="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-100">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                className="h-10 w-10 text-slate-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                strokeWidth={1.5}
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    d="M9 17v-6h13M9 5h13M5 5h.01M5 12h.01M5 19h.01"
                                />
                            </svg>
                        </div>

                        <h3 className="mt-6 text-lg font-semibold text-slate-800">
                            No Weekly Off Policy Found
                        </h3>

                        <p className="mt-2 text-sm text-slate-500">
                            Click the button above to create your first weekly
                            off policy.
                        </p>

                        <Link
                            href={route("hr.weekly-off-policies.create")}
                            className="mt-6 inline-flex rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700"
                        >
                            Create Policy
                        </Link>
                    </div>
                )}

                {/* Pagination */}

                {policies.last_page > 1 && (
                    <div className="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white px-6 py-4 shadow-sm">
                        <div className="text-sm text-slate-600">
                            Showing
                            <span className="mx-1 font-semibold">
                                {policies.from}
                            </span>
                            to
                            <span className="mx-1 font-semibold">
                                {policies.to}
                            </span>
                            of
                            <span className="mx-1 font-semibold">
                                {policies.total}
                            </span>
                            records
                        </div>

                        <div className="flex flex-wrap gap-2">
                            {policies.links.map((link, index) => (
                                <button
                                    key={index}
                                    disabled={!link.url}
                                    onClick={() => router.visit(link.url)}
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                    className={`rounded-lg border px-4 py-2 text-sm transition ${
                                        link.active
                                            ? "border-indigo-600 bg-indigo-600 text-white"
                                            : "border-slate-300 bg-white text-slate-700 hover:bg-slate-100"
                                    } ${
                                        !link.url
                                            ? "cursor-not-allowed opacity-40"
                                            : ""
                                    }`}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </HRLayout>
    );
}
