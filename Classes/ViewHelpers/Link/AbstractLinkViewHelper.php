<?php

/**
 * Link to anything ;).
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Link;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * Link to anything ;).
 */
abstract class AbstractLinkViewHelper extends AbstractTagBasedViewHelper
{
    /**
     * Tag type.
     *
     * @var string
     */
    protected $tagName = 'a';

    /**
     * Store the last href to avoid escaping for the URI view Helper.
     */
    protected string $lastHref = '';

    /**
     * Arguments initialization.
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerUniversalTagAttributes();
        $this->registerTagAttribute('target', 'string', 'Target of link', false);
        $this->registerTagAttribute(
            'rel',
            'string',
            'Specifies the relationship between the current document and the linked document',
            false
        );
    }

    /**
     * Render the link.
     */
    public function renderLink(
        ?int $pageUid = null,
        array $additionalParams = [],
        bool $absolute = false,
        $section = ''
    ): string {
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        // $uriBuilder = $this->renderingContext->getUriBuilder(); // Typo3 11 and later
        $this->lastHref = $uriBuilder->reset()
            ->setTargetPageUid($pageUid)
            ->setSection($section)
            ->setArguments($additionalParams)
            ->setCreateAbsoluteUri($absolute)
            ->build();
        if ('' !== $this->lastHref) {
            $this->tag->addAttribute('href', $this->lastHref);
            $this->tag->setContent($this->renderChildren());
            $result = $this->tag->render();
        } else {
            $result = $this->renderChildren();
        }

        return $result;
    }

    /**
     * Get the right page Uid.
     */
    protected function getPageUid(int $pageUid, ?string $contextName = null): int
    {
        if (MathUtility::canBeInterpretedAsInteger($pageUid) && $pageUid > 0) {
            return $pageUid;
        }
        if (null === $contextName && $this->actionName) {
            $contextName = $this->actionName . 'Pid';
        }

        // by settings
        if ($contextName && $this->templateVariableContainer->exists('settings')) {
            $settings = $this->templateVariableContainer->get('settings');
            if (isset($settings[$contextName]) && MathUtility::canBeInterpretedAsInteger($settings[$contextName])) {
                return (int)$settings[$contextName];
            }
        }

        return (int)$GLOBALS['TSFE']->id;
    }
}
