<?php

declare(strict_types=1);

namespace FondBot\Drivers\VkCommunity;

use GuzzleHttp\Client;
use FondBot\Contracts\Channels\User;
use FondBot\Contracts\Channels\Driver;
use FondBot\Contracts\Conversation\Keyboard;
use FondBot\Contracts\Channels\OutgoingMessage;
use FondBot\Contracts\Channels\ReceivedMessage;
use FondBot\Channels\Exceptions\InvalidChannelRequest;
use FondBot\Contracts\Channels\Extensions\WebhookVerification;

class VkCommunityDriver extends Driver implements WebhookVerification
{
    const API_VERSION = '5.53';
    const API_URL = 'https://api.vk.com/method/';

    private $guzzle;

    /** @var User|null */
    private $sender;

    public function __construct(Client $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    /**
     * Configuration parameters.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'access_token',
            'confirmation_token',
        ];
    }

    /**
     * Verify incoming request data.
     *
     * @throws InvalidChannelRequest
     */
    public function verifyRequest(): void
    {
        $type = $this->getRequest('type');
        $object = $this->getRequest('object');

        if ($type === null || $type !== 'message_new') {
            throw new InvalidChannelRequest('Invalid type');
        }

        if ($object === null) {
            throw new InvalidChannelRequest('Invalid object');
        }

        if (!isset($object['user_id'])) {
            throw new InvalidChannelRequest('Invalid user_id');
        }

        if (!isset($object['body'])) {
            throw new InvalidChannelRequest('Invalid body');
        }
    }

    /**
     * Get message sender.
     *
     * @return User
     * @throws \FondBot\Channels\Exceptions\InvalidChannelRequest
     */
    public function getUser(): User
    {
        if ($this->sender !== null) {
            return $this->sender;
        }

        $userId = (string) $this->getRequest('object')['user_id'];
        $request = $this->guzzle->get(self::API_URL.'users.get', [
            'query' => [
                'user_ids' => $userId,
                'v' => self::API_VERSION,
            ],
        ]);
        $response = json_decode($request->getBody()->getContents(), true);

        return $this->sender = new VkCommunityUser($response['response'][0]);
    }

    /**
     * Get message received from sender.
     *
     * @return ReceivedMessage
     */
    public function getMessage(): ReceivedMessage
    {
        return new VkCommunityReceivedMessage($this->getRequest('object'));
    }

    /**
     * Send reply to participant.
     *
     * @param User          $sender
     * @param string        $text
     * @param Keyboard|null $keyboard
     *
     * @return OutgoingMessage
     */
    public function sendMessage(User $sender, string $text, Keyboard $keyboard = null): OutgoingMessage
    {
        $message = new VkCommunityOutgoingMessage($sender, $text, $keyboard);
        $query = array_merge($message->toArray(), [
            'access_token' => $this->getParameter('access_token'),
            'v' => self::API_VERSION,
        ]);

        $this->guzzle->get(self::API_URL.'messages.send', [
            'query' => $query,
        ]);

        return $message;
    }

    /**
     * Whether current request type is verification.
     *
     * @return bool
     */
    public function isVerificationRequest(): bool
    {
        return $this->getRequest('type') === 'confirmation';
    }

    /**
     * Run webhook verification and respond if required.
     *
     * @return mixed
     */
    public function verifyWebhook()
    {
        return $this->getParameter('confirmation_token');
    }
}
