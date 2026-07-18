import { useEffect } from "react";
import { useForm } from "@inertiajs/react";

import PolicyBasicInfo from "./Components/PolicyBasicInfo";
import WeeklyOffRuleGrid from "./Components/WeeklyOffRuleGrid";
import PolicyPreview from "./Components/PolicyPreview";

export default function Form({
    policy = null,
    companies = [],
    onSuccess = () => {},
}) {
    const isEdit = !!policy;

    const { data, setData, post, put, processing, errors, reset, clearErrors } =
        useForm({
            company_id: policy?.company_id ?? "",

            policy_name: policy?.policy_name ?? "",

            policy_code: policy?.policy_code ?? "",

            description: policy?.description ?? "",

            status: policy?.status ?? true,

            days: policy?.days ?? [],
        });

    /*
    |--------------------------------------------------------------------------
    | Initialize Default Days
    |--------------------------------------------------------------------------
    */

    useEffect(() => {
        if (isEdit) {
            return;
        }

        if (data.days.length > 0) {
            return;
        }

        setData("days", defaultWeekDays());
    }, []);

    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    function submit(e) {
        e.preventDefault();

        clearErrors();

        if (isEdit) {
            put(route("hr.weekly-off-policies.update", policy.id), {
                preserveScroll: true,

                onSuccess: () => {
                    onSuccess();
                },
            });

            return;
        }

        post(route("hr.weekly-off-policies.store"), {
            preserveScroll: true,

            onSuccess: () => {
                reset();

                setData("days", defaultWeekDays());

                onSuccess();
            },
        });
    }

    return (
        <form onSubmit={submit} className="space-y-8">
            <div className="grid grid-cols-12 gap-6">
                {/* Left Side */}
                <div className="col-span-12 xl:col-span-8 space-y-6">
                    {/* Basic Information */}
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div className="border-b border-slate-200 px-6 py-4">
                            <h2 className="text-lg font-semibold text-slate-900">
                                Policy Information
                            </h2>

                            <p className="mt-1 text-sm text-slate-500">
                                Configure the weekly off policy details.
                            </p>
                        </div>

                        <div className="p-6">
                            <PolicyBasicInfo
                                data={data}
                                setData={setData}
                                companies={companies}
                                errors={errors}
                            />
                        </div>
                    </div>

                    {/* Weekly Off Rules */}
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div className="border-b border-slate-200 px-6 py-4">
                            <h2 className="text-lg font-semibold text-slate-900">
                                Weekly Off Rules
                            </h2>

                            <p className="mt-1 text-sm text-slate-500">
                                Enable the days that are considered weekly off.
                            </p>
                        </div>

                        <div className="p-6">
                            <WeeklyOffRuleGrid
                                data={data}
                                setData={setData}
                                errors={errors}
                            />
                        </div>
                    </div>
                </div>

                {/* Right Sidebar */}
                <div className="col-span-12 xl:col-span-4">
                    <div className="sticky top-5 space-y-6">
                        <PolicyPreview data={data} />

                        {/* Save Card */}

                        <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div className="p-6">
                                <h3 className="text-base font-semibold text-slate-900">
                                    Actions
                                </h3>

                                <p className="mt-2 text-sm text-slate-500">
                                    Review your configuration before saving.
                                </p>

                                <div className="mt-6 space-y-3">
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="w-full rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        {processing
                                            ? "Saving..."
                                            : isEdit
                                              ? "Update Policy"
                                              : "Save Policy"}
                                    </button>

                                    <button
                                        type="button"
                                        onClick={() => history.back()}
                                        className="w-full rounded-xl border border-slate-300 px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    );
}

/*
|--------------------------------------------------------------------------
| Default Week Days
|--------------------------------------------------------------------------
*/

function defaultWeekDays() {
    return [
        createDay(0, "Sunday"),

        createDay(1, "Monday"),

        createDay(2, "Tuesday"),

        createDay(3, "Wednesday"),

        createDay(4, "Thursday"),

        createDay(5, "Friday"),

        createDay(6, "Saturday"),
    ];
}

function createDay(day, label) {
    return {
        label,

        day_of_week: day,

        enabled: false,

        week_type: "every",

        week_number: null,

        off_type: "full_day",

        status: true,
    };
}
