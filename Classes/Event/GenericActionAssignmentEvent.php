<?php

namespace HDNET\Calendarize\Event;

final class GenericActionAssignmentEvent
{
    public function __construct(
        private array $variables,
        private readonly string $className,
        private readonly string $functionName
    ) {
    }

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
        return $this->functionName;
    }

    public function setVariables(array $variables): void
    {
        $this->variables = $variables;
    }
}
