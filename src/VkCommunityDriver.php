<?php

declare(strict_types=1);

namespace FondBot\Drivers\VkCommunity;

use FondBot\Drivers\Chat;
use FondBot\Drivers\User;
use FondBot\Drivers\Driver;
use FondBot\Drivers\CommandHandler;
use FondBot\Drivers\ReceivedMessage;
use FondBot\Drivers\TemplateCompiler;
use FondBot\Drivers\Exceptions\InvalidRequest;
use FondBot\Drivers\Extensions\WebhookVerification;

class VkCommunityDriver extends Driver implements WebhookVerification
{
    const API_VERSION = '5.53';
    const API_URL = 'https://api.vk.com/method/';

    /** @var User|null */
    private $sender;

    /**
     * Get template compiler instance.
     *
     * @return TemplateCompiler|null
     */
    public function getTemplateCompiler(): ?TemplateCompiler
    {
        return null;
    }

    /**
     * Get command handler instance.
     *
     * @return CommandHandler
     */
    public function getCommandHandler(): CommandHandler
    {
        return new VkCommunityCommandHandler($this, $this->http);
    }

    /**
     * Whether current request type is verification.
     *
     * @return bool
     */
    public function isVerificationRequest(): bool
    {
        return $this->request->getParameter('type') === 'confirmation';
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
        $type = $this->request->getParameter('type');
        $object = $this->request->getParameter('object');

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
     * Get chat.
     *
     * @return Chat
     */
    public function getChat(): Chat
    {
        return new Chat(
            (string) $this->request->getParameter('object.user_id'),
            ''
        );
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

        $userId = (string) $this->request->getParameter('object.user_id');
        $request = $this->http->get(self::API_URL.'users.get', [
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
        return new VkCommunityReceivedMessage($this->request->getParameter('object'));
    }
}
