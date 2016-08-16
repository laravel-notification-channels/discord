<?php

namespace NotificationChannels\Discord;

use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\ServiceProvider;
use NotificationChannels\Discord\Commands\SetupCommand;

class DiscordServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('command.discord:setup', SetupCommand::class);

        $this->commands([
            'command.discord:setup',
        ]);
    }

    public function boot()
    {
        $this->app->when(Discord::class)
            ->needs('$token')
            ->give($this->app['config']->get('services.discord.token'));
    }
}
