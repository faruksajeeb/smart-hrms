<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\LeaveAttachment;

class AttachmentRejectedNotification extends Notification implements ShouldQueue
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
            ->subject('Leave Attachment Rejected')
            ->line("Your attachment for leave application {$this->attachment->application->application_no} has been rejected.")
            ->line("Reason: {$this->attachment->remarks}")
            ->action('View Leave Application', url("/employee/leave/applications/{$this->attachment->application->id}"))
            ->line('Please upload a valid document.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'attachment_id' => $this->attachment->id,
            'application_id' => $this->attachment->leave_application_id,
            'application_no' => $this->attachment->application->application_no,
            'file_name' => $this->attachment->original_file_name,
            'remarks' => $this->attachment->remarks,
        ];
    }
}
