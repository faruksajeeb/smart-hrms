<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\LeaveAttachment;

class AttachmentVerifiedNotification extends Notification implements ShouldQueue
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
            ->subject('Leave Attachment Verified')
            ->line("Your attachment for leave application {$this->attachment->application->application_no} has been verified.")
            ->action('View Leave Application', url("/employee/leave/applications/{$this->attachment->application->id}"))
            ->line('Your leave application can now proceed for approval.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'attachment_id' => $this->attachment->id,
            'application_id' => $this->attachment->leave_application_id,
            'application_no' => $this->attachment->application->application_no,
            'file_name' => $this->attachment->original_file_name,
        ];
    }
}
