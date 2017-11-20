<?php

declare(strict_types=1);

namespace FondBot\Drivers\VkCommunity\Types;

use FondBot\Drivers\Type;

class Place extends Type
{
    private $id;
    private $title;
    private $latitude;
    private $longitude;
    private $created;
    private $icon;
    private $country;
    private $city;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function getCreated(): ?int
    {
        return $this->created;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }
}
