<?php
/**
 * External service
 *
 * @package Calendarize\Service\TimeTable
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service\TimeTable;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\HelperUtility;
use JMBTechnologyLimited\ICalDissect\ICalEvent;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * External service
 *
 * @author Tim Lochmüller
 */
class ExternalTimeTable extends AbstractTimeTable {

	/**
	 * Seconds of 23:59:59 that mark the day end in the ICS parser
	 */
	const DAY_END = 86399;

	/**
	 * ICS reader service
	 *
	 * @var \HDNET\Calendarize\Service\IcsReaderService
	 * @inject
	 */
	protected $icsReaderService;

	/**
	 * Modify the given times via the configuration
	 *
	 * @param array         $times
	 * @param Configuration $configuration
	 *
	 * @return void
	 */
	public function handleConfiguration(array &$times, Configuration $configuration) {
		$url = $configuration->getExternalIcsUrl();
		if (!GeneralUtility::isValidUrl($url)) {
			HelperUtility::createFlashMessage('Configuration with invalid ICS URL: ' . $url, 'Index ICS URL', FlashMessage::ERROR);
			return;
		}

		$events = $this->icsReaderService->toArray($url);
		foreach ($events as $event) {
			/** @var $event ICalEvent */
			$startTime = DateTimeUtility::getDaySecondsOfDateTime($event->getStart());
			$endTime = DateTimeUtility::getDaySecondsOfDateTime($event->getEnd());
			if ($endTime === self::DAY_END) {
				$endTime = 0;
			}
			$entry = array(
				'start_date' => $event->getStart(),
				'end_date'   => $event->getEnd(),
				'start_time' => $startTime,
				'end_time'   => $endTime,
				'all_day'    => $endTime === 0,
			);
			$times[] = $entry;
		}
	}
}
