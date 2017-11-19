<?php

declare(strict_types=1);

namespace FondBot\Drivers\VkCommunity\Types;

use FondBot\Drivers\Type;

class User extends Type
{
    private $uid;
    private $firstName;
    private $lastName;
    private $deactivated;
    private $hidden;
    private $verified;
    private $blacklisted;
    private $sex;
    private $bdate;
    private $city;
    private $country;
    private $homeTown;
    private $photo50;
    private $photo100;
    private $photo200Orig;
    private $photo200;
    private $photo400Orig;
    private $photoMax;
    private $photoMaxOrig;
    private $online;
    private $lists;
    private $domain;
    private $hasMobile;
    private $contacts;
    private $site;
    private $education;
    private $universities;
    private $schools;
    private $status;
    private $lastSeen;
    private $followersCount;
    private $commonCount;
    private $counters;
    private $occupation;
}
