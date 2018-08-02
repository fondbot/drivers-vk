<?php

declare(strict_types=1);

namespace FondBot\Drivers\Vk\Types;

use FondBot\Drivers\Type;

class User extends Type
{
    private $uid;
    private $firstName;
    private $lastName;
    private $nickname;
    private $screenName;

    public function getUid(): ?int
    {
        return $this->uid;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function getScreenName(): ?string
    {
        return $this->screenName;
    }
}
