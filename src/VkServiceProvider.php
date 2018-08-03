<?php

declare(strict_types=1);

namespace FondBot\Drivers\Vk;

use FondBot\Channels\ChannelManager;
use Illuminate\Support\ServiceProvider;

class VkServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        /** @var ChannelManager $manager */
        $manager = $this->app[ChannelManager::class];

        $manager->extend('vk', function () {
            return new VkDriver();
        });
    }
}
