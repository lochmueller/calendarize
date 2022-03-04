<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Unit\Ical;

use HDNET\Calendarize\Ical\DissectEventAdapter;
use HDNET\Calendarize\Ical\ICalEvent;
use JMBTechnologyLimited\ICalDissect\ICalParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DissectEventAdapterTest extends ICalEventTest
{
    protected $tmpFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tmpFile = null;
    }

    protected function tearDown(): void
    {
        if (\is_scalar($this->tmpFile) && file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }
        parent::tearDown();
    }

    /**
     * {@inheritdoc}
     */
    protected function getEvent(string $content): ICalEvent
    {
        $this->tmpFile = GeneralUtility::tempnam('ical');
        GeneralUtility::writeFile($this->tmpFile, $content);
        $parser = new ICalParser();
        if (!$parser->parseFromFile($this->tmpFile)) {
            self::fail('Unable to open or parse temporary ical file.');
        }

        return new DissectEventAdapter($parser->getEvents()[0]);
    }

    public function testDuration()
    {
        self::markTestSkipped('The DissectEventAdapter does not support DURATION.');
        parent::testDuration();
    }
}
