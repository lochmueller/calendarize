<?php

declare(strict_types=1);

namespace HDNET\Calendarize\ViewHelpers\Link;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface as ExtbaseRequestInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Typolink\LinkFactory;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;
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
     * Action name used to resolve the target page via settings (e.g. "listPid").
     */
    protected ?string $actionName = null;

    /**
     * Arguments initialization.
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('target', 'string', 'Target of link');
        $this->registerArgument(
            'rel',
            'string',
            'Specifies the relationship between the current document and the linked document',
        );
    }

    /**
     * Render the link.
     */
    public function renderLink(
        ?int $pageUid = null,
        array $additionalParams = [],
        bool $absolute = false,
        $section = '',
    ): string {
        $this->lastHref = $this->buildUri($pageUid, $additionalParams, $absolute, (string)$section);

        if ('' !== $this->lastHref) {
            $this->tag->addAttribute('href', $this->lastHref);
            $this->tag->setContent($this->renderChildren());
            $result = $this->tag->render();
        } else {
            $result = $this->renderChildren();
        }

        return (string)$result;
    }

    /**
     * Build the URI for the given page and query parameters.
     *
     * Works with an Extbase request as well as with a plain PSR-7 request, which is
     * the case if the ViewHelper is used outside of an Extbase context (e.g. in a
     * FLUIDTEMPLATE or in a template rendered by another extension).
     */
    protected function buildUri(
        ?int $pageUid,
        array $queryParameters = [],
        bool $absolute = false,
        string $section = '',
    ): string {
        $request = $this->getRequest();

        if ($request instanceof ExtbaseRequestInterface) {
            /** @var UriBuilder $uriBuilder */
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

            return $uriBuilder->reset()
                ->setRequest($request)
                ->setTargetPageUid($pageUid)
                ->setSection($section)
                ->setArguments($queryParameters)
                ->setCreateAbsoluteUri($absolute)
                ->build();
        }

        if ($request instanceof ServerRequestInterface && $this->isFrontendRequest($request)) {
            return $this->buildFrontendUri($request, $pageUid, $queryParameters, $absolute, $section);
        }

        return '';
    }

    /**
     * Build the URI via typolink, without any Extbase context.
     */
    protected function buildFrontendUri(
        ServerRequestInterface $request,
        ?int $pageUid,
        array $queryParameters = [],
        bool $absolute = false,
        string $section = '',
    ): string {
        $typolinkConfiguration = [
            'parameter' => (int)$pageUid > 0 ? (int)$pageUid : 'current',
        ];
        if ([] !== $queryParameters) {
            $typolinkConfiguration['queryParameters'] = $queryParameters;
        }
        if ('' !== $section) {
            $typolinkConfiguration['section'] = $section;
        }
        if ($absolute) {
            $typolinkConfiguration['forceAbsoluteUrl'] = true;
        }

        $contentObjectRenderer = $request->getAttribute('currentContentObject');
        if (!$contentObjectRenderer instanceof ContentObjectRenderer) {
            $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $contentObjectRenderer->setRequest($request);
        }

        try {
            return GeneralUtility::makeInstance(LinkFactory::class)
                ->create('', $typolinkConfiguration, $contentObjectRenderer)
                ->getUrl();
        } catch (UnableToLinkException) {
            return '';
        }
    }

    /**
     * Get the right page Uid.
     */
    protected function getPageUid(?string $contextName = null): int
    {
        $pageUid = (int)($this->arguments['pageUid'] ?? 0);
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

        return $this->getCurrentPageUid();
    }

    /**
     * Get the UID of the current page, if there is any.
     */
    protected function getCurrentPageUid(): int
    {
        $request = $this->getRequest();
        if (!$request instanceof ServerRequestInterface) {
            return 0;
        }

        $pageInformation = $request->getAttribute('frontend.page.information');
        if (null !== $pageInformation) {
            return (int)$pageInformation->getId();
        }

        $pageArguments = $request->getAttribute('routing');
        if ($pageArguments instanceof PageArguments) {
            return $pageArguments->getPageId();
        }

        return 0;
    }

    protected function isFrontendRequest(ServerRequestInterface $request): bool
    {
        return is_int($request->getAttribute('applicationType'))
            && ApplicationType::fromRequest($request)->isFrontend();
    }

    protected function getRequest(): ?ServerRequestInterface
    {
        $request = null;
        if ($this->renderingContext->hasAttribute(ServerRequestInterface::class)) {
            $request = $this->renderingContext->getAttribute(ServerRequestInterface::class);
        }

        return $request;
    }
}
