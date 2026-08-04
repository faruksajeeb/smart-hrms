<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\LeaveApplication;

class DelegateDeclinedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public LeaveApplication $application) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Acting Person Declined Delegation')
            ->line("{$this->application->delegate->name} has declined the Acting Person assignment for leave application {$this->application->application_no}.")
            ->line("Remarks: {$this->application->delegate_remarks}")
            ->action('View Leave Application', url("/employee/leave/applications/{$this->application->id}"))
            ->line('Please assign a new Acting Person or contact HR.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'application_no' => $this->application->application_no,
            'delegate_name' => $this->application->delegate->name,
            'delegate_remarks' => $this->application->delegate_remarks,
        ];
    }
}
