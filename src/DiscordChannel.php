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
            [
                'name' => 'content',
                'contents' => $message->body,
            ],
        ];

        if (count($message->embed) > 0) {
            $data[] = [
                'name' => 'embeds',
                'contents' => [$message->embed],
            ];
        }

        if (count($message->components) > 0) {
            $data[] = [
                'name' => 'components',
                'contents' => $message->components,
            ];
        }

        foreach ($message->files ?? [] as $i => $file) {
            $data[] = [
                'name' => "files[$i]",
                'contents' => $file,
            ];
        }

        $data = [
            'multipart' => $data,
        ];

        return $this->discord->send($channel, $data);

//        $data = [
//            'content' => $message->body
//        ];
//
//        if (count($message->embed) > 0) {
//            $data['embeds'] = [$message->embed];
//        }
//
//        if (count($message->components) > 0) {
//            $data['components'] = $message->components;
//        }
//
//        if (count($message->files) > 0) {
//            $data['files'] = $message->files;
//        }

//        return $this->discord->send($channel, $data);
    }
}
