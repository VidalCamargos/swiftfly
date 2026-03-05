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
        $travelOrderStatusKey = $this->travelOrder->status?->value;
        $travelOrderStatusTranslationKey = $travelOrderStatusKey === null
            ? null
            : "notifications.travel_order.status.{$travelOrderStatusKey}";

        $travelOrderStatusText = $travelOrderStatusTranslationKey === null
            ? ''
            : __($travelOrderStatusTranslationKey);

        if ($travelOrderStatusTranslationKey !== null && $travelOrderStatusText === $travelOrderStatusTranslationKey) {
            $travelOrderStatusText = $travelOrderStatusKey ?? '';
        }

        $mailSubject = __('notifications.travel_order.status_changed.subject', [
            'status' => $travelOrderStatusText,
        ]);

        $mailDescription = __('notifications.travel_order.status_changed.description', [
            'status' => $travelOrderStatusText,
        ]);

        $mailGreeting = __('notifications.common.greeting', [
            'name' => $notifiable->name,
        ]);

        return (new MailMessage())
            ->subject($mailSubject)
            ->greeting($mailGreeting)
            ->line($mailDescription)
            ->line(__('notifications.common.closing'));
    }
}
