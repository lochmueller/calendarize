<?php

declare(strict_types=1);

namespace HDNET\Calendarize\EventListener;

/*
 * @deprecated {@see CategoryConstraintListener} The CategoryConstraintEventListener now handles all models.
 */
class DefaultEventConstraintsListener extends CategoryConstraintEventListener
{
    public function __construct()
    {
        @trigger_error(
            'The CategoryConstraintEventListener now handles all models (incl. custom models) that use the default categories column.',
            \E_USER_DEPRECATED,
        );
    }
}
