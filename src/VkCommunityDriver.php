<?php

declare(strict_types=1);

namespace FondBot\Drivers\VkCommunity;

use GuzzleHttp\Client;
use FondBot\Drivers\User;
use FondBot\Drivers\Driver;
use FondBot\Drivers\Command;
use FondBot\Drivers\ReceivedMessage;
use FondBot\Drivers\Commands\SendMessage;
use FondBot\Drivers\Exceptions\InvalidRequest;
use FondBot\Drivers\Extensions\WebhookVerification;

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

    /**
     * Verify incoming request data.
     *
     * @throws InvalidRequest
     */
    public function verifyRequest(): void
    {
        $type = $this->getRequest('type');
        $object = $this->getRequest('object');

        if ($type === null || $type !== 'message_new') {
            throw new InvalidRequest('Invalid type');
        }

        if ($object === null) {
            throw new InvalidRequest('Invalid object');
        }

        if (!isset($object['user_id'])) {
            throw new InvalidRequest('Invalid user_id');
        }

        if (!isset($object['body'])) {
            throw new InvalidRequest('Invalid body');
        }
    }

    /**
     * Get message sender.
     *
     * @return User
     * @throws InvalidRequest
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
        $user = $response['response'][0];

        return $this->sender = new User(
            (string) $user['id'],
            $user['first_name'].' '.$user['last_name']
        );
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
     * Handle command.
     *
     * @param Command $command
     */
    public function handle(Command $command): void
    {
        if ($command instanceof SendMessage) {
            $this->handleSendMessageCommand($command);
        }
    }

    /**
     * Send reply to participant.
     *
     * @param SendMessage $command
     */
    protected function handleSendMessageCommand(SendMessage $command): void
    {
        $message = new VkCommunityOutgoingMessage($command->recipient, $command->text, $command->keyboard);
        $query = array_merge($message->toArray(), [
            'access_token' => $this->getParameter('access_token'),
            'v' => self::API_VERSION,
        ]);

        $this->guzzle->get(self::API_URL.'messages.send', [
            'query' => $query,
        ]);
    }
}
