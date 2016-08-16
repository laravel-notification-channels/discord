<?php

namespace NotificationChannels\Discord;

use Illuminate\Support\Arr;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;
use NotificationChannels\Discord\Exceptions\CouldNotSendNotification;

class Discord
{
    /**
     * @var string
     */
    protected $baseUrl = 'https://discordapp.com/api';

    /**
     * @var \GuzzleHttp\Client
     */
    protected $http;

    /**
     * @var string
     */
    protected $token;

    /**
     * @param  \GuzzleHttp\Client  $http
     * @param  string  $token
     */
    public function __construct(HttpClient $http, $token)
    {
        $this->http = $http;
        $this->token = $token;
    }

    /**
     * @param  string  $channel
     * @param  array  $data
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function send($channel, array $data)
    {
        return $this->request('POST', 'channels/'.$channel.'/messages', $data);
    }

    /**
     * @param  mixed  $user
     * @return string
     */
    public function getPrivateChannel($user)
    {
        return $this->request('POST', 'users/@me/channels', ['recipient_id' => $user])['id'];
    }

    /**
     * @param  string  $endpoint
     * @param  array  $data
     * @return array
     * @throws \NotificationChannels\Discord\Exceptions\CouldNotSendNotification
     */
    protected function request($verb, $endpoint, array $data)
    {
        $url = rtrim($this->baseUrl, '/').'/'.ltrim($endpoint, '/');

        try {
            $response = $this->http->request($verb, $url, [
                'headers' => [
                    'Authorization' => 'Bot '.$this->token,
                ],
                'json' => $data,
            ]);
        } catch (RequestException $e) {
            throw CouldNotSendNotification::serviceRespondedWithAnHttpError($e->getResponse());
        } catch (\Exception $e) {
            throw CouldNotSendNotification::serviceCommunicationError($e);
        }

        $body = json_decode($response->getBody(), true);

        if (Arr::get($body, 'code', 0) > 0) {
            throw CouldNotSendNotification::serviceRespondedWithAnApiError($body);
        }

        return $body;
    }
}
