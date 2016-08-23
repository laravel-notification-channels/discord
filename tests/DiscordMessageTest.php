<?php

namespace NotificationChannels\Discord\Tests;

use NotificationChannels\Discord\DiscordMessage;

class DiscordMessageTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function it_accepts_the_message_body_during_initialization()
    {
        $message = new DiscordMessage('a message');

        $this->assertEquals('a message', $message->body);
    }

    /** @test */
    public function it_provides_a_create_method()
    {
        $message = DiscordMessage::create('a message');

        $this->assertEquals('a message', $message->body);
    }

    /** @test */
    public function it_can_set_the_body()
    {
        $message = new DiscordMessage;

        $message->body('a message');

        $this->assertEquals('a message', $message->body);
    }
}
