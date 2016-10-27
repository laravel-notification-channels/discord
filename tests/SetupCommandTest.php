<?php

namespace NotificationChannels\Discord\Tests;

use Mockery;
use WebSocket\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Contracts\Console\Kernel;
use GuzzleHttp\Exception\RequestException;
use Orchestra\Testbench\TestCase as Orchestra;
use NotificationChannels\Discord\Commands\SetupCommand;

class SetupCommandTest extends Orchestra
{
    /** @test */
    public function it_requires_a_bot_token()
    {
        $command = Mockery::mock(SetupCommand::class.'[error]', [new HttpClient]);

        $command->shouldReceive('error')->with('You must paste your Discord token (App Bot User token) into your `services.php` config file.')->once();
        $command->shouldReceive('error')->with('View the README for more info: https://github.com/laravel-notification-channels/discord#installation')->once();

        $this->app[Kernel::class]->registerCommand($command);

        $returnCode = $this->artisan('discord:setup');

        $this->assertEquals($returnCode, -1);
    }

    /** @test */
    public function it_tells_the_user_to_connect_the_bot_to_their_discord_server()
    {
        $command = Mockery::mock(SetupCommand::class.'[confirm,ask,warn]', [new HttpClient, 'my-token']);

        $command->shouldReceive('confirm')->with('Is the bot already added to your server?')->once()->andReturn(false);
        $command->shouldReceive('ask')->with('What is your Discord app client ID?')->once()->andReturn('my-client-id');
        $command->shouldReceive('warn')->with('Add the bot to your server by visiting this link: https://discordapp.com/oauth2/authorize?&client_id=my-client-id&scope=bot&permissions=0');
        $command->shouldReceive('confirm')->with('Continue?', true)->once()->andReturn(false);

        $this->app[Kernel::class]->registerCommand($command);

        $returnCode = $this->artisan('discord:setup');

        $this->assertEquals($returnCode, -1);
    }

    /** @test */
    public function it_gives_a_websocket_client_for_the_given_gateway()
    {
        $command = new SetupCommand(new HttpClient, 'my-token');

        $socket = $command->getSocket('my-gateway');

        $this->assertInstanceOf(Client::class, $socket);
    }

    /** @test */
    public function it_fetches_the_websocket_gateway_url()
    {
        $http = Mockery::mock(HttpClient::class);

        $http->shouldReceive('get')->with('https://discordapp.com/api/gateway', [
            'headers' => [
                'Authorization' => 'Bot my-token',
            ],
        ])->once()->andReturn(new Response(200, [], json_encode(['url' => 'wss://test-gateway.discord.gg'])));

        $command = new SetupCommand($http, 'my-token');

        $gateway = $command->getGateway();

        $this->assertEquals($gateway, 'wss://test-gateway.discord.gg');
    }

    /** @test */
    public function it_returns_a_default_websocket_gateway_url()
    {
        $http = Mockery::mock(HttpClient::class);

        $http->shouldReceive('get')->with('https://discordapp.com/api/gateway', [
            'headers' => [
                'Authorization' => 'Bot my-token',
            ],
        ])->once()->andThrow(new RequestException('Not found', Mockery::mock(Request::class), new Response(404, [], json_encode(['message' => 'Not found']))));

        $command = Mockery::mock(SetupCommand::class.'[warn]', [$http, 'my-token']);

        $command->shouldReceive('warn')->with("Could not get a websocket gateway address, defaulting to 'wss://gateway.discord.gg'.")->once();

        $gateway = $command->getGateway();

        $this->assertEquals($gateway, 'wss://gateway.discord.gg');
    }

    /** @test */
    public function it_connects_to_the_discord_gateway()
    {
        $command = Mockery::mock(SetupCommand::class.'[getGateway,getSocket,confirm,ask,warn,info]', [new HttpClient, 'my-token']);
        $socket = Mockery::mock(Client::class, ['wss://gateway.discord.gg']);

        $socket->shouldReceive('send')->with(json_encode([
            'op' => 2,
            'd' => [
                'token' => 'my-token',
                'v' => 3,
                'compress' => false,
                'properties' => [
                    '$os' => PHP_OS,
                    '$browser' => 'laravel-notification-channels-discord',
                    '$device' => 'laravel-notification-channels-discord',
                    '$referrer' => '',
                    '$referring_domain' => '',
                ],
            ],
        ]))->once();
        $socket->shouldReceive('receive')->once()->andReturn(json_encode(['t' => 'READY']));

        $command->shouldReceive('confirm')->with('Is the bot already added to your server?')->once()->andReturn(true);
        $command->shouldReceive('warn')->with("Attempting to identify the bot with Discord's websocket gateway...")->once();
        $command->shouldReceive('getGateway')->once()->andReturn('wss://gateway.discord.gg');
        $command->shouldReceive('warn')->with("Connecting to 'wss://gateway.discord.gg'...")->once();
        $command->shouldReceive('getSocket')->with('wss://gateway.discord.gg')->once()->andReturn($socket);
        $command->shouldReceive('info')->with('Your bot has been identified by Discord and can now send API requests!')->once();

        $this->app[Kernel::class]->registerCommand($command);

        $this->artisan('discord:setup');
    }

    /** @test */
    public function it_notifies_the_user_of_a_failed_identification_attempt()
    {
        $command = Mockery::mock(SetupCommand::class.'[getGateway,getSocket,confirm,ask,warn,error]', [new HttpClient, 'my-token']);
        $socket = Mockery::mock(Client::class, ['wss://gateway.discord.gg']);

        $socket->shouldReceive('send')->with(json_encode([
            'op' => 2,
            'd' => [
                'token' => 'my-token',
                'v' => 3,
                'compress' => false,
                'properties' => [
                    '$os' => PHP_OS,
                    '$browser' => 'laravel-notification-channels-discord',
                    '$device' => 'laravel-notification-channels-discord',
                    '$referrer' => '',
                    '$referring_domain' => '',
                ],
            ],
        ]))->once();
        $socket->shouldReceive('receive')->once()->andReturn(json_encode(['op' => 4004]));

        $command->shouldReceive('confirm')->with('Is the bot already added to your server?')->once()->andReturn(true);
        $command->shouldReceive('warn')->with("Attempting to identify the bot with Discord's websocket gateway...")->once();
        $command->shouldReceive('getGateway')->once()->andReturn('wss://gateway.discord.gg');
        $command->shouldReceive('warn')->with("Connecting to 'wss://gateway.discord.gg'...")->once();
        $command->shouldReceive('getSocket')->with('wss://gateway.discord.gg')->once()->andReturn($socket);
        $command->shouldReceive('error')->with('Discord responded with an error while trying to identify the bot: {"op":4004}')->once();

        $this->app[Kernel::class]->registerCommand($command);

        $returnCode = $this->artisan('discord:setup');

        $this->assertEquals($returnCode, -1);
    }
}
