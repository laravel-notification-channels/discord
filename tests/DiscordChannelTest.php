<?php

namespace NotificationChannels\Discord\Tests;

use Mockery;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client as HttpClient;
use NotificationChannels\Discord\Discord;
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;
use NotificationChannels\Discord\Exceptions\CouldNotSendNotification;

class DiscordChannelTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_can_send_a_notification()
    {
        $http = Mockery::mock(HttpClient::class);
        $http->shouldReceive('request')
            ->once()
            ->with('POST', 'https://discordapp.com/api/channels/0123456789/messages', [
                'headers' => [
                    'Authorization' => 'Bot super-secret',
                ],
                'json' => ['content' => 'Hello, Discord!'],
            ])
            ->andReturn(new Response(200));

        $discord = new Discord($http, 'super-secret');
        $channel = new DiscordChannel($discord);

        $channel->send(new TestNotifiable, new TestNotification);
    }

    /** @test */
    public function it_throws_an_exception_when_it_could_not_send_the_notification()
    {
        $this->setExpectedException(CouldNotSendNotification::class);

        $http = Mockery::mock(HttpClient::class);
        $http->shouldReceive('request')
            ->once()
            ->andThrow(\Exception::class);

        $discord = new Discord($http, 'super-secret');
        $channel = new DiscordChannel($discord);

        $channel->send(new TestNotifiable, new TestNotification);
    }

    /** @test */
    public function it_throws_an_exception_when_an_api_error_is_returned()
    {
        $this->setExpectedException(CouldNotSendNotification::class);

        $response = Mockery::mock(Response::class);
        $response->shouldReceive('getBody')
            ->once()
            ->andReturn('{"code": 10003, "message": "Unknown Channel"}');

        $http = Mockery::mock(HttpClient::class);
        $http->shouldReceive('post')
            ->once()
            ->andReturn($response);

        $discord = new Discord($http, 'super-secret');
        $channel = new DiscordChannel($discord);

        $channel->send(new TestNotifiable, new TestNotification);
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

class TestNotification extends \Illuminate\Notifications\Notification
{
    public function toDiscord()
    {
        return (new DiscordMessage)
            ->body('Hello, Discord!');
    }
}
