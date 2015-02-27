<?php
/**
 * Time table builder service
 *
 * @package Calendarize\Service
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Service\TimeTable\AbstractTimeTable;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;

/**
 * Time table builder service
 *
 * @author Tim Lochmüller
 */
class TimeTableService extends AbstractService {

	/**
	 * Build the timetable for the given configuration matrix (sorted)
	 *
	 * @param array $ids
	 *
	 * @return array
	 */
	public function getTimeTablesByConfigurationIds(array $ids) {
		$timeTable = array();
		if (!$ids) {
			return $timeTable;
		}

		/** @var \HDNET\Calendarize\Domain\Repository\ConfigurationRepository $configRepository */
		$configRepository = HelperUtility::create('HDNET\\Calendarize\\Domain\\Repository\\ConfigurationRepository');
		foreach ($ids as $configurationUid) {
			$configuration = $configRepository->findByUid($configurationUid);
			if (!($configuration instanceof Configuration)) {
				continue;
			}

			$handler = $this->buildConfigurationHandler($configuration);
			if (!$handler) {
				HelperUtility::createFlashMessage('There is no TimeTable handler for the given configuration type: ' . $configuration->getType(), 'Index invalid', FlashMessage::ERROR);
				continue;
			}

			$handler->handleConfiguration($timeTable, $configuration);
		}

		return $timeTable;
	}

	/**
	 * Build the configuration handler
	 *
	 * @param Configuration $configuration
	 *
	 * @return bool|AbstractTimeTable
	 */
	protected function buildConfigurationHandler(Configuration $configuration) {
		$handler = 'HDNET\\Calendarize\\Service\\TimeTable\\' . ucfirst($configuration->getType()) . 'TimeTable';
		if (!class_exists($handler)) {
			return FALSE;
		}
		return HelperUtility::create($handler);
	}

}