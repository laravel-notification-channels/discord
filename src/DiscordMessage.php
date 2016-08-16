<?php

namespace NotificationChannels\Discord;

class DiscordMessage
{
    /**
     * The text content of the message.
     *
     * @var string
     */
    public $body;

    /**
     * @param  string $body
     *
     * @return static
     */
    public static function create($body = '')
    {
        return new static($body);
    }

    /**
     * @param  string  $body
     */
    public function __construct($body = '')
    {
        $this->body = $body;
    }

    /**
     * Set the text content of the message.
     *
     * @param  string  $body
     * @return $this
     */
    public function body($body)
    {
        $this->body = $body;

        return $this;
    }
}
