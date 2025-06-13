<?php

namespace App\Notifications;


use App\Models\Community;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommunityApplicationReceived extends Notification
{
    use Queueable;


    protected $applicant;
    protected $community;

    

   public function __construct(User $applicant, Community $community)
    {
        $this->applicant = $applicant;
        $this->community = $community;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
        return ['database']; // Stores the notification in the database

    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'applicant_id' => $this->applicant->id,
            'applicant_name' => $this->applicant->first_name,
            'community_id' => $this->community->id,
            'community_name' => $this->community->community,
            'message' => "{$this->applicant->first_name} has applied to join {$this->community->community}.",
        ];
    }
}
