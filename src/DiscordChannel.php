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
        $formData = $this->toFormData($message);

        return $this->discord->send($channel, $formData);
    }

    private function toFormData(DiscordMessage $message): array
    {
        $data = [
            'content' => $message->body,
        ];

        if (count($message->embed) > 0) {
            $data['embeds'] = [$message->embed];
        }

        if (count($message->components) > 0) {
            $data['components'] = $message->components;
        }

        $formData = [
            [
                'name' => 'payload_json',
                'contents' => json_encode($data),
            ]
        ];

        foreach ($message->files as $i => $file) {
            $formData[] = [
                'name' => 'files[' . $i . ']',
                'contents' => $file,
            ];
        }

        return $formData;
    }
}
