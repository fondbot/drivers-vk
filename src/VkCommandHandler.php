<?php

declare(strict_types=1);

namespace FondBot\Drivers\Vk;

use FondBot\Drivers\CommandHandler;
use FondBot\Drivers\Commands\SendMessage;
use FondBot\Drivers\Commands\SendRequest;
use FondBot\Drivers\Commands\SendAttachment;

class VkCommandHandler extends CommandHandler
{
    /**
     * Handle send message command.
     *
     * @param SendMessage $command
     */
    protected function handleSendMessage(SendMessage $command): void
    {
        $payload = [
            'access_token' => $this->driver->getParameter('access_token'),
            'v' => VkDriver::API_VERSION,
            'user_id' => $command->getRecipient()->getId(),
            'message' => $command->getText(),
        ];

        $this->driver->getHttp()
            ->get(VkDriver::API_URL.'messages.send', [
                'query' => $payload,
            ]);
    }

    /**
     * Handle send attachment command.
     *
     * @param SendAttachment $command
     */
    protected function handleSendAttachment(SendAttachment $command): void
    {
        // TODO: Implement handleSendAttachment() method.
    }

    /**
     * Handle send request command.
     *
     * @param SendRequest $command
     */
    protected function handleSendRequest(SendRequest $command): void
    {
        // TODO: Implement handleSendRequest() method.
    }
}
