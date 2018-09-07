# Discord notification channel for Laravel 5.6+

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laravel-notification-channels/discord.svg?style=flat-square)](https://packagist.org/packages/laravel-notification-channels/discord)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/laravel-notification-channels/discord/master.svg?style=flat-square)](https://travis-ci.org/laravel-notification-channels/discord)
[![StyleCI](https://styleci.io/repos/65772492/shield)](https://styleci.io/repos/65772492)
[![Quality Score](https://img.shields.io/scrutinizer/g/laravel-notification-channels/discord.svg?style=flat-square)](https://scrutinizer-ci.com/g/laravel-notification-channels/discord)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/laravel-notification-channels/discord/master.svg?style=flat-square)](https://scrutinizer-ci.com/g/laravel-notification-channels/discord/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel-notification-channels/discord.svg?style=flat-square)](https://packagist.org/packages/laravel-notification-channels/discord)

This package makes it easy to send notifications using the [Discord bot API](https://discordapp.com/developers/docs/intro) with Laravel.

## Contents

- [Discord notification channel for Laravel 5.6+](#discord-notification-channel-for-laravel-56)
    - [Contents](#contents)
    - [Installation](#installation)
        - [Setting up your Discord bot](#setting-up-your-discord-bot)
    - [Usage](#usage)
        - [Available Message methods](#available-message-methods)
    - [Changelog](#changelog)
    - [Testing](#testing)
    - [Security](#security)
    - [Contributing](#contributing)
    - [Credits](#credits)
    - [License](#license)


## Installation

You can install the package via composer:

```bash
composer require laravel-notification-channels/discord
```

Next, you must load the service provider:

```php
// config/app.php
'providers' => [
    // ...
    NotificationChannels\Discord\DiscordServiceProvider::class,
],
```

### Setting up your Discord bot

1. [Create a Discord application.](https://discordapp.com/developers/applications/me/create)
2. Click the `Create a Bot User` button on your Discord application.
3. Paste your bot's API token, found under `App Bot User`, in your `services.php` config file:

    ```php
    // config/services.php
    'discord' => [
        'token' => 'YOUR_API_TOKEN',
    ],
    ```

4. Add the bot to your server and identify it by running the artisan command:

    ```shell
    php artisan discord:setup
    ```

## Usage

In every model you wish to be notifiable via Discord, you must add a channel ID property to that model accessible through a `routeNotificationForDiscord` method:

```php
class Guild extends Eloquent
{
    use Notifiable;

    public function routeNotificationForDiscord()
    {
        return $this->discord_channel;
    }
}
```

> **NOTE**: Discord handles direct messages as though they are a regular channel. If you wish to allow users to receive direct messages from your bot, you will need to create a private channel with that user.
> An example workflow may look like the following:
>
> 1. Your `users` table has two discord columns: `discord_user` and `discord_channel`
> 2. When a user updates their Discord user ID (`discord_user`), generate and save a channel ID (`discord_channel`)
> 3. Return the user's `discord_channel` in the `routeNotificationForDiscord` method on the User model
>
> You can generate direct message channels by using the `getPrivateChannel` method in `NotificationChannels\Discord\Discord`:
>
> ```php
> use NotificationChannels\Discord\Discord;
> // ...
>
> class UserDiscordSettingsController
> {
>     public function store(Request $request)
>     {
>         $user = $request->input('discord_user');
>         $channel = app(Discord::class)->getPrivateChannel($user);
>
>         Auth::user()->update([
>             'discord_user' => $user,
>             'discord_channel' => $channel,
>         ]);
>     }
> }
> ```

You may now tell Laravel to send notifications to Discord channels in the `via` method:

```php
// ...
use NotificationChannels\Discord\DiscordChannel;
use NotificationChannels\Discord\DiscordMessage;

class GameChallengeNotification extends Notification
{
    public $challenger;

    public $game;

    public function __construct(Guild $challenger, Game $game)
    {
        $this->challenger = $challenger;
        $this->game = $game;
    }

    public function via($notifiable)
    {
        return [DiscordChannel::class];
    }

    public function toDiscord($notifiable)
    {
        return DiscordMessage::create("You have been challenged to a game of *{$this->game->name}* by **{$this->challenger->name}**!");
    }
}
```

### Available Message methods

* `body(string)`: Set the content of the message. ([Supports basic markdown](https://support.discordapp.com/hc/en-us/articles/210298617-Markdown-Text-101-Chat-Formatting-Bold-Italic-Underline-))
* `embed(array)`: Set the embedded content. ([View embed structure](https://discordapp.com/developers/docs/resources/channel#embed-object))

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

```bash
$ composer test
```

## Security

If you discover any security related issues, please email cs475x@icloud.com instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Cody Scott](https://github.com/codyphobe)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [LICENSE](LICENSE.md) for more information.
