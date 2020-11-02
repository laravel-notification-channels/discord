<?php

namespace NotificationChannels\Discord\Tests;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response;
use Mockery;
use NotificationChannels\Discord\Discord;
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;

class DiscordChannelTest extends BaseTest
{
    /** @test */
    public function it_can_send_a_notification()
    {
        $http = Mockery::mock(HttpClient::class);
        $http->shouldReceive('request')
            ->once()
            ->with('POST', 'https://discord.com/api/channels/0123456789/messages', [
                'headers' => [
                    'Authorization' => 'Bot super-secret',
                ],
                'json' => ['content' => 'Hello, Discord!', 'embed' => [
                    'title' => 'Object Title',
                    'url' => 'https://discord.com',
                ]],
            ])
            ->andReturn(new Response(200));

        $discord = new Discord($http, 'super-secret');
        $channel = new DiscordChannel($discord);

        $channel->send(new TestNotifiable, new TestNotification);
    }

    /** @test */
    public function it_does_not_send_a_notification_if_the_notifiable_does_not_provide_a_discord_channel()
    {
        $discord = Mockery::spy(new Discord(new HttpClient, 'super-secret'));
        $channel = new DiscordChannel($discord);

        $channel->send(new TestNotifiableWithoutRoute, new TestNotification);

        $discord->shouldNotHaveReceived('send');
    }
}

class TestNotifiable
{
    use \Illuminate\Notifications\Notifiable;

    public function routeNotificationForDiscord()
    {
        return '0123456789';
    }
}

class TestNotifiableWithoutRoute
{
    use \Illuminate\Notifications\Notifiable;
}

class TestNotification extends \Illuminate\Notifications\Notification
{
    public function toDiscord()
    {
        return (new DiscordMessage)
            ->body('Hello, Discord!')
            ->embed([
                'title' => 'Object Title',
                'url' => 'https://discord.com',
            ]);
    }
}
