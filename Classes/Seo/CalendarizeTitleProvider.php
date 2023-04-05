<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Seo;

use TYPO3\CMS\Core\PageTitle\AbstractPageTitleProvider;

/**
 * Generate page title.
 *
 * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/PageTitleApi/Index.html
 */
class CalendarizeTitleProvider extends AbstractPageTitleProvider
{
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
