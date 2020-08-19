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
            $sql[] = 'CREATE TABLE ' . $configuration['tableName'] . ' (calendarize tinytext);';
        }

        return \implode(LF, $sql);
    }
}
