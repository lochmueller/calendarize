<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Link;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

class AbstractActionViewHelper extends AbstractLinkViewHelper
{
    protected ?string $extensionName = 'Calendarize';

    protected ?string $pluginName = 'Calendar';
    protected ?string $controllerName = 'Calendar';
    protected ?string $actionName;

    public function initializeArguments(): void
    {
        parent::initializeArguments();

        $this->registerArgument('section', 'string', 'The anchor to be added to the URI');
        $this->registerArgument('pageUid', 'int', 'Target page');
        $this->registerArgument('absolute', 'bool', 'If set, the URI of the rendered link is absolute');
    }

    /**
     * Render a link with action and controller.
     */
    public function renderExtbaseLink(array $controllerArguments = [], ?int $pageUid = null): string
    {
        $absolute = $this->arguments['absolute'] ?? false;
        $pageUid = $pageUid ?? $this->getPageUid();

        $section = $this->arguments['section'] ?? '';

        /** @var UriBuilder $uriBuilder */
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $this->lastHref = $uriBuilder->reset()
            ->setRequest($this->getRequest())
            ->setTargetPageUid($pageUid)
            ->setSection($section)
            ->setCreateAbsoluteUri($absolute)
            ->uriFor(
                $this->actionName,
                $controllerArguments,
                $this->controllerName,
                $this->extensionName,
                $this->pluginName,
            );

        if ('' !== $this->lastHref) {
            $this->tag->addAttribute('href', $this->lastHref);
            $this->tag->setContent($this->renderChildren());
            $this->tag->forceClosingTag(true);
            $result = $this->tag->render();
        } else {
            $result = $this->renderChildren();
        }

        return $result;
    }
}
