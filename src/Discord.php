<?php

namespace NotificationChannels\Discord;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;
use NotificationChannels\Discord\Exceptions\CouldNotSendNotification;

class Discord
{
    /**
     * Discord API base URL.
     *
     * @var string
     */
    protected $baseUrl = 'https://discord.com/api';

    /**
     * API HTTP client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * Discord API token.
     *
     * @var string
     */
    protected $token;

    /**
     * @param \GuzzleHttp\Client $http
     * @param string $token
     */
    public function __construct(HttpClient $http, $token)
    {
        $this->httpClient = $http;
        $this->token = $token;
    }

    /**
     * Send a message to a Discord channel.
     *
     * @param string $channel
     * @param array $data
     *
     * @return array
     */
    public function send($channel, array $data)
    {
        return $this->request('POST', 'channels/'.$channel.'/messages', $data);
    }

    /**
     * Get a private channel with another Discord user from their snowflake ID.
     *
     * @param string $userId
     *
     * @return string
     */
    public function getPrivateChannel($userId)
    {
        return $this->request('POST', 'users/@me/channels', ['recipient_id' => $userId])['id'];
    }

    /**
     * Perform an HTTP request with the Discord API.
     *
     * @param string $verb
     * @param string $endpoint
     * @param array $data
     *
     * @return array
     *
     * @throws \NotificationChannels\Discord\Exceptions\CouldNotSendNotification
     */
    protected function request($verb, $endpoint, array $data)
    {
        $url = rtrim($this->baseUrl, '/').'/'.ltrim($endpoint, '/');

        try {
            $response = $this->httpClient->request($verb, $url, [
                'headers' => [
                    'Authorization' => 'Bot '.$this->token,
                ],
                'json' => $data,
            ]);
        } catch (RequestException $exception) {
            if ($response = $exception->getResponse()) {
                throw CouldNotSendNotification::serviceRespondedWithAnHttpError($response);
            }

            throw CouldNotSendNotification::serviceCommunicationError($exception);
        } catch (Exception $exception) {
            throw CouldNotSendNotification::serviceCommunicationError($exception);
        }

        $body = json_decode($response->getBody(), true);

        if (Arr::get($body, 'code', 0) > 0) {
            throw CouldNotSendNotification::serviceRespondedWithAnApiError($body);
        }

        return $body;
    }
}
