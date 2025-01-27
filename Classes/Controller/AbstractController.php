<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Controller;

use HDNET\Calendarize\Domain\Repository\IndexRepository;
use HDNET\Calendarize\Event\DirectResponseEvent;
use HDNET\Calendarize\Event\GenericActionAssignmentEvent;
use HDNET\Calendarize\Event\GenericActionRedirectEvent;
use HDNET\Calendarize\Event\InitializeActionEvent;
use HDNET\Calendarize\Event\InitializeViewEvent;
use HDNET\Calendarize\Property\TypeConverter\AbstractBookingRequest;
use HDNET\Calendarize\Service\PluginConfigurationService;
use HDNET\Calendarize\Utility\DateTimeUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use TYPO3\CMS\Frontend\Page\PageAccessFailureReasons;

abstract class AbstractController extends ActionController
{
    protected IndexRepository $indexRepository;

    protected PluginConfigurationService $pluginConfigurationService;

    /**
     * The feed formats and content types.
     */
    protected array $feedFormats = [
        'ics' => 'text/calendar',
        'xml' => 'application/xml',
        'atom' => 'application/rss+xml',
    ];

    /**
     * Inject plugin configuration service.
     */
    public function injectPluginConfigurationService(PluginConfigurationService $pluginConfigurationService): void
    {
        $this->pluginConfigurationService = $pluginConfigurationService;
    }

    /**
     * Inject index repository.
     */
    public function injectIndexRepository(IndexRepository $indexRepository): void
    {
        $this->indexRepository = $indexRepository;
    }

    /**
     * Inject the configuration manager.
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
        );
        $this->settings = $this->pluginConfigurationService->respectPluginConfiguration($this->settings);
        $this->arguments = GeneralUtility::makeInstance(Arguments::class);
    }

    /**
     * Init all actions.
     */
    public function initializeAction(): void
    {
        parent::initializeAction();

        $event = new InitializeActionEvent(
            $this->request,
            $this->arguments,
            $this->settings,
            static::class,
            $this->actionMethodName,
        );
        $this->eventDispatcher->dispatch($event);
        $this->request = $event->getRequest();
        $this->arguments = $event->getArguments();
        $this->settings = $event->getSettings();

        AbstractBookingRequest::setConfigurations(
            GeneralUtility::trimExplode(',', $this->settings['configuration'] ?? ''),
        );
    }

    protected function initializeView(): void
    {
        $event = new InitializeViewEvent(
            $this->request,
            $this->arguments,
            $this->settings,
            static::class,
            $this->actionMethodName,
        );
        $this->eventDispatcher->dispatch($event);
        $this->request = $event->getRequest();
        $this->arguments = $event->getArguments();
        $this->settings = $event->getSettings();
    }

    /**
     * Calls the specified action method and passes the arguments.
     *
     * If the action returns a string, it is appended to the content in the
     * response object. If the action doesn't return anything and a valid
     * view exists, the view is rendered automatically.
     *
     * @throws PropagateResponseException
     *
     * @api
     */
    protected function callActionMethod(RequestInterface $request): ResponseInterface
    {
        $response = parent::callActionMethod($request);
        if (isset($this->feedFormats[$request->getFormat()])) {
            if ($request->hasArgument('hmac')) {
                $hmac = $request->getArgument('hmac');
                if ($this->validatePluginHmac($hmac)) {
                    $this->setHeadersAndExit(
                        $response,
                        $this->feedFormats[$request->getFormat()],
                        $request->getFormat(),
                    );
                }

                // When the hmac does not match, the request belongs to a different action.
                return $response;
            }
            // No hmac is set (default configuration in Template/Details.html), so we handle the first action.
            $this->setHeadersAndExit($response, $this->feedFormats[$request->getFormat()], $request->getFormat());
        }

        return $response;
    }

    /**
     * Send the content type header and the right file extension in front of the content.
     *
     * @throws PropagateResponseException
     */
    protected function setHeadersAndExit(
        ResponseInterface $response,
        string $contentType,
        string $fileExtension,
    ): void {
        if (200 !== $response->getStatusCode()) {
            // Prevents html error pages to be returned with wrong Content-Type.
            return;
        }
        $testMode = (bool)$this->settings['feed']['debugMode'];
        if ($testMode) {
            $response = $response->withHeader('Content-Type', 'text/plain; charset=utf-8');
        } else {
            $response = $response->withHeader('Content-Type', $contentType . '; charset=utf-8')
                ->withHeader('Content-Disposition', 'attachment; filename=calendar.' . $fileExtension);
        }

        // Use CRLF, see https://tools.ietf.org/html/rfc5545#section-3.1
        if ('ics' === $this->request->getFormat()) {
            $response->getBody()->rewind();
            $response = $response->withBody(
                $this->streamFactory->createStream(
                    str_replace("\n", "\r\n", $response->getBody()->getContents()),
                ),
            );
        }
        // Any other actions (rendered before this) returning a status code >= 300 code would cause the status header
        // to be set. Other PSR-7 responses by extbase actions (< 300) don't set the status code.
        // (see https://review.typo3.org/c/Packages/TYPO3.CMS/+/71014)
        // This would cause the response to be ignored by the browser.
        header('HTTP/' . $response->getProtocolVersion() . ' ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase());
        // Prevents the HTML skeleton and any other (following) actions to be rendered.

        $event = new DirectResponseEvent($response, $this);
        $event = $this->eventDispatcher->dispatch($event);

        throw new PropagateResponseException($event->getResponse());
    }

    /**
     * Extend the variables by the event and name and assign the variable to the view.
     */
    protected function eventExtendedAssignMultiple(array $variables, string $className, string $functionName): void
    {
        // use this variable in your extension to add more custom variables
        $variables['extended'] = [];
        $variables['extended']['pluginHmac'] = $this->calculatePluginHmac();
        $variables['settings'] = $this->settings;
        $variables['contentObject'] = $this->request->getAttribute('currentContentObject')->data;

        $event = new GenericActionAssignmentEvent($variables, $className, $functionName);
        $this->eventDispatcher->dispatch($event);

        $this->view->assignMultiple($event->getVariables());
    }

    /**
     * A redirect that have an event included.
     */
    protected function eventExtendedRedirect(
        string $className,
        string $eventName,
        array $variables = [],
    ): ResponseInterface {
        // set default variables for the redirect
        if (empty($variables)) {
            $variables['extended'] = [
                'actionName' => 'list',
                'controllerName' => null,
                'extensionName' => null,
                'arguments' => [],
                'pageUid' => (int)$this->settings['listPid'],
                'delay' => 0,
                'statusCode' => 301,
            ];
            $variables['extended']['pluginHmac'] = $this->calculatePluginHmac();
            $variables['settings'] = $this->settings;
        }

        $event = new GenericActionRedirectEvent($variables, $className, $eventName);
        $this->eventDispatcher->dispatch($event);
        $variables = $event->getVariables();

        return $this->redirect(
            $variables['extended']['actionName'],
            $variables['extended']['controllerName'],
            $variables['extended']['extensionName'],
            $variables['extended']['arguments'],
            $variables['extended']['pageUid'],
            $variables['extended']['delay'],
            $variables['extended']['statusCode'],
        );
    }

    /**
     * Return the controllerName, pluginName and actionName.
     */
    protected function getStringForPluginHmac(): string
    {
        $actionMethodName = ucfirst($this->request->getControllerActionName());
        $pluginName = $this->request->getPluginName();
        $controllerName = $this->request->getControllerName();
        $pluginUid = $this->request->getAttribute('currentContentObject')->data['uid'];

        return $controllerName . $pluginName . $actionMethodName . $pluginUid;
    }

    /**
     * Calculate the plugin Hmac.
     *
     * @see HashService::generateHmac
     */
    protected function calculatePluginHmac(): string
    {
        $string = $this->getStringForPluginHmac();

        $hashService = GeneralUtility::makeInstance(HashService::class);

        return $hashService->generateHmac($string);
    }

    /**
     * @see HashService::validateHmac
     */
    protected function validatePluginHmac(string $hmac): bool
    {
        $string = $this->getStringForPluginHmac();

        /** @var HashService $hashService */
        $hashService = GeneralUtility::makeInstance(HashService::class);

        return $hashService->validateHmac($string, $hmac);
    }

    /**
     * Check if the static template is included.
     */
    protected function checkStaticTemplateIsIncluded(): void
    {
        if (!\array_key_exists('dateLimitBrowserPrev', $this->settings)) {
            $this->addFlashMessage(
                'Basic configuration settings are missing. It seems, that the Static Extension TypoScript
                 is not loaded to your TypoScript configuration. Please add the calendarize TS to your TS settings.',
                'Configuration Error',
                ContextualFeedbackSeverity::ERROR,
            );
        }
    }

    /**
     * Add cache tags.
     */
    protected function addCacheTags(array $tags): void
    {
        $this->request->getAttribute('frontend.controller')?->addCacheTags($tags);
    }

    protected function isDateOutOfTypoScriptConfiguration(\DateTime $dateTime): bool
    {
        $prev = DateTimeUtility::normalizeDateTimeSingle($this->settings['dateLimitBrowserPrev'] ?? null);
        $next = DateTimeUtility::normalizeDateTimeSingle($this->settings['dateLimitBrowserNext'] ?? null);

        return $prev > $dateTime || $next < $dateTime;
    }

    protected function return404Page(): ResponseInterface
    {
        return GeneralUtility::makeInstance(ErrorController::class)->pageNotFoundAction(
            $this->request,
            'The requested page does not exist',
            ['code' => PageAccessFailureReasons::PAGE_NOT_FOUND],
        );
    }
}
