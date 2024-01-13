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
     * The component objects attached to the message.
     *
     * @var array
     */
    public $components;

    /**
     * @param string     $body
     * @param array|null $embed
     *
     * @return static
     */
    public static function create($body = '', $embed = [], $components = [])
    {
        return new static($body, $embed, $components);
    }

    /**
     * @param string $body
     * @param array  $embed
     */
    public function __construct($body = '', $embed = [], $components = [])
    {
        $this->body = $body;
        $this->embed = $embed;
        $this->components = $components;
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
     * Set a single embedded object.
     * 
     * TODO: Refactor to enable multiple embeds.
     * See https://discord.com/developers/docs/resources/channel#create-message
     *
     * @param array $embed
     *
     * @return $this
     */
    public function embed($embed)
    {
        $this->embed = $embed;

        return $this;
    }

    /**
     * Set the components object.
     *
     * @param array $components
     *
     * @return $this
     */
    public function components($components)
    {
        $this->components = $components;

        return $this;
    }
}
