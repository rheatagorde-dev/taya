<?php

namespace App\Notifications;

use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Alert $alert
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $detainee = $this->alert->detainee;
        $level = strtoupper(str_replace('_', ' ', $this->alert->alert_level));

        return (new MailMessage)
            ->subject("TAYA Alert — {$level}: {$detainee->full_name}")
            ->greeting("Alert Notification — {$level}")
            ->line("**Detainee:** {$detainee->full_name}")
            ->line("**Charge:** {$detainee->charge_description}")
            ->line("**Days Detained:** {$detainee->days_detained}")
            ->line("**Alert Level:** {$level}")
            ->line("**Recommended Action:** {$this->alert->recommended_action}")
            ->action('View Alert Details', url("/alerts/{$this->alert->id}"))
            ->line('Please take immediate action on this alert.');
    }
}
