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
     * The embedded object attached to the message.
     *
     * @var array
     */
    public $embed;

    /**
     * @param string     $body
     * @param array|null $embed
     *
     * @return static
     */
    public static function create($body = '', $embed = [])
    {
        return new static($body, $embed);
    }

    /**
     * @param string $body
     * @param array  $embed
     */
    public function __construct($body = '', $embed = [])
    {
        $this->body = $body;
        $this->embed = $embed;
    }

    /**
     * Set the text content of the message.
     *
     * @param string $body
     *
     * @return $this
     */
    public function body($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Set the embedded object.
     *
     * @param $embed
     *
     * @return $this
     */
    public function embed($embed)
    {
        $this->embed = $embed;

        return $this;
    }
}
