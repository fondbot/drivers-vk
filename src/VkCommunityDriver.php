<?php

declare(strict_types=1);

namespace FondBot\Drivers\VkCommunity;

use FondBot\Channels\Chat;
use FondBot\Channels\User;
use FondBot\Events\Unknown;
use FondBot\Channels\Driver;
use FondBot\Contracts\Event;
use Illuminate\Http\Request;
use FondBot\Contracts\Template;
use FondBot\Templates\Attachment;
use FondBot\Contracts\Channels\WebhookVerification;

class VkCommunityDriver extends Driver implements WebhookVerification
{
    private $client;

    /** {@inheritdoc} */
    public function getName(): string
    {
        return 'VK Communities';
    }

    /** {@inheritdoc} */
    public function getShortName(): string
    {
        return 'vk-community';
    }

    /** {@inheritdoc} */
    public function getDefaultParameters(): array
    {
        return [
            'access_token' => '',
            'confirmation_token' => '',
            'secret_key' => '',
        ];
    }

    /** {@inheritdoc} */
    public function getClient(): VkCommunityClient
    {
        if ($this->client !== null) {
            $this->client = new VkCommunityClient;
        }

        return $this->client;
    }

    /** {@inheritdoc} */
    public function createEvent(Request $request): Event
    {
        $chat = Chat::create($request->input('object.user_id'));

        return new Unknown;
    }

    /** {@inheritdoc} */
    public function sendMessage(Chat $chat, User $recipient, string $text, Template $template = null): void
    {
        // TODO: Implement sendMessage() method.
    }

    /** {@inheritdoc} */
    public function sendAttachment(Chat $chat, User $recipient, Attachment $attachment): void
    {
        // TODO: Implement sendAttachment() method.
    }

    /** {@inheritdoc} */
    public function sendRequest(Chat $chat, User $recipient, string $endpoint, array $parameters = []): void
    {
        // TODO: Implement sendRequest() method.
    }

    /** {@inheritdoc} */
    public function isVerificationRequest(Request $request): bool
    {
        return $request->input('type') === 'confirmation';
    }

    /** {@inheritdoc} */
    public function verifyWebhook(Request $request)
    {
        return $this->getParameters()->get('confirmation_token');
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
