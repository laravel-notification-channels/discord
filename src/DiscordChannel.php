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
     * @return array
     *
     * @throws \NotificationChannels\Discord\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $channel = $notifiable->routeNotificationFor('discord', $notification)) {
            return;
        }

        $message = $notification->toDiscord($notifiable);

        $data = [
            'content' => $message->body
        ];

        if (count($message->embed) > 0) {
            $data['embeds'] = [$message->embed];
        }

        if (count($message->components) > 0) {
            $data['components'] = $message->components;
        }

        return $this->discord->send($channel, $data);
    }
}
