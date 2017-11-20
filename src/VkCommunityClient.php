<?php

declare(strict_types=1);

namespace FondBot\Drivers\VkCommunity;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use FondBot\Drivers\VkCommunity\Types\User;
use FondBot\Channels\Exceptions\DriverException;

class VkCommunityClient
{
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

    /**
     * @param array|string $userIds
     * @param array|string $fields
     * @param string $nameCase
     *
     * @return array|\FondBot\Drivers\Type|User|\stdClass
     *
     * @throws DriverException
     */
    public function getUsers(array $userIds, array $fields = ['screen_name', 'nickname'], string $nameCase = 'nom')
    {
        $userIds = implode(',', $userIds);
        $fields = implode(',', $fields);

        $response = $this->get('users.get', compact('userIds', 'fields', 'nameCase'));

        return User::createFromJson($response[0]);
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
    public function get(string $endpoint, array $parameters = [])
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

        $response = $this->guzzle->request('GET', $this->getBaseUrl().'/'.$endpoint, ['query' => $parameters]);

        return $this->parseResponse($response);
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

        return $json->response;
    }
}
