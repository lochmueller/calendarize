<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Link;

use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;

class AbstractActionViewHelper extends AbstractLinkViewHelper
{
    protected $extensionName = 'Calendarize';
    protected $pluginName = 'Calendar';
    protected $controllerName = 'Calendar';
    protected $actionName;

    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument('section', 'string', 'The anchor to be added to the URI', false);
        $this->registerArgument('pageUid', 'int', 'Target page', false);
        $this->registerArgument('absolute', 'bool', 'If set, the URI of the rendered link is absolute', false);
    }

    /**
     * Render a link with action and controller.
     *
     * @param array $controllerArguments
     * @param null  $pageUid
     *
     * @return mixed|string
     */
    public function renderExtbaseLink(array $controllerArguments = [], $pageUid = null)
    {
        $absolute = $this->arguments['absolute'] ?? false;
        $pageUid = $pageUid ?? $this->getPageUid($this->arguments['pageUid'] ?? '');

        $section = $this->arguments['section'] ?? '';

        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $this->renderingContext->getControllerContext()->getUriBuilder();
        // $uriBuilder = $this->renderingContext->getUriBuilder(); // Typo3 11 and later
        $this->lastHref = $uriBuilder->reset()
            ->setTargetPageUid($pageUid)
            ->setSection($section)
            ->setCreateAbsoluteUri($absolute)
            ->uriFor(
                $this->actionName,
                $controllerArguments,
                $this->controllerName,
                $this->extensionName,
                $this->pluginName
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
