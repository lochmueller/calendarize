<?php

/**
 * LanguageViewHelper.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers;

use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * LanguageViewHelper.
 */
class LanguageViewHelper extends AbstractViewHelper
{
    /**
     * Get the current language ISO code.
     *
     * @return string
     */
    public function render()
    {
        /** @var ServerRequest $request */
        $request = $GLOBALS['TYPO3_REQUEST'];

        /** @var SiteLanguage $language */
        $language = $request->getAttribute('language');

        return $language->getTwoLetterIsoCode();
    }
}
