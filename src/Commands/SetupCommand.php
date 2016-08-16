<?php

namespace NotificationChannels\Discord\Commands;

use WebSocket\Client;
use Illuminate\Support\Arr;
use Illuminate\Console\Command;
use GuzzleHttp\Client as HttpClient;

class SetupCommand extends Command
{
    protected $signature = 'discord:setup';

    protected $description = "Add the bot to your server(s) and identify it with Discord's gateway.";

    public function handle()
    {
        if (! $token = config('services.discord.token')) {
            $this->error('You must paste your Discord token (App Bot User token) into your `services.php` config file.');
            $this->error('View the README for more info: https://github.com/laravel-notification-channels/discord#installation');

            return -1;
        }

        if (! $this->confirm('Is the bot already added to your server?')) {
            $clientId = $this->ask('What is your Discord app client ID?');

            $this->warn('Add the bot to your server by visiting this link: https://discordapp.com/oauth2/authorize?&client_id='.$clientId.'&scope=bot&permissions=0');
        }

        $this->warn("Attempting to identify the bot with Discord's WebScoket gateway...");

        $gateway = 'wss://gateway.discord.gg';

        try {
            $response = (new HttpClient)->get('https://discordapp.com/api/gateway', [
                'headers' => [
                    'Authorization' => 'Bot '.$token,
                ],
            ]);

            $gateway = Arr::get(json_decode($response->getBody(), true), 'url', $gateway);
        } catch (\Excetion $e) {
            $this->warn("Could not get a WebSocket gateway address, defaulting to {$gateway}.");
        }

        $this->warn("Connecting to '$gateway'...");

        $client = new Client($gateway);

        // Discord requires all bots to connect via a WebSocket connection and
        // identify at least once before any HTTP API requests are allowed.
        // https://discordapp.com/developers/docs/topics/gateway#gateway-identify
        $client->send(json_encode([
            'op' => 2,
            'd' => [
                'token' => $token,
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
        ]));

        $response = $client->receive();
        $identified = Arr::get(json_decode($response, true), 't') === 'READY';

        if (! $identified) {
            $this->error("Discord responded with an error while trying to identify the bot: $response");

            return -1;
        }

        $this->info('Your bot has been identified by Discord and can now send API requests!');
    }
}
