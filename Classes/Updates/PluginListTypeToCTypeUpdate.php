<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Updates;

use TYPO3\CMS\Core\Upgrades\AbstractListTypeToCTypeUpdate;

class PluginListTypeToCTypeUpdate extends AbstractListTypeToCTypeUpdate
{
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'calendarize_listdetail' => 'calendarize_listdetail',
            'calendarize_list' => 'calendarize_list',
            'calendarize_detail' => 'calendarize_detail',
            'calendarize_search' => 'calendarize_search',
            'calendarize_result' => 'calendarize_result',
            'calendarize_latest' => 'calendarize_latest',
            'calendarize_single' => 'calendarize_single',
            'calendarize_year' => 'calendarize_year',
            'calendarize_quarter' => 'calendarize_quarter',
            'calendarize_month' => 'calendarize_month',
            'calendarize_week' => 'calendarize_week',
            'calendarize_day' => 'calendarize_day',
            'calendarize_past' => 'calendarize_past',
            'calendarize_shortcut' => 'calendarize_shortcut',
            'calendarize_calendar' => 'calendarize_calendar',
            'calendarize_booking' => 'calendarize_booking',
        ];
    }

    public function getTitle(): string
    {
        return 'Migrates calendarize plugins';
    }

    public function getDescription(): string
    {
        return 'Migrates all calendarize plugins from list_type to CType.';
    }
}
