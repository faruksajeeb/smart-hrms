import { Head, useForm, router } from "@inertiajs/react";
import { useState } from "react";

import HRLayout from "@/Layouts/HRLayout";
import Modal from "@/Components/Modal";

import ShiftForm from "./Components/ShiftForm";
import ShiftTable from "./Components/ShiftTable";

export default function Index({ shifts }) {
    const [showForm, setShowForm] = useState(false);
    const [editingId, setEditingId] = useState(null);

    const initialState = {
        shift_name: "",
        shift_code: "",
        description: "",

        start_time: "09:00",
        end_time: "18:00",

        break_start: "13:00",
        break_end: "14:00",

        grace_time: 15,
        working_hours: 8,

        late_after: 15,
        half_day_after: 240,
        minimum_work_hours: 8,

        color: "#3b82f6",

        is_flexible: false,
        is_night_shift: false,

        status: true,
    };

    const {
        data,
        setData,
        post,
        patch,
        processing,
        errors,
        reset,
    } = useForm(initialState);

    /**
     * Open Create Form
     */
    function openCreate() {
        reset();
        setData(initialState);
        setEditingId(null);
        setShowForm(true);
    }

    /**
     * Open Edit Form
     */
    function openEdit(shift) {
        setEditingId(shift.id);

        setData({
            shift_name: shift.shift_name ?? "",
            shift_code: shift.shift_code ?? "",
            description: shift.description ?? "",

            start_time: shift.start_time?.substring(0, 5) ?? "",
            end_time: shift.end_time?.substring(0, 5) ?? "",

            break_start: shift.break_start?.substring(0, 5) ?? "",
            break_end: shift.break_end?.substring(0, 5) ?? "",

            grace_time: shift.grace_time ?? 0,
            working_hours: shift.working_hours ?? 8,

            late_after: shift.late_after ?? 0,
            half_day_after: shift.half_day_after ?? 0,
            minimum_work_hours: shift.minimum_work_hours ?? 8,

            color: shift.color ?? "#3b82f6",

            is_flexible: Boolean(shift.is_flexible),
            is_night_shift: Boolean(shift.is_night_shift),

            status: Boolean(shift.status),
        });

        setShowForm(true);
    }

    /**
     * Cancel Form
     */
    function cancelForm() {
        reset();
        setData(initialState);
        setEditingId(null);
        setShowForm(false);
    }

    /**
     * Save
     */
    function submit(e) {
        e.preventDefault();

        if (editingId) {
            patch(route("hr.shifts.update", editingId), {
                preserveScroll: true,
                onSuccess: () => cancelForm(),
            });

            return;
        }

        post(route("hr.shifts.store"), {
            preserveScroll: true,
            onSuccess: () => cancelForm(),
        });
    }

    /**
     * Delete
     */
    function remove(id) {
        if (!confirm("Are you sure you want to delete this shift?")) {
            return;
        }

        router.delete(route("hr.shifts.destroy", id), {
            preserveScroll: true,
        });
    }

    /**
     * Toggle Status
     */
    function toggleStatus(shift) {
        router.patch(
            route("hr.shifts.update", shift.id),
            {
                ...shift,
                status: !shift.status,
            },
            {
                preserveScroll: true,
            }
        );
    }

    return (
        <HRLayout
            heading="Shift Management"
            subheading="Create and manage company shift templates."
        >
            <Head title="Shift Management" />

            <div className="space-y-6">

                <div className="flex items-center justify-between">

                    <div>

                        <h2 className="text-xl font-semibold text-slate-900">
                            Shift Templates
                        </h2>

                        <p className="mt-1 text-sm text-slate-500">
                            Configure office working shifts.
                        </p>

                    </div>

                    <button
                        onClick={openCreate}
                        className="rounded-xl bg-emerald-600 px-5 py-2.5 text-white transition hover:bg-emerald-700"
                    >
                        + New Shift
                    </button>

                </div>

                <ShiftTable
                    shifts={shifts}
                    onEdit={openEdit}
                    onDelete={remove}
                    onToggleStatus={toggleStatus}
                />

            </div>

            <Modal show={showForm} onClose={cancelForm} maxWidth="6xl">
                <ShiftForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    editing={editingId !== null}
                    onSubmit={submit}
                    onCancel={cancelForm}
                />
            </Modal>

        </HRLayout>
    );
}