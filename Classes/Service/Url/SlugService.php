<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service\Url;

use HDNET\Calendarize\Event\BaseSlugGenerationEvent;
use HDNET\Calendarize\Event\SlugSuffixGenerationEvent;
use HDNET\Calendarize\Features\SpeakingUrlInterface;
use HDNET\Calendarize\Service\AbstractService;
use HDNET\Calendarize\Utility\ConfigurationUtility;
use HDNET\Calendarize\Utility\EventUtility;
use HDNET\Calendarize\Utility\ExtensionConfigurationUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\DataHandling\Model\RecordStateFactory;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;

class SlugService extends AbstractService
{
    protected const TABLE_NAME = 'tx_calendarize_domain_model_index';
    protected const SLUG_NAME = 'slug';

    protected RecordStateFactory $stateFactory;

    protected EventDispatcherInterface $eventDispatcher;

    protected bool $useDate;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->stateFactory = RecordStateFactory::forName(self::TABLE_NAME);

        $this->useDate = !(bool)ConfigurationUtility::get('disableDateInSpeakingUrl');
    }

    /**
     * Generate a slug for the record and add specific suffixes to each index,
     * e.g. spacex-falcon-9-crew-2-20210422.
     *
     * @param string $uniqueRegisterKey
     * @param array  $record
     * @param array  $neededItems
     *
     * @return array
     */
    public function generateSlugForItems(string $uniqueRegisterKey, array $record, array $neededItems): array
    {
        if (empty($neededItems)) {
            return [];
        }

        // Get domain model
        $configuration = ExtensionConfigurationUtility::get($uniqueRegisterKey);
        /** @var DomainObjectInterface $model */
        $model = EventUtility::getOriginalRecordByConfigurationInWorkspace(
            $configuration ?? [],
            (int)$record['uid'],
            $record['t3ver_wsid'] ?? 0,
        );

        $baseSlug = $this->generateBaseSlug($uniqueRegisterKey, $record, $model);

        return $this->generateSlugSuffix($uniqueRegisterKey, $baseSlug, $neededItems);
    }

    /**
     * Generate a base slug for the record.
     *
     * @param string                $uniqueRegisterKey
     * @param array                 $record
     * @param DomainObjectInterface $model
     *
     * @return string
     */
    protected function generateBaseSlug(string $uniqueRegisterKey, array $record, DomainObjectInterface $model): string
    {
        // If the model has a speaking url use it
        if ($model instanceof SpeakingUrlInterface) {
            $baseSlug = $model->getRealUrlAliasBase();
        }

        // Multiple fallbacks
        if (empty($baseSlug)) {
            $baseSlug = $record['slug']
                ?? $record['path_segment']
                ?? "$uniqueRegisterKey-{$record['uid']}";
        }

        $baseSlug = $this->getSlugHelper($record['t3ver_wsid'] ?? 0)->sanitize($baseSlug);

        return $this->eventDispatcher->dispatch(new BaseSlugGenerationEvent(
            $uniqueRegisterKey,
            $model,
            $record,
            $baseSlug,
        ))->getBaseSlug();
    }

    /**
     * Generate a suffix for all items, e.g. test-20201103.
     *
     * @param string $uniqueRegisterKey
     * @param string $base
     * @param array  $items
     *
     * @return array
     */
    protected function generateSlugSuffix(string $uniqueRegisterKey, string $base, array $items): array
    {
        $addFields = [];
        foreach ($items as $key => $item) {
            $indexSlug = $base;

            // Skip date on single event
            if ($this->useDate && 1 !== \count($items)) {
                $indexSlug .= '-' . str_replace('-', '', $item['start_date']);
            }

            $indexSlug = $this->getSlugHelper($record['t3ver_wsid'] ?? 0)->sanitize($indexSlug);
            $addFields[$key]['slug'] = $this->eventDispatcher->dispatch(new SlugSuffixGenerationEvent(
                $uniqueRegisterKey,
                $item,
                $base,
                $indexSlug,
            ))->getSlug();
        }

        return $addFields;
    }

    /**
     * Process the record and make the slug unique in the table, e.g. adds a suffix on duplicate.
     *
     * @param array    $recordData
     * @param int|null $counter
     *
     * @return string
     */
    public function makeSlugUnique(array $recordData, ?int $counter = null): string
    {
        // Create RecordState and generate slug
        $state = $this->stateFactory->fromArray(
            $recordData,
            $recordData['pid'],
            $recordData['uid'] ?? '',
        );

        /* @noinspection PhpUnhandledExceptionInspection */
        return $this->getSlugHelper($recordData['t3ver_wsid'] ?? 0)
            ->buildSlugForUniqueInTable($recordData['slug'] . ($counter ? '-' . $counter : ''), $state);
    }

    protected function getSlugHelper(int $workspaceId = 0): SlugHelper
    {
        return GeneralUtility::makeInstance(
            SlugHelper::class,
            self::TABLE_NAME,
            self::SLUG_NAME,
            $GLOBALS['TCA'][self::TABLE_NAME]['columns'][self::SLUG_NAME]['config'],
            $workspaceId,
        );
    }
}
