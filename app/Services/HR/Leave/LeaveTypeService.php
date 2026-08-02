<?php

namespace App\Services\HR\Leave;

use App\Enums\LeaveTypeStatus;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LeaveTypeService
{
    public function create(array $data, ?int $userId = null): LeaveType
    {
        return LeaveType::create([
            'leave_name' => $data['leave_name'],
            'leave_code' => $data['leave_code'],
            'description' => $data['description'] ?? null,
            'is_paid' => $data['is_paid'] ?? true,
            'display_color' => $data['display_color'] ?? null,
            'display_order' => $data['display_order'] ?? 0,
            'status' => $data['status'] ?? LeaveTypeStatus::Active,
            'created_by' => $userId ?? Auth::id(),
            'updated_by' => $userId ?? Auth::id(),
        ]);
    }

    public function update(LeaveType $leaveType, array $data, ?int $userId = null): LeaveType
    {
        $leaveType->update([
            'leave_name' => $data['leave_name'] ?? $leaveType->leave_name,
            'leave_code' => $data['leave_code'] ?? $leaveType->leave_code,
            'description' => $data['description'] ?? $leaveType->description,
            'is_paid' => $data['is_paid'] ?? $leaveType->is_paid,
            'display_color' => $data['display_color'] ?? $leaveType->display_color,
            'display_order' => $data['display_order'] ?? $leaveType->display_order,
            'status' => $data['status'] ?? $leaveType->status,
            'updated_by' => $userId ?? Auth::id(),
        ]);

        return $leaveType->fresh();
    }

    public function delete(LeaveType $leaveType): void
    {
        $leaveType->delete();
    }

    public function getActiveTypes()
    {
        return LeaveType::where('status', LeaveTypeStatus::Active)
            ->orderBy('display_order')
            ->orderBy('leave_name')
            ->get();
    }
}
