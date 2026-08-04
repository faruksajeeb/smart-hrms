<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\LeaveApplication;

class DelegateAssignedNotification extends Notification implements ShouldQueue
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
            ->subject('Acting Person Assigned for Leave')
            ->line("You have been assigned as the Acting Person for {$this->application->employee->name}'s leave application {$this->application->application_no}.")
            ->action('View Leave Application', url("/employee/leave/applications/{$this->application->id}"))
            ->line('Please review the leave details and take necessary action.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'application_no' => $this->application->application_no,
            'employee_name' => $this->application->employee->name,
            'leave_type' => $this->application->leaveType->leave_name,
            'start_date' => $this->application->start_date,
            'end_date' => $this->application->end_date,
        ];
    }
}
