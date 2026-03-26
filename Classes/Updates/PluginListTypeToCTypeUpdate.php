<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Updates;

use TYPO3\CMS\Core\Upgrades\AbstractListTypeToCTypeUpdate;

class PluginListTypeToCTypeUpdate extends AbstractListTypeToCTypeUpdate
{
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'calenarize_listdetail' => 'calenarize_listdetail',
            'calenarize_list' => 'calenarize_list',
            'calenarize_detail' => 'calenarize_detail',
            'calenarize_search' => 'calenarize_search',
            'calenarize_result' => 'calenarize_result',
            'calenarize_latest' => 'calenarize_latest',
            'calenarize_single' => 'calenarize_single',
            'calenarize_year' => 'calenarize_year',
            'calenarize_quarter' => 'calenarize_quarter',
            'calenarize_month' => 'calenarize_month',
            'calenarize_week' => 'calenarize_week',
            'calenarize_day' => 'calenarize_day',
            'calenarize_past' => 'calenarize_past',
            'calenarize_shortcut' => 'calenarize_shortcut',
            'calenarize_calendar' => 'calenarize_calendar',
            'calenarize_booking' => 'calenarize_booking',
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
