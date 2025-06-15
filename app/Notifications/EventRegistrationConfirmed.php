<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Event;
use App\Models\EventRegistration;

class EventRegistrationConfirmed extends Notification
{
    use Queueable;

    public $registration;

    /**
     * Create a new notification instance.
     */
    public function __construct(EventRegistration $registration)
    {
        $this->registration = $registration;
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

        // Format date safely
        $eventDate = $event->start_date ? $event->start_date->format('M d, Y') : 'TBA';
        $eventTime = $event->start_date ? $event->start_date->format('H:i') : 'TBA';
        $eventEndDate = $event->end_date ? $event->end_date->format('M d, Y H:i') : null;

        $message = (new MailMessage)
            ->subject('✅ Registration Confirmed: ' . $event->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('🎉 Great news! Your registration for "**' . $event->title . '**" has been confirmed!')
            ->line('')
            ->line('## 📋 Event Details')
            ->line('**Event:** ' . $event->title)
            ->line('**Description:** ' . ($event->description ?? 'No description available'))
            ->line('**📅 Date:** ' . $eventDate)
            ->line('**🕐 Time:** ' . $eventTime);

        if ($eventEndDate) {
            $message->line('**🏁 End:** ' . $eventEndDate);
        }

        $message->line('**📍 Location:** ' . ($event->location ?? 'TBA'))
            ->line('**💰 Price:** ' . ($event->price ? '$' . number_format($event->price, 2) : 'Free'))
            ->line('**👥 Max Participants:** ' . ($event->max_participants ?? 'Unlimited'))
            ->line('')
            ->line('## 📝 Important Information')
            ->line('✅ Please arrive **15 minutes early** for smooth check-in')
            ->line('✅ Bring a **valid photo ID** for verification')
            ->line('✅ Check your email for your **attendance code**')
            ->line('✅ Save this confirmation email for your records')
            ->line('')
            ->line('## 📱 Next Steps')
            ->line('1. **Wait for your attendance code** (sent separately)')
            ->line('2. **Add the event to your calendar**')
            ->line('3. **Prepare any required materials**')
            ->action('View Event Details', url("/etkinlik/{$event->id}"))
            ->line('')
            ->line('If you need to cancel your registration or have any questions, please contact us as soon as possible.')
            ->line('')
            ->line('We look forward to seeing you at the event! 🎊');

        return $message;
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
            'message' => "Your registration for '{$event->title}' has been confirmed!",
            'event_id' => $event->id,
            'event_title' => $event->title,
            'event_description' => $event->description,
            'event_location' => $event->location,
            'event_start_date' => $event->start_date?->format('Y-m-d H:i:s'),
            'event_end_date' => $event->end_date?->format('Y-m-d H:i:s'),
            'event_price' => $event->price,
            'event_max_participants' => $event->max_participants,
            'registration_id' => $this->registration->id,
            'registration_status' => $this->registration->status,
            'registration_date' => $this->registration->created_at->format('Y-m-d H:i:s'),
            'type' => 'event_registration_confirmed'
        ];
    }
}
