<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Link;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface as ExtbaseRequestInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Service\ExtensionService;

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
        $absolute = (bool)($this->arguments['absolute'] ?? false);
        $pageUid = $pageUid ?? $this->getPageUid();

        $section = (string)($this->arguments['section'] ?? '');

        $this->lastHref = $this->buildActionUri($controllerArguments, $pageUid, $absolute, $section);

        if ('' !== $this->lastHref) {
            $this->tag->addAttribute('href', $this->lastHref);
            $this->tag->setContent($this->renderChildren());
            $this->tag->forceClosingTag(true);
            $result = $this->tag->render();
        } else {
            $result = $this->renderChildren();
        }

        return (string)$result;
    }

    /**
     * Build the URI to the Extbase action.
     *
     * If the ViewHelper is used outside of an Extbase context, the rendering context only
     * provides a plain PSR-7 request, which the Extbase UriBuilder cannot handle. In that
     * case the controller arguments are prefixed with the plugin namespace manually and
     * the link is built via typolink.
     */
    protected function buildActionUri(
        array $controllerArguments,
        ?int $pageUid,
        bool $absolute,
        string $section,
    ): string {
        $request = $this->getRequest();

        if ($request instanceof ExtbaseRequestInterface) {
            /** @var UriBuilder $uriBuilder */
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

            return $uriBuilder->reset()
                ->setRequest($request)
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
        }

        if (!$request instanceof ServerRequestInterface || !$this->isFrontendRequest($request)) {
            return '';
        }

        if (null !== $this->actionName) {
            $controllerArguments['action'] = $this->actionName;
        }
        if (null !== $this->controllerName) {
            $controllerArguments['controller'] = $this->controllerName;
        }

        return $this->buildFrontendUri(
            $request,
            $pageUid,
            [$this->getPluginNamespace() => $controllerArguments],
            $absolute,
            $section,
        );
    }

    /**
     * Resolve the plugin namespace (e.g. "tx_calendarize_calendar") of the target plugin.
     */
    protected function getPluginNamespace(): string
    {
        try {
            return GeneralUtility::makeInstance(ExtensionService::class)
                ->getPluginNamespace($this->extensionName, $this->pluginName);
        } catch (\Throwable) {
            // The ExtensionService is not available in every context, use the default namespace
            return 'tx_' . strtolower($this->extensionName . '_' . $this->pluginName);
        }
    }
}
