<?php

declare(strict_types=1);

namespace FondBot\Drivers\VkCommunity;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use FondBot\Channels\Exceptions\DriverException;

class VkCommunityClient
{
    private const API_VERSION = '5.69';
    private const BASE_URL = 'https://api.vk.com/method';

    private $guzzle;
    private $token;

    public function __construct(Client $guzzle, string $token)
    {
        $this->guzzle = $guzzle;
        $this->token = $token;
    }

    public function getBaseUrl()
    {
        return self::BASE_URL;
    }

    public function setGuzzle(Client $guzzle): void
    {
        $this->guzzle = $guzzle;
    }

    public function getUsers(array $userIds, array $fields = [], string $nameCase = 'nom')
    {
        $response = $this->post('users.get', compact('userIds', 'fields', 'nameCase'));

        dd($response);
    }

    /**
     * Send request.
     *
     * @param string $endpoint
     * @param array $parameters
     * @return mixed
     *
     * @throws DriverException
     */
    public function post(string $endpoint, array $parameters = [])
    {
        // Remove parameters with null value
        $parameters = collect($parameters)
            ->mapWithKeys(function ($value, $key) {
                return [snake_case($key) => $value];
            })
            ->filter(function ($value) {
                return $value !== null;
            })
            ->toArray();
        $response = $this->guzzle->request('POST', $this->getBaseUrl().'/'.$endpoint, ['json' => $parameters]);

        return $this->parseResponse($response);
    }

    private function parseResponse(ResponseInterface $response)
    {
        $body = (string) $response->getBody();
        $json = json_decode($body);
        dd($json);
        if ($json->ok !== true) {
            throw new DriverException($body);
        }

        return $json->result;
    }
}
