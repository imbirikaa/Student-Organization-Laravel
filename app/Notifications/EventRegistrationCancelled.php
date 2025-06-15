<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Event;
use App\Models\EventRegistration;

class EventRegistrationCancelled extends Notification
{
    use Queueable;

    public $registration;
    public $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(EventRegistration $registration, $reason = null)
    {
        $this->registration = $registration;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $event = $this->registration->event;
        $eventDate = $event->start_date ? $event->start_date->format('M d, Y H:i') : 'TBA';

        $message = (new MailMessage)
            ->subject('❌ Registration Cancelled: ' . $event->title)
            ->greeting('Hello ' . $notifiable->name)
            ->line('We\'re sorry to inform you that your registration for "**' . $event->title . '**" has been cancelled.')
            ->line('')
            ->line('## 📋 Event Details')
            ->line('**Event:** ' . $event->title)
            ->line('**Description:** ' . ($event->description ?? 'No description available'))
            ->line('**📅 Date:** ' . $eventDate)
            ->line('**📍 Location:** ' . ($event->location ?? 'TBA'));

        if ($this->reason) {
            $message->line('')
                ->line('## 📝 Cancellation Reason')
                ->line($this->reason);
        }

        return $message
            ->line('')
            ->line('## 🔄 What\'s Next?')
            ->line('• If this was a mistake, please contact us immediately')
            ->line('• Check for alternative events that might interest you')
            ->line('• If you paid for this event, refund information will be sent separately')
            ->line('')
            ->line('If you have any questions about this cancellation, please don\'t hesitate to contact our support team.')
            ->action('Browse Other Events', url('/etkinlikler'))
            ->line('We apologize for any inconvenience caused.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $event = $this->registration->event;

        return [
            'message' => "Your registration for '{$event->title}' has been cancelled.",
            'event_id' => $event->id,
            'event_title' => $event->title,
            'event_description' => $event->description,
            'event_location' => $event->location,
            'event_start_date' => $event->start_date?->format('Y-m-d H:i:s'),
            'event_end_date' => $event->end_date?->format('Y-m-d H:i:s'),
            'registration_id' => $this->registration->id,
            'cancellation_reason' => $this->reason,
            'cancelled_at' => now()->format('Y-m-d H:i:s'),
            'type' => 'event_registration_cancelled'
        ];
    }
}
