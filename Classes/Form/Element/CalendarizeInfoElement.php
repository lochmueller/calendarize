<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Form\Element;

use HDNET\Calendarize\Service\TcaInformation;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CalendarizeInfoElement extends AbstractFormElement
{
    public function render(): array
    {
        $result = $this->initializeResultArray();

        $parameters = $this->data['parameterArray']['fieldConf']['config']['parameters'];

        $previewLimit = 10;
        if (isset($parameters['items'])) {
            $previewLimit = (int)$parameters['items'];
        }

        /** @var TcaInformation $tcaInformation */
        $tcaInformation = GeneralUtility::makeInstance(TcaInformation::class);

        $result['html'] = $tcaInformation->renderPreviewField(
            (string)$this->data['tableName'],
            (int)$this->data['vanillaUid'],
            $previewLimit,
        );

        return $result;
    }
}
