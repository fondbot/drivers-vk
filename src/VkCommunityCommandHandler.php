<?php

declare(strict_types=1);

namespace FondBot\Drivers\VkCommunity;

use GuzzleHttp\Client;
use FondBot\Drivers\Driver;
use FondBot\Drivers\CommandHandler;
use FondBot\Drivers\Commands\SendMessage;
use FondBot\Drivers\Commands\SendRequest;
use FondBot\Drivers\Commands\SendAttachment;

class VkCommunityCommandHandler extends CommandHandler
{
    private $guzzle;

    public function __construct(Driver $driver, Client $guzzle)
    {
        parent::__construct($driver);
        $this->guzzle = $guzzle;
    }

    /**
     * Handle send message command.
     *
     * @param SendMessage $command
     */
    public function handleSendMessage(SendMessage $command): void
    {
        $payload = [
            'access_token' => $this->driver->getParameter('access_token'),
            'v' => VkCommunityDriver::API_VERSION,
            'user_id' => $command->getRecipient()->getId(),
            'message' => $command->getText(),
        ];

        $this->guzzle->get(VkCommunityDriver::API_URL.'messages.send', [
            'query' => $payload,
        ]);
    }

    /**
     * Handle send attachment command.
     *
     * @param SendAttachment $command
     */
    public function handleSendAttachment(SendAttachment $command): void
    {
        // TODO: Implement handleSendAttachment() method.
    }

    /**
     * Handle send request command.
     *
     * @param SendRequest $command
     */
    public function handleSendRequest(SendRequest $command): void
    {
        // TODO: Implement handleSendRequest() method.
    }
}
