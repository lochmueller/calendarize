<?php

/**
 * OptionRequest
 */
namespace HDNET\Calendarize\Domain\Model\Request;

use HDNET\Calendarize\Domain\Model\AbstractModel;

/**
 * OptionRequest
 */
class OptionRequest extends AbstractModel
{

    /**
     * Sorting
     *
     * @var string
     */
    protected $sorting = 'start_date';

    /**
     * @return string
     */
    public function getSorting(): string
    {
        return $this->sorting;
    }

    /**
     * @param string $sorting
     */
    public function setSorting(string $sorting): void
    {
        $this->sorting = $sorting;
    }
}
