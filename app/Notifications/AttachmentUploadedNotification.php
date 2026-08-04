<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\LeaveAttachment;

class AttachmentUploadedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public LeaveAttachment $attachment) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Leave Attachment Uploaded')
            ->line("A new attachment has been uploaded for leave application {$this->attachment->application->application_no} by {$this->attachment->uploader->name}.")
            ->action('Review Attachment', url("/hr/leave/applications/{$this->attachment->application->id}"))
            ->line('Please verify the attachment.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'attachment_id' => $this->attachment->id,
            'application_id' => $this->attachment->leave_application_id,
            'application_no' => $this->attachment->application->application_no,
            'file_name' => $this->attachment->original_file_name,
            'uploaded_by' => $this->attachment->uploader->name,
        ];
    }
}
