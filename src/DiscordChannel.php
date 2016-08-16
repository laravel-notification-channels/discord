<?php

namespace NotificationChannels\Discord;

use Illuminate\Notifications\Notification;
use NotificationChannels\Discord\Events\MessageWasSent;
use NotificationChannels\Discord\Events\SendingMessage;
use NotificationChannels\Discord\Exceptions\CouldNotSendNotification;

class DiscordChannel
{
    /** @var \NotificationChannels\Discord\Discord */
    protected $discord;

    /**
     * @param  \NotificationChannels\Discord\Discord  $discord
     */
    public function __construct(Discord $discord)
    {
        $this->discord = $discord;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     *
     * @throws \NotificationChannels\Discord\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $channel = $notifiable->routeNotificationFor('discord')) {
            return;
        }

        $message = $notification->toDiscord($notifiable);

        $this->discord->send($channel, [
            'content' => $message->body,
        ]);
    }
}
