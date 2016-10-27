<?php

namespace NotificationChannels\Discord;

use Illuminate\Support\ServiceProvider;
use NotificationChannels\Discord\Commands\SetupCommand;

class DiscordServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->bind('command.discord:setup', SetupCommand::class);
        $this->commands('command.discord:setup');
    }

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $token = $this->app->make('config')->get('services.discord.token');

        $this->app->when(Discord::class)
            ->needs('$token')
            ->give($token);

        $this->app->when(SetupCommand::class)
            ->needs('$token')
            ->give($token);
    }
}
