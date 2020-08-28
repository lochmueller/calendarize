<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Listener;

use HDNET\Calendarize\Register;
use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;

class CalendarizeField
{
    public function __invoke(AlterTableDefinitionStatementsEvent $event): void
    {
        $event->addSqlData($this->getCalendarizeDatabaseString());
    }

    /**
     * Get the calendarize string for the registered tables.
     *
     * @return string
     */
    protected function getCalendarizeDatabaseString()
    {
        $sql = [];
        foreach (Register::getRegister() as $configuration) {
            $fieldName = isset($configuration['fieldName']) ? $configuration['fieldName'] : 'calendarize';
            $sql[] = 'CREATE TABLE ' . $configuration['tableName'] . ' (' . $fieldName . ' tinytext);';
        }

        return \implode(LF, $sql);
    }
}
