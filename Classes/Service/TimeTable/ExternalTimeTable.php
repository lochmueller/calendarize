<?php
/**
 * External service
 *
 * @package Calendarize\Service\TimeTable
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service\TimeTable;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * External service
 *
 * @author Tim Lochmüller
 */
class ExternalTimeTable extends AbstractTimeTable {

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
	 * @todo implement
	 */
	public function handleConfiguration(array &$times, Configuration $configuration) {
		$url = $configuration->getExternalIcsUrl();
		if (!GeneralUtility::isValidUrl($url)) {
			HelperUtility::createFlashMessage('Configuration with invalid ICS URL: ' . $url, 'Index ICS URL', FlashMessage::ERROR);
			return;
		}

		HelperUtility::createFlashMessage('ICS Import is not implemented yet', 'Index ICS URL', FlashMessage::NOTICE);

		#$events = $this->icsReaderService->toArray($url);
		#DebuggerUtility::var_dump($events);
	}

}
