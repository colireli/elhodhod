<?php


namespace App\Channels;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Env;


class WhatsappChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return bool
     */
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toWhatsapp($notifiable);

        //SEND HERE

        return true;

    }

}
