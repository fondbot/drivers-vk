<?php

declare(strict_types=1);

namespace FondBot\Drivers\Vk;

use FondBot\Templates\Keyboard;
use FondBot\Drivers\TemplateCompiler;
use FondBot\Templates\Keyboard\Button;

class VkTemplateCompiler extends TemplateCompiler
{
    /**
     * Render keyboard.
     *
     * @param Keyboard $keyboard
     *
     * @return mixed
     */
    protected function compileKeyboard(Keyboard $keyboard)
    {
//        $buttons = collect($keyboard->getButtons())->transform(function(Button $button) {
//
//        });
    }
}
