<?php

namespace NotificationChannels\Discord\Tests;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;
use NotificationChannels\Discord\Discord;
use NotificationChannels\Discord\Exceptions\CouldNotSendNotification;

class DiscordTest extends BaseTest
{
    /** @test */
    public function it_can_get_a_private_channel_for_a_user()
    {
        $http = Mockery::mock(HttpClient::class);
        $http->shouldReceive('request')
            ->once()
            ->with('POST', 'https://discordapp.com/api/users/@me/channels', [
                'headers' => [
                    'Authorization' => 'Bot super-secret',
                ],
                'json' => ['recipient_id' => 'some-user-id'],
            ])
            ->andReturn(new Response(200, [], json_encode(['id' => 'some-channel-id'])));

        $discord = new Discord($http, 'super-secret');

        $this->assertEquals('some-channel-id', $discord->getPrivateChannel('some-user-id'));
    }

    /** @test */
    public function it_throws_an_exception_when_it_receives_an_http_error()
    {
        $this->expectException(CouldNotSendNotification::class);
        $this->expectExceptionMessage('Discord responded with an HTTP error: 404: Not found');

        $http = Mockery::mock(HttpClient::class);
        $http->shouldReceive('request')
            ->once()
            ->with('POST', 'https://discordapp.com/api/channels/some-channel-id/messages', [
                'headers' => [
                    'Authorization' => 'Bot super-secret',
                ],
                'json' => ['content' => 'a message'],
            ])
            ->andThrow(new RequestException('Not found', Mockery::mock(Request::class), new Response(404, [], json_encode(['message' => 'Not found']))));

        $discord = new Discord($http, 'super-secret');

        $discord->send('some-channel-id', ['content' => 'a message']);
    }

    /** @test */
    public function it_throws_an_exception_when_it_could_not_talk_to_discord()
    {
        $this->expectException(CouldNotSendNotification::class);
        $this->expectExceptionMessage('Communication with Discord failed');

        $http = Mockery::mock(HttpClient::class);
        $http->shouldReceive('request')
            ->once()
            ->with('POST', 'https://discordapp.com/api/channels/some-channel-id/messages', [
                'headers' => [
                    'Authorization' => 'Bot super-secret',
                ],
                'json' => ['content' => 'a message'],
            ])
            ->andThrow(new RequestException('Some error', Mockery::mock(Request::class)));

        $discord = new Discord($http, 'super-secret');

        $discord->send('some-channel-id', ['content' => 'a message']);
    }

    /** @test */
    public function it_wraps_and_rethrows_a_caught_exception()
    {
        $this->expectException(CouldNotSendNotification::class);
        $this->expectExceptionMessage('Communication with Discord failed');

        $http = Mockery::mock(HttpClient::class);
        $http->shouldReceive('request')
            ->once()
            ->with('POST', 'https://discordapp.com/api/channels/some-channel-id/messages', [
                'headers' => [
                    'Authorization' => 'Bot super-secret',
                ],
                'json' => ['content' => 'a message'],
            ])
            ->andThrow(new Exception('Some unexpected error'));

        $discord = new Discord($http, 'super-secret');

        $discord->send('some-channel-id', ['content' => 'a message']);
    }

    /** @test */
    public function it_throws_an_exception_if_the_api_responds_with_an_error()
    {
        $this->expectException(CouldNotSendNotification::class);
        $this->expectExceptionMessage('Discord responded with an API error: 10003: Unknown channel');

        $http = Mockery::mock(HttpClient::class);
        $http->shouldReceive('request')
            ->once()
            ->with('POST', 'https://discordapp.com/api/channels/some-channel-id/messages', [
                'headers' => [
                    'Authorization' => 'Bot super-secret',
                ],
                'json' => ['content' => 'a message'],
            ])
            ->andReturn(new Response(200, [], json_encode(['code' => 10003, 'message' => 'Unknown channel'])));

        $discord = new Discord($http, 'super-secret');

        $discord->send('some-channel-id', ['content' => 'a message']);
    }
}
