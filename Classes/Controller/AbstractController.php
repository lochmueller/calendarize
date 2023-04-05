<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Controller;

use HDNET\Calendarize\Domain\Repository\IndexRepository;
use HDNET\Calendarize\Event\GenericActionAssignmentEvent;
use HDNET\Calendarize\Event\GenericActionRedirectEvent;
use HDNET\Calendarize\Property\TypeConverter\AbstractBookingRequest;
use HDNET\Calendarize\Service\PluginConfigurationService;
use HDNET\Calendarize\Utility\DateTimeUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
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
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
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
        AbstractBookingRequest::setConfigurations(
            GeneralUtility::trimExplode(',', $this->settings['configuration'] ?? '')
        );
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
        $variables['contentObject'] = $this->configurationManager->getContentObject()->data;

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
        array $variables = []
    ): ResponseInterface {
        // set default variables for the redirect
        if (empty($variables)) {
            $variables['extended'] = [
                'actionName' => 'list',
                'controllerName' => null,
                'extensionName' => null,
                'arguments' => [],
                'pageUid' => $this->settings['listPid'],
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
            $variables['extended']['statusCode']
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
        $pluginUid = $this->configurationManager->getContentObject()->data['uid'];

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
        if (!isset($this->settings['dateLimitBrowserPrev'])) {
            $this->addFlashMessage(
                'Basic configuration settings are missing. It seems, that the Static Extension TypoScript
                 is not loaded to your TypoScript configuration. Please add the calendarize TS to your TS settings.',
                'Configuration Error',
                ContextualFeedbackSeverity::ERROR
            );
        }
    }

    /**
     * Add cache tags.
     */
    protected function addCacheTags(array $tags): void
    {
        if ($GLOBALS['TSFE'] instanceof TypoScriptFrontendController) {
            $GLOBALS['TSFE']->addCacheTags($tags);
        }
    }

    protected function isDateOutOfTypoScriptConfiguration(\DateTime $dateTime): bool
    {
        $prev = DateTimeUtility::normalizeDateTimeSingle($this->settings['dateLimitBrowserPrev']);
        $next = DateTimeUtility::normalizeDateTimeSingle($this->settings['dateLimitBrowserNext']);

        return $prev > $dateTime || $next < $dateTime;
    }

    protected function return404Page(): ResponseInterface
    {
        return GeneralUtility::makeInstance(ErrorController::class)->pageNotFoundAction(
            $GLOBALS['TYPO3_REQUEST'],
            'The requested page does not exist',
            ['code' => PageAccessFailureReasons::PAGE_NOT_FOUND]
        );
    }
}
