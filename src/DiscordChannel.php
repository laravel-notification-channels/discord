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
            'content' => $message->body,
        ];

        if (count($message->embed) > 0) {
            $data['embeds'] = [$message->embed];
        }

        if (count($message->components) > 0) {
            $data['components'] = $message->components;
        }

        if (count($message->files) > 0) {
            $data['files'] = $message->files;
        }

        $formData = [];

        foreach ($data as $key => $value) {
            $formData = array_merge($formData, self::toFormData($key, $value));
        }

        return $this->discord->send($channel, $formData);
    }

    public static function toFormData($key, mixed $data, array &$formData = []): array
    {
        if (! is_array($data)) {
            $formData[] = [
                'name' => $key,
                'contents' => $data,
            ];

            return $formData;
        }

        foreach ($data as $subKey => $value) {
            $subKey = $key . '[' . $subKey . ']';

            self::toFormData($subKey, $value, $formData);
        }

        return $formData;
    }
}
