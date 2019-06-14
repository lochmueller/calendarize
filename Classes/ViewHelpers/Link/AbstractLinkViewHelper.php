<?php

/**
 * Link to anything ;).
 */
declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Link;

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

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
     *
     * @var string
     */
    protected $lastHref = '';

    /**
     * Logger.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Build up the object.
     */
    public function __construct()
    {
        parent::__construct();
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

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
     * render the link.
     *
     * @param int|null $pageUid          target page. See TypoLink destination
     * @param array    $additionalParams query parameters to be attached to the resulting URI
     * @param bool     $absolute
     *
     * @return string Rendered page URI
     */
    public function renderLink($pageUid = null, array $additionalParams = [], $absolute = false)
    {
        $objectManager = new ObjectManager();
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $objectManager->get(UriBuilder::class);
        $this->lastHref = (string) $uriBuilder->reset()
            ->setTargetPageUid($pageUid)
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
     *
     * @param int         $pageUid
     * @param string|null $contextName
     *
     * @return int
     */
    protected function getPageUid($pageUid, $contextName = null)
    {
        if (MathUtility::canBeInterpretedAsInteger($pageUid) && $pageUid > 0) {
            return (int) $pageUid;
        }

        // by settings
        if ($contextName && $this->templateVariableContainer->exists('settings')) {
            $settings = $this->templateVariableContainer->get('settings');
            if (isset($settings[$contextName]) && MathUtility::canBeInterpretedAsInteger($settings[$contextName])) {
                return (int) $settings[$contextName];
            }
        }

        return (int) $GLOBALS['TSFE']->id;
    }
}
