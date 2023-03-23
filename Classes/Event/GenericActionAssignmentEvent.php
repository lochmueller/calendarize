<?php

namespace HDNET\Calendarize\Event;

final class GenericActionAssignmentEvent
{
    public function __construct(
        private array $variables,
        private readonly string $className,
        private readonly string $eventName
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

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function setVariables(array $variables): void
    {
        $this->variables = $variables;
    }
}
