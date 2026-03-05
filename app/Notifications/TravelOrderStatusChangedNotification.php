<?php

namespace App\Notifications;

use App\Models\TravelOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TravelOrderStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly TravelOrder $travelOrder)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $travelOrderStatusText = __('notifications.travel_order.status.'.$this->travelOrder->status->value);

        return new MailMessage()
            ->subject(__('notifications.travel_order.status_changed.subject', [
                'status' => $travelOrderStatusText,
            ]))
            ->greeting(__('notifications.common.greeting', [
                'name' => $notifiable->name,
            ]))
            ->line(__('notifications.travel_order.status_changed.description', [
                'status' => $travelOrderStatusText,
            ]))
            ->line(__('notifications.common.closing'));
    }
}
