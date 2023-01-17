<?php

namespace HDNET\Calendarize\Service\TimeTable\Secondary;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Service\TimeTable\AbstractTimeTable;
use HDNET\Calendarize\Service\TimeTable\TimeTableInterface;
use HDNET\Calendarize\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\MathUtility;

class ManipulationTimeTable extends AbstractTimeTable implements TimeTableInterface
{
    protected FlexFormService $flexFormService;

    public function __construct(FlexFormService $flexFormService)
    {
        $this->flexFormService = $flexFormService;
    }

    private function loadFlexForm($flexFormString): array
    {
        return $this->flexFormService
            ->convertFlexFormContentToArray($flexFormString);
    }

    public function enable(): bool
    {
        return (bool)ConfigurationUtility::get('timeTableManipulation');
    }

    public function getIdentifier(): string
    {
        return 'calendarize_manipulation';
    }

    public function getLabel(): string
    {
        return 'Manipulation';
    }

    public function getTcaServiceTypeFields(): string
    {
        return 'type,flex_form';
    }

    public function getFlexForm(): string
    {
        return 'FILE:EXT:calendarize/Configuration/FlexForms/TimeTable/Manipulation.xml';
    }

    public function handleConfiguration(array &$times, Configuration $configuration)
    {
        $settings = $this->flexFormService->convertFlexFormContentToArray($configuration->getFlexForm());
        if (MathUtility::canBeInterpretedAsInteger($settings['settings']['fixedStartTime'] ?? null)) {
            foreach ($times as $key => $time) {
                $times[$key]['start_time'] = (int)$settings['settings']['fixedStartTime'];
            }
        }
        if (MathUtility::canBeInterpretedAsInteger($settings['settings']['fixedEndTime'] ?? null)) {
            foreach ($times as $key => $time) {
                $times[$key]['end_time'] = (int)$settings['settings']['fixedEndTime'];
            }
        }
    }
}
