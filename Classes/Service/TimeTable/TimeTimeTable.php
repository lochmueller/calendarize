<?php
/**
 * Time service
 *
 * @package Calendarize\Service\TimeTable
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service\TimeTable;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Service\RecurrenceService;
use HDNET\Calendarize\Utility\ConfigurationUtility;

/**
 * Time service
 *
 * @author Tim Lochmüller
 */
class TimeTimeTable extends AbstractTimeTable {

	/**
	 * Modify the given times via the configuration
	 *
	 * @param array         $times
	 * @param Configuration $configuration
	 *
	 * @return void
	 */
	public function handleConfiguration(array &$times, Configuration $configuration) {
		$startTime = $configuration->isAllDay() ? NULL : $configuration->getStartTime();
		$endTime = $configuration->isAllDay() ? NULL : $configuration->getEndTime();
		$baseEntry = array(
			'start_date' => $configuration->getStartDate(),
			'end_date'   => $configuration->getEndDate(),
			'start_time' => $startTime,
			'end_time'   => $endTime,
			'all_day'    => $configuration->isAllDay(),
		);
		$times[] = $baseEntry;
		$this->addFrequencyItems($times, $configuration, $baseEntry);
		$this->addRecurrenceItems($times, $configuration, $baseEntry);
	}

	/**
	 * Add frequency items
	 *
	 * @param array         $times
	 * @param Configuration $configuration
	 * @param array         $baseEntry
	 */
	protected function addFrequencyItems(array &$times, Configuration $configuration, array $baseEntry) {
		$frequencyIncrement = $this->getFrequencyIncrement($configuration);
		if (!$frequencyIncrement) {
			return;
		}
		$amountCounter = $configuration->getCounterAmount();
		$tillDate = $configuration->getTillDate();
		$maxLimit = $this->getFrequencyLimitPerItem();
		$lastLoop = $baseEntry;
		for ($i = 0; $i < $maxLimit && ($amountCounter === 0 || $i < $amountCounter); $i++) {
			$loopEntry = $this->createNextLoopEntry($lastLoop, $frequencyIncrement);

			if ($tillDate instanceof \DateTime && $loopEntry['start_date'] > $tillDate) {
				break;
			}

			$lastLoop = $loopEntry;
			$times[] = $loopEntry;
		}
	}

	/**
	 * Create the next loop entry
	 *
	 * @param array  $loopEntry
	 * @param string $modification
	 *
	 * @return mixed
	 */
	protected function createNextLoopEntry($loopEntry, $modification) {
		/** @var $startDate \DateTime */
		$startDate = clone $loopEntry['start_date'];
		$startDate->modify($modification);
		$loopEntry['start_date'] = $startDate;

		/** @var $endDate \DateTime */
		$endDate = clone $loopEntry['end_date'];
		$endDate->modify($modification);
		$loopEntry['end_date'] = $endDate;
		return $loopEntry;
	}

	/**
	 * Get the frequency date increment
	 *
	 * @param Configuration $configuration
	 *
	 * @return string
	 */
	protected function getFrequencyIncrement(Configuration $configuration) {
		$interval = $configuration->getCounterInterval() <= 1 ? 1 : $configuration->getCounterInterval();
		switch ($configuration->getFrequency()) {
			case Configuration::FREQUENCY_DAILY:
				$intervalValue = '+' . $interval . ' days';
				break;
			case Configuration::FREQUENCY_WEEKLY:
				$intervalValue = '+' . $interval . ' weeks';
				break;
			case Configuration::FREQUENCY_MONTHLY:
				if ($configuration->getRecurrence() !== Configuration::RECURRENCE_NONE) {
					return FALSE;
				}
				$intervalValue = '+' . $interval . ' months';
				break;
			case Configuration::FREQUENCY_YEARLY:
				if ($configuration->getRecurrence() !== Configuration::RECURRENCE_NONE) {
					return FALSE;
				}
				$intervalValue = '+' . $interval . ' years';
				break;
			default:
				$intervalValue = FALSE;
		}
		return $intervalValue;
	}

	/**
	 * Add recurrence items
	 *
	 * @param array         $times
	 * @param Configuration $configuration
	 * @param array         $baseEntry
	 */
	protected function addRecurrenceItems(array &$times, Configuration $configuration, array $baseEntry) {
		if ($configuration->getRecurrence() === Configuration::RECURRENCE_NONE || $configuration->getDay() === Configuration::DAY_NONE) {
			return;
		}

		$recurrenceService = new RecurrenceService();
		$amountCounter = $configuration->getCounterAmount();
		$tillDate = $configuration->getTillDate();
		$maxLimit = $this->getFrequencyLimitPerItem();
		$lastLoop = $baseEntry;
		for ($i = 0; $i < $maxLimit && ($amountCounter === 0 || $i < $amountCounter); $i++) {
			$loopEntry = $lastLoop;

			$dateTime = FALSE;
			if ($configuration->getFrequency() === Configuration::FREQUENCY_MONTHLY) {
				$dateTime = $recurrenceService->getRecurrenceForNextMonth($loopEntry['start_date'], $configuration->getRecurrence(), $configuration->getDay());
			} elseif ($configuration->getFrequency() === Configuration::FREQUENCY_YEARLY) {
				$dateTime = $recurrenceService->getRecurrenceForNextYear($loopEntry['start_date'], $configuration->getRecurrence(), $configuration->getDay());
			}
			if ($dateTime === FALSE) {
				break;
			}

			/** @var \DateInterval $interval */
			$interval = $loopEntry['start_date']->diff($dateTime);
			$frequencyIncrement = $interval->format('%R%a days');

			$loopEntry = $this->createNextLoopEntry($loopEntry, $frequencyIncrement);

			if ($tillDate instanceof \DateTime && $loopEntry['start_date'] > $tillDate) {
				break;
			}

			$lastLoop = $loopEntry;
			$times[] = $loopEntry;
		}
	}

	/**
	 * Get the limit of the frequency
	 *
	 * @return int
	 */
	protected function getFrequencyLimitPerItem() {
		$maxLimit = (int)ConfigurationUtility::get('frequencyLimitPerItem');
		if ($maxLimit <= 0) {
			$maxLimit = 300;
		}
		return $maxLimit;
	}
}
