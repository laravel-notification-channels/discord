<?php

namespace NotificationChannels\Discord\Exceptions;

use Exception;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;

class CouldNotSendNotification extends Exception
{
    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param int $code
     * @param \Exception $exception
     *
     * @return static
     */
    public static function serviceRespondedWithAnHttpError(ResponseInterface $response, $code, $exception)
    {
        $message = "Discord responded with an HTTP error: {$response->getStatusCode()}";

        if ($error = Arr::get(json_decode($response->getBody(), true), 'message')) {
            $message .= ": $error";
        }

        return new static($message, $code, $exception);
    }

    /**
     * @param array $response
     * @param int $code
     *
     * @return static
     */
    public static function serviceRespondedWithAnApiError(array $response, $code, $exception = null)
    {
        return new static("Discord responded with an API error: {$response['code']}: {$response['message']}", $code);
    }

    /**
     * @param \Exception $exception
     *
     * @return static
     */
    public static function serviceCommunicationError(Exception $exception)
    {
        return new static("Communication with Discord failed: {$exception->getCode()}: {$exception->getMessage()}", $exception->getCode(), $exception);
    }
}
