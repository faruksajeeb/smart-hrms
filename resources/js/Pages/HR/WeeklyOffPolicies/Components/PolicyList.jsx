import { Link, router } from "@inertiajs/react";

export default function PolicyList({
    policies,
}) {


    function destroy(policy) {

        if (
            !confirm(
                `Delete "${policy.policy_name}"?`
            )
        ) {
            return;
        }

        router.delete(
            route(
                "hr.weekly-off-policies.destroy",
                policy.id
            ),
            {
                preserveScroll: true,
            }
        );

    }

    if (policies.data.length === 0) {

        return (

            <div className="rounded-2xl border border-dashed border-slate-300 bg-white py-16 text-center">

                <h3 className="text-lg font-semibold text-slate-700">
                    No Weekly Off Policy Found
                </h3>

                <p className="mt-2 text-sm text-slate-500">
                    Create your first weekly off policy.
                </p>

            </div>

        );

    }

    return (

        <div className="space-y-5">

            {policies.data.map(policy => (

                <div
                    key={policy.id}
                    className="rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md"
                >

                    <div className="flex flex-col gap-6 p-6 lg:flex-row lg:items-center lg:justify-between">

                        {/* Left */}

                        <div className="space-y-3">

                            <div className="flex items-center gap-3">

                                <h3 className="text-lg font-semibold text-slate-900">
                                    {policy.policy_name}
                                </h3>

                                <span
                                    className={`rounded-full px-3 py-1 text-xs font-semibold ${
                                        policy.status
                                            ? "bg-green-100 text-green-700"
                                            : "bg-red-100 text-red-700"
                                    }`}
                                >
                                    {policy.status
                                        ? "Active"
                                        : "Inactive"}
                                </span>

                            </div>

                            <div className="flex flex-wrap gap-6 text-sm text-slate-600">

                                <div>

                                    <span className="font-medium">
                                        Company:
                                    </span>

                                    {" "}

                                    {policy.company?.name}

                                </div>

                                <div>

                                    <span className="font-medium">
                                        Code:
                                    </span>

                                    {" "}

                                    {policy.policy_code || "-"}

                                </div>

                                <div>

                                    <span className="font-medium">
                                        Weekly Off:
                                    </span>

                                    {" "}

                                    {policy.days_count}

                                    {" "}Day(s)

                                </div>

                            </div>

                            {policy.description && (

                                <p className="text-sm text-slate-500">

                                    {policy.description}

                                </p>

                            )}

                        </div>

                        {/* Right */}

                        <div className="flex flex-wrap gap-3">

                            <Link
                                href={route(
                                    "hr.weekly-off-policies.edit",
                                    policy.id
                                )}
                                className="rounded-xl border border-indigo-200 bg-indigo-50 px-5 py-2 text-sm font-medium text-indigo-700 transition hover:bg-indigo-100"
                            >
                                Edit
                            </Link>
                            <Link
                                href={route(
                                    "hr.weekly-off-policies.clone",
                                    policy.id
                                )}
                                className="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-100"
                            >
                                Clone
                            </Link>

                            <button
                                onClick={() => destroy(policy)}
                                className="rounded-xl border border-red-200 bg-red-50 px-5 py-2 text-sm font-medium text-red-700 transition hover:bg-red-100"
                            >
                                Delete
                            </button>

                        </div>

                    </div>

                </div>

            ))}

        </div>

    );

}