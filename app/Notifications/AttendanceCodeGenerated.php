<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\EventRegistration;

class AttendanceCodeGenerated extends Notification
{
  use Queueable;

  public function __construct(
    public EventRegistration $registration
  ) {}

  public function via(object $notifiable): array
  {
    return ['mail', 'database'];
  }
  public function toMail(object $notifiable): MailMessage
  {
    $event = $this->registration->event;

    // Format date and time safely
    $eventDate = $event->start_date ? $event->start_date->format('M d, Y') : 'TBA';
    $eventTime = $event->start_date ? $event->start_date->format('H:i') : 'TBA';
    $eventEndDate = $event->end_date ? $event->end_date->format('M d, Y') : null;
    $eventEndTime = $event->end_date ? $event->end_date->format('H:i') : null;

    // Format duration if both start and end dates exist
    $duration = '';
    if ($event->start_date && $event->end_date) {
      $diff = $event->start_date->diff($event->end_date);
      if ($diff->days > 0) {
        $duration = $diff->days . ' day(s)';
      } else {
        $duration = $diff->format('%h hour(s) %i minute(s)');
      }
    }

    $message = (new MailMessage)
      ->subject('🎫 Your Attendance Code for ' . $event->title)
      ->greeting('Hello ' . $notifiable->name . '!')
      ->line('🎉 Congratulations! Your registration for "**' . $event->title . '**" has been confirmed.')
      ->line('')
      ->line('## 🎫 Your Attendance Code')
      ->line('**' . $this->registration->attendance_code . '**')
      ->line('*Please save this code - you\'ll need it to check in at the event.*')
      ->line('')
      ->line('## 📋 Complete Event Information')
      ->line('**Event Title:** ' . $event->title)
      ->line('**Description:** ' . ($event->description ?? 'No description available'))
      ->line('**📅 Start Date:** ' . $eventDate)
      ->line('**🕐 Start Time:** ' . $eventTime);

    // Add end date/time if different from start
    if ($eventEndDate && $eventEndDate !== $eventDate) {
      $message->line('**📅 End Date:** ' . $eventEndDate);
    }
    if ($eventEndTime && $eventEndTime !== $eventTime) {
      $message->line('**🕐 End Time:** ' . $eventEndTime);
    }

    // Add duration if available
    if ($duration) {
      $message->line('**⏱️ Duration:** ' . $duration);
    }

    $message->line('**📍 Location:** ' . ($event->location ?? 'TBA'))
      ->line('**👥 Max Participants:** ' . ($event->max_participants ?? 'Unlimited'))
      ->line('**💰 Price:** ' . ($event->price ? '$' . number_format($event->price, 2) : 'Free'))
      ->line('**📱 Contact:** ' . ($event->contact_info ?? 'Not provided'))
      ->line('')
      ->line('## ✅ Check-in Instructions')
      ->line('1. **Arrive at the event venue** on time')
      ->line('2. **Present your attendance code:** `' . $this->registration->attendance_code . '`')
      ->line('3. **Alternative:** Show this email to event staff')
      ->line('4. **Bring a valid ID** for verification')
      ->line('')
      ->line('## 📱 Manage Your Registration')
      ->action('View Event Details', url('/etkinlik/' . $event->id))
      ->line('You can also view all your attendance codes at: ' . url('/my-attendance-codes'))
      ->line('')
      ->line('## ❓ Need Help?')
      ->line('If you have any questions about this event or need assistance with check-in, please contact the event organizers.')
      ->line('')
      ->line('**Registration Details:**')
      ->line('- Registration ID: #' . $this->registration->id)
      ->line('- Registration Date: ' . $this->registration->created_at->format('M d, Y H:i'))
      ->line('- Status: ' . ucfirst($this->registration->status))
      ->line('')
      ->line('Thank you for registering! We look forward to seeing you at the event! 🎊');

    return $message;
  }
  public function toArray(object $notifiable): array
  {
    $event = $this->registration->event;

    return [
      'title' => 'Attendance Code Generated',
      'message' => 'Your attendance code for "' . $event->title . '" is: ' . $this->registration->attendance_code,
      'attendance_code' => $this->registration->attendance_code,
      'event_id' => $this->registration->event_id,
      'event_title' => $event->title,
      'event_start_date' => $event->start_date?->format('Y-m-d H:i:s'),
      'event_location' => $event->location,
      'registration_id' => $this->registration->id,
      'registration_status' => $this->registration->status,
      'type' => 'attendance_code',
      'action_url' => '/etkinlik/' . $event->id
    ];
  }
}
