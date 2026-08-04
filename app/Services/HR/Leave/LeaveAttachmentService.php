<?php

namespace App\Services\HR\Leave;

use App\Enums\AttachmentStatus;
use App\Models\LeaveAttachment;
use App\Models\LeaveApplication;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LeaveAttachmentService
{
    public function upload(LeaveApplication $application, $file, ?int $userId = null): LeaveAttachment
    {
        $disk = config('filesystems.default');

        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $storedName = (string) Str::uuid() . '.' . $extension;

        $year = now()->format('Y');
        $month = now()->format('m');
        $employeeId = $application->user_id;

        $path = "leave-attachments/{$year}/{$month}/{$employeeId}/{$storedName}";

        $file->storeAs("leave-attachments/{$year}/{$month}/{$employeeId}", $storedName, $disk);

        $attachment = LeaveAttachment::create([
            'leave_application_id' => $application->id,
            'file_name' => $path,
            'file_path' => $path,
            'original_file_name' => $originalName,
            'stored_file_name' => $storedName,
            'storage_disk' => $disk,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
            'status' => AttachmentStatus::Pending->value,
            'uploaded_by' => $userId ?? auth()->id(),
        ]);

        $this->notifyHrAboutUpload($attachment);

        return $attachment->fresh();
    }

    public function verify(LeaveAttachment $attachment, ?string $remarks = null, ?int $userId = null): LeaveAttachment
    {
        if ($attachment->status !== AttachmentStatus::Pending) {
            throw new \RuntimeException('Only pending attachments can be verified.');
        }

        $attachment->update([
            'status' => AttachmentStatus::Verified->value,
            'remarks' => $remarks,
            'verified_by' => $userId ?? auth()->id(),
            'verified_at' => now(),
        ]);

        $this->notifyEmployeeAboutVerification($attachment->fresh());

        return $attachment->fresh();
    }

    public function reject(LeaveAttachment $attachment, ?string $remarks = null, ?int $userId = null): LeaveAttachment
    {
        if ($attachment->status !== AttachmentStatus::Pending) {
            throw new \RuntimeException('Only pending attachments can be rejected.');
        }

        $attachment->update([
            'status' => AttachmentStatus::Rejected->value,
            'remarks' => $remarks,
            'verified_by' => $userId ?? auth()->id(),
            'verified_at' => now(),
        ]);

        $this->notifyEmployeeAboutRejection($attachment->fresh());

        return $attachment->fresh();
    }

    private function notifyHrAboutUpload(LeaveAttachment $attachment): void
    {
        $hrUsers = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', 'hr');
        })->get();

        foreach ($hrUsers as $hr) {
            $hr->notify(new \App\Notifications\AttachmentUploadedNotification($attachment));
        }
    }

    private function notifyEmployeeAboutVerification(LeaveAttachment $attachment): void
    {
        $employee = $attachment->application->employee;
        if ($employee) {
            $employee->notify(new \App\Notifications\AttachmentVerifiedNotification($attachment));
        }
    }

    private function notifyEmployeeAboutRejection(LeaveAttachment $attachment): void
    {
        $employee = $attachment->application->employee;
        if ($employee) {
            $employee->notify(new \App\Notifications\AttachmentRejectedNotification($attachment));
        }
    }

    public function delete(LeaveAttachment $attachment): void
    {
        $disk = $attachment->storage_disk ?? config('filesystems.default');

        if (Storage::disk($disk)->exists($attachment->file_path)) {
            Storage::disk($disk)->delete($attachment->file_path);
        }

        $attachment->delete();
    }
}
