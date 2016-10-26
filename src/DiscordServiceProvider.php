<?php

namespace NotificationChannels\Discord;

use Illuminate\Support\ServiceProvider;
use NotificationChannels\Discord\Commands\SetupCommand;

class DiscordServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('command.discord:setup', SetupCommand::class);

        if ($this->app->runningInConsole()) {
            $this->commands('command.discord:setup');
        }
    }

    public function boot()
    {
        $this->app->when(Discord::class)
            ->needs('$token')
            ->give($this->app->make('config')->get('services.discord.token'));
    }
}
