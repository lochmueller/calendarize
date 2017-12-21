<?php

/**
 * RealURL features.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Features;

/**
 * RealURL features.
 */
interface SpeakingUrlInterface
{
    /**
     * Get the base for the realurl alias.
     *
     * @return string
     */
    public function getRealUrlAliasBase(): string;
}
