<?php

namespace HDNET\Calendarize\EventListener;

use HDNET\Calendarize\Domain\Model\PluginConfiguration;
use HDNET\Calendarize\Event\IndexRepositoryDefaultConstraintEvent;
use HDNET\Calendarize\Register;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class DefaultEventConstraintsListener
{
    public function __invoke(IndexRepositoryDefaultConstraintEvent $event)
    {
        if (!empty($event->getIndexTypes()) && !\in_array(Register::UNIQUE_REGISTER_KEY, $event->getIndexTypes(), true)) {
            return;
        }

        $table = 'sys_category_record_mm';
        $db = HelperUtility::getDatabaseConnection($table);
        $q = $db->createQueryBuilder();

        $additionalSlotArguments = $event->getAdditionalSlotArguments();

        $categoryIds = [];
        if (isset($additionalSlotArguments['contentRecord']['uid']) && MathUtility::canBeInterpretedAsInteger($additionalSlotArguments['contentRecord']['uid'])) {
            $rows = $q->select('uid_local')
                ->from($table)
                ->where(
                    $q->expr()->andX(
                        $q->expr()->eq('tablenames', $q->quote('tt_content')),
                        $q->expr()->eq('fieldname', $q->quote('categories')),
                        $q->expr()->eq('uid_foreign', $q->createNamedParameter($additionalSlotArguments['contentRecord']['uid']))
                    )
                )
                ->execute()
                ->fetchAll();

            foreach ($rows as $row) {
                $categoryIds[] = (int)$row['uid_local'];
            }
        }

        if (isset($additionalSlotArguments['settings']['pluginConfiguration']) && $additionalSlotArguments['settings']['pluginConfiguration'] instanceof PluginConfiguration) {
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

        $q->resetQueryParts();
        $rows = $q->select('uid_foreign')
            ->from('sys_category_record_mm')
            ->where(
                $q->expr()->in('uid_local', $categoryIds),
                $q->expr()->eq('tablenames', $q->quote($this->getTableName())),
                $q->expr()->eq('fieldname', $q->quote('categories'))
            )
            ->execute()
            ->fetchAll();

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
     * If you use a own model with special search criteria you have to register your
     * own custom Slot. If you only want the category logic for your model, you can
     * easily register a own slot that is based on this class. Thean you only have
     * to overwrite the tableName property.
     *
     * @return string
     */
    protected function getTableName()
    {
        $config = Register::getDefaultCalendarizeConfiguration();

        return $config['tableName'];
    }
}
