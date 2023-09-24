<?php

namespace HDNET\Calendarize\EventListener;

use HDNET\Calendarize\Domain\Model\PluginConfiguration;
use HDNET\Calendarize\Event\IndexRepositoryDefaultConstraintEvent;
use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class DefaultEventConstraintsListener
{
    public function __invoke(IndexRepositoryDefaultConstraintEvent $event): void
    {
        if (
            !empty($event->getIndexTypes())
            && !\in_array(Register::UNIQUE_REGISTER_KEY, $event->getIndexTypes(), true)
        ) {
            return;
        }

        $table = 'sys_category_record_mm';
        $queryBuilder = HelperUtility::getQueryBuilder($table);

        $additionalSlotArguments = $event->getAdditionalSlotArguments();

        $categoryIds = [];
        if (
            isset($additionalSlotArguments['contentRecord']['uid'])
            && MathUtility::canBeInterpretedAsInteger($additionalSlotArguments['contentRecord']['uid'])
        ) {
            $rows = $queryBuilder
                ->select('uid_local')
                ->from($table)
                ->where(
                    $queryBuilder->expr()->and(
                        $queryBuilder->expr()->eq('tablenames', $queryBuilder->quote('tt_content')),
                        $queryBuilder->expr()->eq('fieldname', $queryBuilder->quote('categories')),
                        $queryBuilder->expr()->eq(
                            'uid_foreign',
                            $queryBuilder->createNamedParameter($additionalSlotArguments['contentRecord']['uid'])
                        )
                    )
                )
                ->executeQuery()
                ->fetchAllAssociative();

            foreach ($rows as $row) {
                $categoryIds[] = (int)$row['uid_local'];
            }
        }

        if (
            isset($additionalSlotArguments['settings']['pluginConfiguration'])
            && $additionalSlotArguments['settings']['pluginConfiguration'] instanceof PluginConfiguration
        ) {
            /** @var PluginConfiguration $pluginConfiguration */
            $pluginConfiguration = $additionalSlotArguments['settings']['pluginConfiguration'];
            $categories = $pluginConfiguration->getCategories();
            foreach ($categories as $category) {
                $categoryIds[] = $category->getUid();
            }
        }

        if (empty($categoryIds)) {
            return;
        }

        $queryBuilder = HelperUtility::getQueryBuilder($table);
        $rows = $queryBuilder
            ->select('uid_foreign')
            ->from('sys_category_record_mm')
            ->where(
                $queryBuilder->expr()->in('uid_local', $queryBuilder->createNamedParameter($categoryIds)),
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->quote($this->getTableName())),
                $queryBuilder->expr()->eq('fieldname', $queryBuilder->quote('categories'))
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $indexIds = $event->getIndexIds();
        foreach ($rows as $row) {
            $indexIds[] = (int)$row['uid_foreign'];
        }

        $indexIds[] = -1;
        $event->setIndexIds($indexIds);
    }

    /**
     * Table name.
     *
     * Note: This complete class is for the Event Model of the calendarize extension.
     * If you use an own model with special search criteria you have to register your
     * own custom Slot. If you only want the category logic for your model, you can
     * easily register an own slot that is based on this class. Then you only have
     * to overwrite the tableName property.
     */
    protected function getTableName(): string
    {
        $config = Register::getDefaultCalendarizeConfiguration();

        return (string)($config['tableName'] ?? '');
    }
}
