<?php

declare(strict_types=1);

namespace FondBot\Drivers\VkCommunity\Types;

use FondBot\Drivers\Type;

class Message extends Type
{
    private $id;
    private $userId;
    private $fromId;
    private $date;
    private $title;
    private $body;

    /** @var Geo */
    private $geo;

    private $attachments;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getFromId(): ?int
    {
        return $this->fromId;
    }

    public function getDate(): ?int
    {
        return $this->date;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getGeo(): ?Geo
    {
        return $this->geo;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }
}
