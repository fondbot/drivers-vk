<?php

declare(strict_types=1);

namespace FondBot\Drivers\Vk;

use FondBot\Channels\Chat;
use FondBot\Channels\User;
use VK\Client\VKApiClient;
use FondBot\Events\Unknown;
use FondBot\Channels\Driver;
use FondBot\Contracts\Event;
use Illuminate\Http\Request;
use FondBot\Contracts\Template;
use FondBot\Templates\Attachment;
use FondBot\Events\MessageReceived;
use FondBot\Contracts\Channels\WebhookVerification;

class VkDriver extends Driver implements WebhookVerification
{
    private const API_VERSION = '5.80';

    protected $accessToken;
    protected $confirmationToken;
    protected $secretKey;
    protected $groupId;

    /** @var VKApiClient */
    protected $client;

    /**
     * Get gateway display name.
     *
     * This can be used for various system where human-friendly name is required.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'VK';
    }

    /**
     * Get driver short name.
     *
     * This name is used as an alias for configuration.
     *
     * @return string
     */
    public function getShortName(): string
    {
        return 'vk';
    }

    /**
     * Create API client.
     *
     * @return mixed
     */
    public function createClient(): VKApiClient
    {
        return new VKApiClient(static::API_VERSION, app()->getLocale());
    }

    /**
     * Create event based on incoming request.
     *
     * @param Request $request
     *
     * @return Event
     */
    public function createEvent(Request $request): Event
    {
        if ($request->input('secret') !== $this->secretKey) {
            return new Unknown();
        }

        $type = $request->input('type');

        switch ($type) {
            case 'message_new':
                $objectUserId = (string) $request->input('object.peer_id');
                $chat = new Chat($objectUserId);
                $from = $this->resolveUser($objectUserId);

                return new MessageReceived($chat, $from, $request->input('object.text'));
        }

        return new Unknown;
    }

    /**
     * Send message.
     *
     * @param Chat $chat
     * @param User $recipient
     * @param string $text
     * @param Template|null $template
     *
     * @throws \VK\Exceptions\Api\VKApiMessagesChatBotFeatureException
     * @throws \VK\Exceptions\Api\VKApiMessagesChatUserNoAccessException
     * @throws \VK\Exceptions\Api\VKApiMessagesDenySendException
     * @throws \VK\Exceptions\Api\VKApiMessagesForwardAmountExceededException
     * @throws \VK\Exceptions\Api\VKApiMessagesForwardException
     * @throws \VK\Exceptions\Api\VKApiMessagesKeyboardInvalidException
     * @throws \VK\Exceptions\Api\VKApiMessagesPrivacyException
     * @throws \VK\Exceptions\Api\VKApiMessagesTooLongMessageException
     * @throws \VK\Exceptions\Api\VKApiMessagesUserBlockedException
     * @throws \VK\Exceptions\VKApiException
     * @throws \VK\Exceptions\VKClientException
     */
    public function sendMessage(Chat $chat, User $recipient, string $text, Template $template = null): void
    {
        $this->client->messages()->send($this->accessToken, [
            'peer_id' => $recipient->getId(),
            'message' => $text,
        ]);
    }

    /**
     * Send attachment.
     *
     * @param Chat $chat
     * @param User $recipient
     * @param Attachment $attachment
     *
     * @throws \VK\Exceptions\Api\VKApiMessagesChatBotFeatureException
     * @throws \VK\Exceptions\Api\VKApiMessagesChatUserNoAccessException
     * @throws \VK\Exceptions\Api\VKApiMessagesDenySendException
     * @throws \VK\Exceptions\Api\VKApiMessagesForwardAmountExceededException
     * @throws \VK\Exceptions\Api\VKApiMessagesForwardException
     * @throws \VK\Exceptions\Api\VKApiMessagesKeyboardInvalidException
     * @throws \VK\Exceptions\Api\VKApiMessagesPrivacyException
     * @throws \VK\Exceptions\Api\VKApiMessagesTooLongMessageException
     * @throws \VK\Exceptions\Api\VKApiMessagesUserBlockedException
     * @throws \VK\Exceptions\VKApiException
     * @throws \VK\Exceptions\VKClientException
     */
    public function sendAttachment(Chat $chat, User $recipient, Attachment $attachment): void
    {
        $this->client->messages()->send($this->accessToken, [
            'peer_id' => $recipient->getId(),
            'attachment' => $attachment->getPath(),
        ]);
    }

    /**
     * Determine if current request is verification.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function isVerificationRequest(Request $request): bool
    {
        return
            $request->input('type') === 'confirmation' &&
            (string) $request->input('group_id') === (string) $this->groupId;
    }

    /**
     * Perform webhook verification.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function verifyWebhook(Request $request)
    {
        return $this->confirmationToken;
    }

    protected function resolveUser(string $userId): User
    {
        $response = $this->client->users()->get($this->accessToken, [
            'user_ids' => [$userId],
            'fields' => ['first_name', 'last_name', 'screen_name'],
        ]);

        $data = $response[0];

        return new User(
            $userId,
            $data['first_name'].' '.$data['last_name'],
            $data['screen_name'],
            $data
        );
    }
}
