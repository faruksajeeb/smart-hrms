<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\LeaveApplication;

class DelegateAcceptedNotification extends Notification implements ShouldQueue
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
            ->subject('Acting Person Accepted Delegation')
            ->line("{$this->application->delegate->name} has accepted the Acting Person assignment for your leave application {$this->application->application_no}.")
            ->action('View Leave Application', url("/employee/leave/applications/{$this->application->id}"))
            ->line('Your work handover has been acknowledged.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'application_no' => $this->application->application_no,
            'delegate_name' => $this->application->delegate->name,
        ];
    }
}
