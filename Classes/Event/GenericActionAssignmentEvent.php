<?php

namespace HDNET\Calendarize\Event;

final class GenericActionAssignmentEvent
{
    private $variables = [];

    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $eventName;

    public function __construct(array $variables, string $className, string $eventName)
    {
        $this->variables = $variables;
        $this->className = $className;
        $this->eventName = $eventName;
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
