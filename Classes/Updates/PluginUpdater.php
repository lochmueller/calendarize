<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Updates;

use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;

#[UpgradeWizard('calendarize_pluginUpdater')]
class PluginUpdater extends AbstractUpdate
{
    protected string $title = 'Calendarize Plugin Updater';

    protected string $description = 'This wizard migrates the switchableControllerActions in all existing ' .
        'plugins to the new list types. The permissions in BE groups are updated as well to allow all new ' .
        'list types where necessary.';

    protected array $migrationMap = [
        'Calendar->list;Calendar->detail' => 'calendarize_listdetail',
        'Calendar->list' => 'calendarize_list',
        'Calendar->detail' => 'calendarize_detail',
        'Calendar->search' => 'calendarize_search',
        'Calendar->result' => 'calendarize_result',
        'Calendar->latest' => 'calendarize_latest',
        'Calendar->single' => 'calendarize_single',
        'Calendar->year' => 'calendarize_year',
        'Calendar->quarter' => 'calendarize_quarter',
        'Calendar->month' => 'calendarize_month',
        'Calendar->week' => 'calendarize_week',
        'Calendar->day' => 'calendarize_day',
        'Calendar->past' => 'calendarize_past',
        'Booking->booking;Booking->send' => 'calendarize_booking',
    ];

    public function __construct(
        protected readonly QueryBuilder $contentElementsQueryBuilder,
        protected readonly QueryBuilder $backendGroupsQueryBuilder,
        protected readonly FlexFormTools $flexFormTools,
        protected readonly FlexFormService $flexFormService,
    ) {}

    /**
     * @var string
     */
    public const OLD_LIST_TYPE = 'calendarize_calendar';

    public function executeUpdate(): bool
    {
        $this->migratePlugins();
        $this->migrateBackendUserGroupPermissions();

        return true;
    }

    protected function migratePlugins(): void
    {
        $contentElements = $this->getContentElementsToMigrate();

        $this->output->writeln('Start migration of ' . \count($contentElements) . ' plugins.');

        foreach ($contentElements as $contentElement) {
            $flexForm = $this->flexFormService->convertFlexFormContentToArray($contentElement['pi_flexform']);
            $newListType = $this->getNewListType($flexForm['switchableControllerActions'] ?? '');
            $flexFormData = $this->removeFlexFormSettingsNotForListType($contentElement, $newListType);

            if (\count($flexFormData['data']) > 0) {
                $newFlexform = $this->array2xml($flexFormData);
            } else {
                $newFlexform = '';
            }

            $this->updateContentElement($contentElement['uid'], $newListType, $newFlexform);
        }

        $this->output->writeln('');
        $this->output->writeln('');
        $this->output->writeln('Migration of plugins finished.');
    }

    protected function getContentElementsToMigrate(): array
    {
        $this->contentElementsQueryBuilder->getRestrictions()->removeAll()->add(
            GeneralUtility::makeInstance(DeletedRestriction::class),
        );
        $constraintsForActions = [];
        foreach ($this->migrationMap as $action => $_) {
            $constraintsForActions[] = $this->contentElementsQueryBuilder->expr()->like(
                'pi_flexform',
                $this->contentElementsQueryBuilder->createNamedParameter(
                    '%>' . $this->contentElementsQueryBuilder->escapeLikeWildcards(htmlspecialchars($action)) . '<%',
                ),
            );
        }

        return $this->contentElementsQueryBuilder
            ->select('uid', 'list_type', 'pi_flexform', 'pid', 'CType')
            ->from('tt_content')
            ->where(
                $this->contentElementsQueryBuilder->expr()->eq(
                    'CType',
                    $this->contentElementsQueryBuilder->createNamedParameter('list'),
                ),
                $this->contentElementsQueryBuilder->expr()->eq(
                    'list_type',
                    $this->contentElementsQueryBuilder->createNamedParameter(self::OLD_LIST_TYPE),
                ),
                $this->contentElementsQueryBuilder->expr()->or(...$constraintsForActions),
            )
            ->executeQuery()
            ->fetchAllAssociative();
    }

    protected function getNewListType(string $actions): string
    {
        return $this->migrationMap[$actions] ?? self::OLD_LIST_TYPE;
    }

    protected function removeFlexFormSettingsNotForListType(array $contentElement, string $newListType): array
    {
        // Update record with new list_type (this is needed because FlexFormTools
        // looks up those values in the given record and assumes they're up-to-date)
        $contentElement['list_type'] = $newListType;
        $newFlexform = $this->flexFormTools->cleanFlexFormXML('tt_content', 'pi_flexform', $contentElement);
        $flexFormData = GeneralUtility::xml2array($newFlexform);

        return $this->removeEmptyFlexFormSheets($flexFormData);
    }

    protected function removeEmptyFlexFormSheets(array $flexFormData): array
    {
        foreach ($flexFormData['data'] as $sheetKey => $sheetData) {
            // Remove empty sheets
            if (!\is_array($flexFormData['data'][$sheetKey]['lDEF']) || !\count($flexFormData['data'][$sheetKey]['lDEF']) > 0) {
                unset($flexFormData['data'][$sheetKey]);
            }
        }

        return $flexFormData;
    }

    protected function array2xml(array $input = []): string
    {
        $options = [
            'parentTagMap' => [
                'data' => 'sheet',
                'sheet' => 'language',
                'language' => 'field',
                'el' => 'field',
                'field' => 'value',
                'field:el' => 'el',
                'el:_IS_NUM' => 'section',
                'section' => 'itemType',
            ],
            'disableTypeAttrib' => 2,
        ];
        $spaceInd = 4;
        $output = GeneralUtility::array2xml($input, '', 0, 'T3FlexForms', $spaceInd, $options);

        return '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>' . LF . $output;
    }

    protected function updateContentElement(int $uid, string $newListType, string $flexform): void
    {
        $this->contentElementsQueryBuilder->update('tt_content')
            ->set('list_type', $newListType)
            ->set('pi_flexform', $flexform)
            ->where(
                $this->contentElementsQueryBuilder->expr()->in(
                    'uid',
                    $this->contentElementsQueryBuilder->createNamedParameter($uid, Connection::PARAM_INT),
                ),
            )
            ->executeStatement();
    }

    protected function migrateBackendUserGroupPermissions(): void
    {
        $groups = $this->getBackendUserGroupsToMigrate();
        $this->output->writeln('');
        $this->output->writeln('');
        $this->output->writeln('Start migration of ' . \count($groups) . ' BE groups.');

        foreach ($groups as $group) {
            $this->updateBackendUserGroup($group);
        }

        $this->output->writeln('');
        $this->output->writeln('');
        $this->output->writeln('Migration of backend groups finished.');
    }

    protected function getBackendUserGroupsToMigrate(): array
    {
        $this->backendGroupsQueryBuilder->getRestrictions()->removeAll()->add(
            GeneralUtility::makeInstance(DeletedRestriction::class),
        );

        return $this->backendGroupsQueryBuilder
            ->select('uid', 'explicit_allowdeny')
            ->from('be_groups')
            ->where(
                $this->backendGroupsQueryBuilder->expr()->like(
                    'explicit_allowdeny',
                    $this->backendGroupsQueryBuilder->createNamedParameter(
                        '%' . $this->backendGroupsQueryBuilder->escapeLikeWildcards($this->getOldListTypeForGroupPermissions()) . '%',
                    ),
                ),
                $this->backendGroupsQueryBuilder->expr()->notLike(
                    'explicit_allowdeny',
                    $this->backendGroupsQueryBuilder->createNamedParameter(
                        '%' . $this->backendGroupsQueryBuilder->escapeLikeWildcards($this->getNewListTypesForGroupPermissions()) . '%',
                    ),
                ),
            )
            ->executeQuery()
            ->fetchAllAssociative();
    }

    protected function updateBackendUserGroup(array $group): void
    {
        $default = $this->getNewListTypesForGroupPermissions();

        $searchReplace = [
            $this->getOldListTypeForGroupPermissions() . ':ALLOW' => $default,
            $this->getOldListTypeForGroupPermissions() . ':DENY' => '',
            $this->getOldListTypeForGroupPermissions() => $default,
        ];

        $newList = str_replace(array_keys($searchReplace), array_values($searchReplace), $group['explicit_allowdeny']);
        $this->backendGroupsQueryBuilder->update('be_groups')
            ->set('explicit_allowdeny', $newList)
            ->where(
                $this->backendGroupsQueryBuilder->expr()->in(
                    'uid',
                    $this->backendGroupsQueryBuilder->createNamedParameter($group['uid'], Connection::PARAM_INT),
                ),
            )
            ->executeStatement();
    }

    protected function getOldListTypeForGroupPermissions(): string
    {
        return 'tt_content:list_type:' . self::OLD_LIST_TYPE;
    }

    protected function getNewListTypesForGroupPermissions(): string
    {
        $groupPermissions = $this->getOldListTypeForGroupPermissions();
        foreach ($this->migrationMap as $newListType) {
            $groupPermissions .= ',tt_content:list_type:' . $newListType;
        }

        return $groupPermissions;
    }

    public function updateNecessary(): bool
    {
        return \count($this->getContentElementsToMigrate()) > 0 || \count($this->getBackendUserGroupsToMigrate()) > 0;
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }
}
