<?php

/**
 * Check if the given Index is on the given day.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\DateTime;

use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\IndexUtility;
use HDNET\Calendarize\ViewHelpers\AbstractViewHelper;

/**
 * Check if the given Index is on the given day.
 */
class IndexOnDayViewHelper extends AbstractViewHelper
{
    /**
     * Init arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('day', \DateTimeInterface::class, 'Day to check against the Indices', true);
        $this->registerArgument('index', Index::class, 'Index to check against day', false, null);
        $this->registerArgument('indices', \Iterator::class, 'Indices to check against day', false, []);
        $this->registerArgument('modification', 'string', 'Apply Modifier to the Date', false, '');
    }

    /**
     * Check if the index or one of the given indices is on the given day.
     *
     * @return bool
     */
    public function render()
    {
        /** @var \DateTimeInterface $day */
        $day = $this->arguments['day'];
        /** @var ?Index $index */
        $index = $this->arguments['index'];
        /** @var array<Index> $indices */
        $indices = $this->arguments['indices'];
        /** @var string $modification */
        $modification = $this->arguments['modification'];

        $day = DateTimeUtility::normalizeDateTimeSingle($day->format('d.m.Y'));
        $baseDay = clone $day;
        if ('' !== $modification) {
            $baseDay->modify($modification);
        }

        $baseDay->setTime(0, 0, 0);
        $startTime = clone $baseDay;
        $baseDay->setTime(23, 59, 59);
        $endTime = clone $baseDay;

        if ($index instanceof Index) {
            $indices[] = $index;
        }
        foreach ($indices as $index) {
            /** @var $index Index */
            if (IndexUtility::isIndexInRange($index, $startTime, $endTime)) {
                return true;
            }
        }

        return false;
    }
}
