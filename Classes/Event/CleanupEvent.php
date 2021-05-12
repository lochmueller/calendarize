<?php

namespace HDNET\Calendarize\Event;

final class CleanupEvent
{
    private $modus;

    private $repository;

    private $model;

    private $function;

    public function __construct($modus, $repository, $model, $function)
    {
        $this->modus = $modus;
        $this->repository = $repository;
        $this->model = $model;
        $this->function = $function;
    }

    public function getModus()
    {
        return $this->modus;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function getFunction()
    {
        return $this->function;
    }

    public function setFunction($function): void
    {
        $this->function = $function;
    }
}
