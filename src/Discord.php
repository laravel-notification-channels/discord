<?php

namespace NotificationChannels\Discord;

use Exception;
use Illuminate\Support\Arr;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use NotificationChannels\Discord\Exceptions\CouldNotSendNotification;

class Discord
{
    /**
     * Discord API base URL.
     *
     * @var string
     */
    protected $baseUrl = 'https://discordapp.com/api';

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
     * @param       $channel
     * @param array $data
     *
     * @return array
     * @throws CouldNotSendNotification
     */
    public function send($channel, array $data)
    {
        return $this->request('POST', $channel, $data);
    }

    /**
     * @param $user
     *
     * @return mixed
     * @throws CouldNotSendNotification
     */
    public function getPrivateChannel($user)
    {
        return $this->request('POST', 'users/@me/channels', ['recipient_id' => $user])['id'];
    }

    /**
     * Perform an HTTP request with the Discord API.
     *
     * @param string $verb
     * @param string $url
     * @param array $data
     *
     * @return array
     *
     * @throws \NotificationChannels\Discord\Exceptions\CouldNotSendNotification
     */
    protected function request($verb, $url, array $data)
    {
        try {
            $response = $this->httpClient->request($verb, $url, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'form_params' => $data
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
