<?php

namespace NotificationChannels\Discord;

use Illuminate\Notifications\Notification;

class DiscordChannel
{
    /**
     * @var \NotificationChannels\Discord\Discord
     */
    protected $discord;

    /**
     * @param \NotificationChannels\Discord\Discord $discord
     */
    public function __construct(Discord $discord)
    {
        $this->discord = $discord;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @return void
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
