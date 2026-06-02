<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Invite extends Notification
{
    protected $notification_url;
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param $notification_url
     */
    public function __construct($notification_url)
    {
        $this->notification_url=$notification_url;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(config('ucp.notifications.invite_subject'))
            ->greeting(trans('Hello').',')
            ->line(trans('A user with your data has been created in the UCP - Universal Convergence App of Serv24 GmbH'))
            ->line(trans('Accept the invitation by clicking on the button below'))
            ->action(trans('Accept Invitation'), $this->notification_url)
            ->line(trans('This link would be valid for two days. If this link is expired and you would still use it, please contact your responsible administrator.'))
            ->line(trans('If you do not want to accept the invitation, no course of action is required'));

    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
