<?php

declare(strict_types=1);

/**
 * IndexUtilityTest.
 */

namespace HDNET\Calendarize\Tests\Unit\DateTime;

use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Tests\Unit\AbstractUnitTest;
use HDNET\Calendarize\Utility\IndexUtility;

/**
 * IndexUtilityTest.
 */
class IndexUtilityTest extends AbstractUnitTest
{
    public function testIndexInRange()
    {
        $index = new Index();
        $startDate = new \DateTime('23.04.1988 04:36:34');
        $endDate = clone $startDate;
        $endDate->modify('+5 hours');
        $index->setStartDate($startDate);
        $index->setAllDay(true);
        $index->setEndDate($endDate);

        $startRange = new \DateTime('23.04.1987 04:36:34');
        $endRange = new \DateTime('23.04.1989 04:36:34');

        self::assertTrue(IndexUtility::isIndexInRange($index, $startRange, $endRange), 'The index is not in the range');
    }

    public function testIndexBeforeRange()
    {
        $index = new Index();
        $startDate = new \DateTime('23.04.1980 04:36:34');
        $endDate = clone $startDate;
        $endDate->modify('+5 hours');
        $index->setStartDate($startDate);
        $index->setAllDay(true);
        $index->setEndDate($endDate);

        $startRange = new \DateTime('23.04.1987 04:36:34');
        $endRange = new \DateTime('23.04.1989 04:36:34');

        self::assertFalse(IndexUtility::isIndexInRange($index, $startRange, $endRange), 'The index is not in the range');
    }

    public function testIndexAfterRange()
    {
        $index = new Index();
        $startDate = new \DateTime('23.04.1990 04:36:34');
        $endDate = clone $startDate;
        $endDate->modify('+5 hours');
        $index->setStartDate($startDate);
        $index->setAllDay(true);
        $index->setEndDate($endDate);

        $startRange = new \DateTime('23.04.1987 04:36:34');
        $endRange = new \DateTime('23.04.1989 04:36:34');

        self::assertFalse(IndexUtility::isIndexInRange($index, $startRange, $endRange), 'The index is not in the range');
    }
}
