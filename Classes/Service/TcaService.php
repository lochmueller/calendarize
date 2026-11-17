<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Domain\Model\Configuration;
use HDNET\Calendarize\Domain\Model\ConfigurationInterface;
use HDNET\Calendarize\Utility\DateTimeUtility;
use HDNET\Calendarize\Utility\HelperUtility;
use HDNET\Calendarize\Utility\TranslateUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

/**
 * TCA service.
 */
class TcaService extends AbstractService
{
    /**
     * The configuration only affects dates that are already over.
     */
    public const IMPACT_PAST = 'past';

    /**
     * The configuration (also) affects dates of today or of the future.
     */
    public const IMPACT_FUTURE = 'future';

    /**
     * Runtime cache for the already calculated impacts.
     *
     * @var array<int, string|null>
     */
    protected static array $impactCache = [];

    /**
     * Render the configuration title.
     */
    public function configurationTitle(array &$params, ?object $_ = null): void
    {
        $row = $params['row'];
        $this->migrateFormEngineRow($row);

        $handling = \is_array($row['handling']) ? array_shift($row['handling']) : $row['handling'];
        $title = '<b>' . TranslateUtility::get('configuration.type.' . $row['type'])
            . ' (' . TranslateUtility::get('configuration.handling.' . $handling) . ')</b><br /> ';
        switch ($row['type']) {
            case ConfigurationInterface::TYPE_TIME:
                $title .= $this->getConfigurationTitleTime($row);
                break;
            case ConfigurationInterface::TYPE_GROUP:
                $title .= $this->getConfigurationGroupTitle($row);
                break;
            case ConfigurationInterface::TYPE_EXTERNAL:
                $title .= 'URL: ' . $row['external_ics_url'];
                break;
        }

        // The impact is only relevant for the inline records in the backend form. Other
        // contexts (e.g. the event label in record lists) can skip the calculation.
        $impact = false !== ($params['calculateImpact'] ?? true) ? $this->getConfigurationImpact($row) : null;
        if (null !== $impact) {
            $title = '<span data-impact="' . htmlspecialchars($impact) . '">' . $title . '</span>';
        }

        $params['title'] .= $title;
    }

    /**
     * Check, if the given configuration row has any impact in the past (all dates are over)
     * or in the future (there is at least one date of today or later). Returns null, if the
     * impact can not be calculated (e.g. new records or external calendars, that would have
     * to be downloaded for every single rendering of the label).
     */
    protected function getConfigurationImpact(array $row): ?string
    {
        $uid = $row['uid'] ?? null;
        if (!MathUtility::canBeInterpretedAsInteger($uid)) {
            return null;
        }
        $uid = (int)$uid;

        if (!\in_array($row['type'], [
            ConfigurationInterface::TYPE_TIME,
            ConfigurationInterface::TYPE_GROUP,
        ], true)) {
            return null;
        }

        if (\array_key_exists($uid, self::$impactCache)) {
            return self::$impactCache[$uid];
        }

        self::$impactCache[$uid] = $this->calculateConfigurationImpact($uid);

        return self::$impactCache[$uid];
    }

    /**
     * Execute the configuration to check, if there is any date of today or of the future.
     */
    protected function calculateConfigurationImpact(int $uid): ?string
    {
        $record = BackendUtility::getRecordWSOL('tx_calendarize_domain_model_configuration', $uid);
        if (!\is_array($record)) {
            return null;
        }

        $dataMapper = GeneralUtility::makeInstance(DataMapper::class);
        $configuration = $dataMapper->map(Configuration::class, [$record])[0] ?? null;
        if (!($configuration instanceof Configuration)) {
            return null;
        }

        $timeTableService = GeneralUtility::makeInstance(TimeTableService::class);
        // The label is rendered in a "dry run" context, so do not bother the editor with
        // flash messages of the executed configurations (the indexer already handles them).
        $times = HelperUtility::silentFlashMessages(
            static fn(): array => $timeTableService->getTimesByConfiguration($configuration),
        );

        if (empty($times)) {
            return null;
        }

        $today = DateTimeUtility::resetTime(DateTimeUtility::getNow());
        foreach ($times as $time) {
            $date = $time['end_date'] ?? $time['start_date'] ?? null;
            if (!($date instanceof \DateTimeInterface)) {
                continue;
            }
            if ($date >= $today) {
                return self::IMPACT_FUTURE;
            }
        }

        return self::IMPACT_PAST;
    }

    /**
     * Add configurations to event titles.
     */
    public function eventTitle(array &$params, ?object $_): void
    {
        // if record has no title
        if (!MathUtility::canBeInterpretedAsInteger($params['row']['uid'] ?? '')) {
            return;
        }

        // base title
        $table = $params['table'];
        unset($GLOBALS['TCA'][$table]['ctrl']['label_userFunc']);
        $params['title'] = $params['row']['title'];
        $GLOBALS['TCA'][$table]['ctrl']['label_userFunc'] = self::class . '->eventTitle';

        // base record
        $fullRow = BackendUtility::getRecordWSOL($table, $params['row']['uid']);

        $transPointer = $GLOBALS['TCA'][$table]['ctrl']['transOrigPointerField'] ?? false; // e.g. l10n_parent
        if ($transPointer && (int)($fullRow[$transPointer] ?? 0) > 0) {
            return;
        }

        $configurations = isset($fullRow['calendarize']) ? GeneralUtility::intExplode(
            ',',
            $fullRow['calendarize'],
            true,
        ) : [];

        if (empty($configurations)) {
            return;
        }

        foreach ($configurations as $key => $value) {
            $paramsInternal = [
                'row' => BackendUtility::getRecordWSOL(
                    'tx_calendarize_domain_model_configuration',
                    $value,
                ) ?? [],
                'title' => '',
                'calculateImpact' => false,
            ];
            $this->configurationTitle($paramsInternal);
            $configurations[$key] = strip_tags($paramsInternal['title']);
        }
        $params['title'] .= ' / ' . implode(' / ', $configurations);
    }

    /**
     * The new FormEngine prepare the select as array
     * Migrate it to the old behavior.
     */
    protected function migrateFormEngineRow(array &$row): void
    {
        $migrateFields = ['type', 'frequency', 'groups'];
        foreach ($migrateFields as $field) {
            $row[$field] = \is_array($row[$field]) ? array_shift($row[$field]) : $row[$field];
        }
    }

    /**
     * Get group title.
     */
    protected function getConfigurationGroupTitle(array $row): string
    {
        $title = '';
        $groups = GeneralUtility::trimExplode(',', (string)$row['groups'], true);
        foreach ($groups as $key => $id) {
            $row = BackendUtility::getRecord('tx_calendarize_domain_model_configurationgroup', $id);
            if (!empty($row)) {
                $groups[$key] = $row['title'] . ' (#' . $id . ')';
            }
        }
        if ($groups) {
            $title .= '<ul><li>' . implode('</li><li>', $groups) . '</li></ul>';
        }

        return $title;
    }

    /**
     * Get the title for a configuration time.
     */
    protected function getConfigurationTitleTime(array $row): string
    {
        $title = '';
        if ($row['start_date']) {
            try {
                $dateStart = BackendUtility::date((is_string($row['start_date']) ? new \DateTime($row['start_date']) : $row['start_date'])->getTimestamp());
                if ($row['end_date']) {
                    $dateEnd = BackendUtility::date((is_string($row['end_date']) ? new \DateTime($row['end_date']) : $row['end_date'])->getTimestamp());
                } else {
                    $dateEnd = $dateStart;
                }

                $title .= $dateStart;
                if ($dateStart !== $dateEnd) {
                    $title .= ' - ' . $dateEnd;
                }
            } catch (\Exception $exception) {
            }
        }
        if ($row['all_day']) {
            $title .= ' ' . TranslateUtility::get('tx_calendarize_domain_model_index.all_day');
        } elseif ($row['start_time']) {
            $title .= ' <br />' . BackendUtility::time(($row['start_time'] instanceof \DateTimeImmutable ? $row['start_time']->getTimestamp() : $row['start_time']) % DateTimeUtility::SECONDS_DAY, false);
            if ($row['open_end_time']) {
                $title .= ' - ' . TranslateUtility::get('open_end');
            } else {
                $title .= ' - ' . BackendUtility::time(($row['end_time'] instanceof \DateTimeImmutable ? $row['end_time']->getTimestamp() : $row['end_time']) % DateTimeUtility::SECONDS_DAY, false);
            }
        }
        if ($row['frequency'] && ConfigurationInterface::FREQUENCY_NONE !== $row['frequency']) {
            $title .= ' <br /><i>' . TranslateUtility::get('configuration.frequency.' . $row['frequency']) . '</i>';
        }

        return $title;
    }
}
