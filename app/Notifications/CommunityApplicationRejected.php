<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Community;

class CommunityApplicationRejected extends Notification
{
    use Queueable;

    public $community;
    public $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Community $community, string $reason = 'Application rejected')
    {
        $this->community = $community;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => "Your application to join '{$this->community->community}' has been rejected.",
            'reason' => $this->reason,
            'community_id' => $this->community->id,
            'community_name' => $this->community->community,
            'type' => 'community_application_rejected'
        ];
    }
}
