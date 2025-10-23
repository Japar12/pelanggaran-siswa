<?php

namespace App\Notifications;

use App\Models\Violation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class WebViolationNotification extends Notification
{
    use Queueable;

        public $violation;

    /**
     * Create a new notification instance.
     */
    public function __construct(Violation $violation)
    {
         $this->violation = $violation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Simpan ke tabel notifications
    }

     public function toDatabase($notifiable)
    {
        return [
            'title' => 'Pelanggaran Baru',
            'message' => "Siswa {$this->violation->student->name} melakukan pelanggaran: {$this->violation->type}",
            'violation_id' => $this->violation->id,
        ];
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
            //
        ];
    }
}
