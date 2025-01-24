<?php

namespace HDNET\Calendarize\Event;

final class GenericActionRedirectEvent
{
    public function __construct(
        private array $variables,
        private readonly string $className,
        private readonly string $functionName,
    ) {}

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getFunctionName(): string
    {
        return $this->functionName;
    }

    /**
     * @deprecated use getFunctionName instead
     */
    public function getEventName(): string
    {
        @trigger_error('Use GenericActionRedirectEvent::getFunctionName() instead', \E_USER_DEPRECATED);

        return $this->functionName;
    }

    public function setVariables(array $variables): void
    {
        $this->variables = $variables;
    }
}
