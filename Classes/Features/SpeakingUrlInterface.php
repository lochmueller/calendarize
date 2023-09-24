<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Features;

// @todo: still in use?
/**
 * RealURL features.
 */
interface SpeakingUrlInterface
{
    /**
     * Get the base for the realurl alias.
     */
    public function getRealUrlAliasBase(): string;
}
