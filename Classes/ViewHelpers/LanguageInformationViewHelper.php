<?php

declare(strict_types=1);
/**
 * LanguageInformationViewHelper.
 */

namespace HDNET\Calendarize\ViewHelpers;

use TYPO3\CMS\Backend\Configuration\TranslationConfigurationProvider;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * LanguageInformationViewHelper.
 */
class LanguageInformationViewHelper extends AbstractViewHelper
{
    /**
     * Flags.
     *
     * @var array
     */
    protected static $flags = [];

    /**
     * Init arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('languageUid', 'int', 'Language UID', true);
    }

    /**
     * Render.
     *
     * @return string
     */
    public function render()
    {
        $langUid = (int) $this->arguments['languageUid'];
        if (\array_key_exists($langUid, self::$flags)) {
            return self::$flags[$langUid];
        }

        $translationTools = GeneralUtility::makeInstance(TranslationConfigurationProvider::class);
        $sysLanguages = $translationTools->getSystemLanguages();

        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

        $out = '';
        $title = \htmlspecialchars($sysLanguages[$langUid]['title']);
        if ($sysLanguages[$langUid]['flagIcon']) {
            $out .= '<span title="' . $title . '">' . $iconFactory->getIcon($sysLanguages[$langUid]['flagIcon'], Icon::SIZE_SMALL)->render() . '</span>';
            $out .= '&nbsp;';
        }
        $out .= $title;

        self::$flags[$this->arguments['languageUid']] = (string) $out;

        return $out;
    }
}
