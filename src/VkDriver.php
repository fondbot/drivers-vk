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

    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'VK';
    }

    /** {@inheritdoc} */
    public function getShortName(): string
    {
        return 'vk';
    }

    /** {@inheritdoc} */
    public function createClient(): VKApiClient
    {
        return new VKApiClient(static::API_VERSION, app()->getLocale());
    }

    /** {@inheritdoc} */
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

    /** {@inheritdoc} */
    public function sendMessage(Chat $chat, User $recipient, string $text, Template $template = null): void
    {
        $this->client->messages()->send($this->accessToken, [
            'peer_id' => $recipient->getId(),
            'message' => $text,
        ]);
    }

    /** {@inheritdoc} */
    public function sendAttachment(Chat $chat, User $recipient, Attachment $attachment): void
    {
        $this->client->messages()->send($this->accessToken, [
            'peer_id' => $recipient->getId(),
            'attachment' => $attachment->getPath(),
        ]);
    }

    /** {@inheritdoc} */
    public function isVerificationRequest(Request $request): bool
    {
        return
            $request->input('type') === 'confirmation' &&
            (string) $request->input('group_id') === (string) $this->groupId;
    }

    /** {@inheritdoc} */
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
