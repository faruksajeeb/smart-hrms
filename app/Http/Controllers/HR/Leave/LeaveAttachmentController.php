<?php

namespace App\Http\Controllers\HR\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveAttachment;
use App\Models\LeaveApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\HR\Leave\LeaveAttachmentService;

class LeaveAttachmentController extends Controller
{
    public function __construct(protected LeaveAttachmentService $attachmentService) {}

    public function store(Request $request, LeaveApplication $application)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimetypes:application/pdf,image/jpeg,image/png,image/jpg'],
        ]);

        $file = $request->file('file');

        $policyDetail = $application->leavePolicy
            ?->details()
            ->where('leave_type_id', $application->leave_type_id)
            ->where('status', 'active')
            ->first();

        if ($policyDetail && $policyDetail->attachment_required) {
            $allowedExtensions = $policyDetail->allowed_extensions ? explode(',', $policyDetail->allowed_extensions) : ['pdf', 'jpg', 'jpeg', 'png'];
            $extension = strtolower($file->getClientOriginalExtension());

            if (!in_array($extension, $allowedExtensions)) {
                return back()->with('error', 'Invalid file type. Allowed: ' . implode(', ', $allowedExtensions));
            }

            $maxSizeMB = $policyDetail->maximum_attachment_size_mb ?? 5;
            if ($file->getSize() > $maxSizeMB * 1024 * 1024) {
                return back()->with('error', "File size exceeds maximum limit of {$maxSizeMB}MB.");
            }

            $existingCount = $application->attachments()->count();
            $maxFiles = $policyDetail->maximum_attachment_files ?? 1;
            if ($existingCount >= $maxFiles) {
                return back()->with('error', "Maximum {$maxFiles} file(s) allowed.");
            }
        }

        try {
            $attachment = $this->attachmentService->upload($application, $file);
            logger()->info('Attachment uploaded', [
                'attachment_id' => $attachment->id,
                'application_id' => $application->id,
                'file_name' => $attachment->original_file_name,
            ]);
        } catch (\Throwable $e) {
            logger()->error('Attachment upload failed', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Attachment upload failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Attachment uploaded successfully.');
    }

    public function destroy(LeaveAttachment $attachment)
    {
        $this->attachmentService->delete($attachment);

        return back()->with('success', 'Attachment deleted successfully.');
    }

    public function download(LeaveApplication $application, LeaveAttachment $attachment)
    {
        if ($attachment->leave_application_id !== $application->id) {
            abort(404);
        }

        $user = auth()->user();

        $authorized = $user->id === $application->user_id
            || $user->id === $application->employee->reporting_manager_id
            || $user->hasRole('hr')
            || $user->hasRole('admin');

        if (!$authorized) {
            abort(403);
        }

        $disk = $attachment->storage_disk ?? config('filesystems.default');
        $filePath = $attachment->file_path;

        if (!Storage::disk($disk)->exists($filePath)) {
            abort(404);
        }

        return response()->download(
            Storage::disk($disk)->path($filePath),
            $attachment->original_file_name ?? $attachment->file_name,
            [
                'Content-Type' => $attachment->mime_type,
            ]
        );
    }

    public function verify(Request $request, LeaveAttachment $attachment)
    {
        $request->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->attachmentService->verify($attachment, $request->string('remarks'));

        return back()->with('success', 'Attachment verified successfully.');
    }

    public function reject(Request $request, LeaveAttachment $attachment)
    {
        $request->validate([
            'remarks' => ['required', 'string', 'max:1000'],
        ]);

        $this->attachmentService->reject($attachment, $request->string('remarks'));

        return back()->with('success', 'Attachment rejected successfully.');
    }

    public function hrIndex(Request $request)
    {
        $query = LeaveAttachment::query()
            ->where('status', 'pending')
            ->with(['application.employee', 'application.leaveType', 'uploader']);

        if ($request->filled('application_id')) {
            $query->where('leave_application_id', $request->integer('application_id'));
        }

        $attachments = $query->orderBy('created_at', 'desc')->paginate(15);

        $applications = LeaveApplication::where('status', '!=', 'draft')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'application_no']);

        return Inertia::render('HR/Leave/Attachments/Index', [
            'attachments' => $attachments,
            'applications' => $applications,
            'filters' => [
                'application_id' => $request->integer('application_id'),
            ],
        ]);
    }
}
