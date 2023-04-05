<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Work on flex forms.
 */
class FlexFormService extends AbstractService
{
    /**
     * Flex form data.
     */
    protected array $flexFormData = [];

    /**
     * oad the given flex form into the service.
     */
    public function load(string $xml): void
    {
        $this->flexFormData = GeneralUtility::xml2array($xml);
    }

    /**
     * Get field value from flex form configuration,
     * including checks if flex form configuration is available.
     *
     * @return string|null if nothing found, value if found
     */
    public function get(string $key, string $sheet = 'sDEF'): ?string
    {
        if (!$this->isValid()) {
            return null;
        }
        $flexFormData = $this->flexFormData['data'];

        return $flexFormData[$sheet]['lDEF'][$key]['vDEF'] ?? null;
    }

    /**
     * Check if the flex form get valid data.
     */
    public function isValid(): bool
    {
        return isset($this->flexFormData['data']);
    }
}
