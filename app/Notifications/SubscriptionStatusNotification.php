<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionStatusNotification extends Notification
{
    use Queueable;

    protected $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Subscription Status Update')
            ->line("Your subscription has been updated to {$this->subscription->tier}")
            ->line("Status: {$this->subscription->status}")
            ->line("Valid until: " . $this->subscription->end_date->format('Y-m-d'))
            ->action('View Subscription', url('/dashboard/subscription'));
    }

    public function toArray($notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'tier' => $this->subscription->tier,
            'status' => $this->subscription->status,
            'end_date' => $this->subscription->end_date,
        ];
    }
} 