<?php

declare(strict_types=1);

namespace FondBot\Drivers\VkCommunity\Types;

use FondBot\Drivers\Type;

class Geo extends Type
{
    private $type;
    private $coordinates;

    /** @var Place */
    private $place;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getCoordinates(): ?string
    {
        return $this->coordinates;
    }

    public function getPlace(): ?Place
    {
        return $this->place;
    }
}
