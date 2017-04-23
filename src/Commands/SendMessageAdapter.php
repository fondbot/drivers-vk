<?php

declare(strict_types=1);

namespace FondBot\Drivers\VkCommunity\Commands;

use FondBot\Drivers\Commands\SendMessage;

class SendMessageAdapter
{
    private $message;

    public function __construct(SendMessage $message)
    {
        $this->message = $message;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'message' => $this->message->text,
            'user_id' => $this->message->recipient->getId(),
        ];
    }
}
