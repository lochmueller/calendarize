<?php

/**
 * LanguageInformationViewHelper.
 */
declare(strict_types=1);

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
     * Specifies whether the escaping interceptors should be disabled or enabled for the render-result of this ViewHelper.
     *
     * @see isOutputEscapingEnabled()
     *
     * @var bool
     *
     * @api
     */
    protected $escapeOutput = false;

    /**
     * Init arguments.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('languageUid', 'int', 'Language UID', true);
        $this->registerArgument('pid', 'int', 'Page UID', false, 0);
    }

    /**
     * Render.
     *
     * @return string
     */
    public function render()
    {
        $langUid = (int)$this->arguments['languageUid'];
        $pid = (int)$this->arguments['pid'];
        if (\array_key_exists($langUid . '-' . $pid, self::$flags)) {
            return self::$flags[$langUid . '-' . $pid];
        }

        $translationTools = GeneralUtility::makeInstance(TranslationConfigurationProvider::class);
        $sysLanguages = $translationTools->getSystemLanguages($pid);

        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

        $out = '';
        $title = \htmlspecialchars($sysLanguages[$langUid]['title']);
        if ($sysLanguages[$langUid]['flagIcon']) {
            $out .= '<span title="' . $title . '">' . $iconFactory->getIcon($sysLanguages[$langUid]['flagIcon'], Icon::SIZE_SMALL)->render() . '</span>';
            $out .= '&nbsp;';
        }
        $out .= $title;

        self::$flags[$langUid . '-' . $pid] = (string)$out;

        return $out;
    }
}
