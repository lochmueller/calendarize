<?php

/**
 * Abstract controller.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Controller;

use HDNET\Calendarize\Domain\Repository\IndexRepository;
use HDNET\Calendarize\Event\GenericActionAssignmentEvent;
use HDNET\Calendarize\Event\GenericActionRedirectEvent;
use HDNET\Calendarize\Property\TypeConverter\AbstractBookingRequest;
use HDNET\Calendarize\Service\PluginConfigurationService;
use HDNET\Calendarize\Utility\DateTimeUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Security\Cryptography\HashService;
use TYPO3\CMS\Frontend\Controller\ErrorController;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageAccessFailureReasons;

/**
 * Abstract controller.
 */
abstract class AbstractController extends ActionController
{
    /**
     * The index repository.
     *
     * @var \HDNET\Calendarize\Domain\Repository\IndexRepository
     */
    protected $indexRepository;

    /**
     * @var PluginConfigurationService
     */
    protected $pluginConfigurationService;

    /**
     * The feed formats and content types.
     *
     * @var array
     */
    protected $feedFormats = [
        'ics' => 'text/calendar',
        'xml' => 'application/xml',
        'atom' => 'application/rss+xml',
    ];

    /**
     * Inject plugin configuration service.
     *
     * @param PluginConfigurationService $pluginConfigurationService
     */
    public function injectPluginConfigurationService(PluginConfigurationService $pluginConfigurationService)
    {
        $this->pluginConfigurationService = $pluginConfigurationService;
    }

    /**
     * Inject index repository.
     *
     * @param \HDNET\Calendarize\Domain\Repository\IndexRepository $indexRepository
     */
    public function injectIndexRepository(IndexRepository $indexRepository)
    {
        $this->indexRepository = $indexRepository;
    }

    /**
     * Inject the configuration manager.
     *
     * @param ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
        $this->settings = $this->pluginConfigurationService->respectPluginConfiguration((array)$this->settings);
    }

    /**
     * Init all actions.
     */
    public function initializeAction()
    {
        parent::initializeAction();
        AbstractBookingRequest::setConfigurations(GeneralUtility::trimExplode(',', $this->settings['configuration'] ?? ''));
    }

    /**
     * Extend the variables by the event and name and assign the variable to the view.
     */
    protected function eventExtendedAssignMultiple(array $variables, string $className, string $eventName)
    {
        // use this variable in your extension to add more custom variables
        $variables['extended'] = [];
        $variables['extended']['pluginHmac'] = $this->calculatePluginHmac();
        $variables['settings'] = $this->settings;
        $variables['contentObject'] = $this->configurationManager->getContentObject()->data;

        $event = new GenericActionAssignmentEvent($variables, $className, $eventName);
        $this->eventDispatcher->dispatch($event);

        $this->view->assignMultiple($event->getVariables());
    }

    /**
     * A redirect that have an event included.
     */
    protected function eventExtendedRedirect(string $className, string $eventName, array $variables = [])
    {
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

        $this->redirect(
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
     *
     * @return string
     */
    protected function getStringForPluginHmac(): string
    {
        $actionMethodName = ucfirst($this->request->getControllerActionName());
        $pluginName = $this->request->getPluginName();
        $controllerName = $this->request->getControllerName();

        return $controllerName . $pluginName . $actionMethodName;
    }

    /**
     * Calculate the plugin Hmac.
     *
     * @return string $hmac
     *
     * @see \TYPO3\CMS\Extbase\Security\Cryptography\HashService::generateHmac()
     */
    protected function calculatePluginHmac()
    {
        $string = $this->getStringForPluginHmac();

        $hashService = GeneralUtility::makeInstance(HashService::class);

        return $hashService->generateHmac($string);
    }

    /**
     * \TYPO3\CMS\Extbase\Security\Cryptography\HashService::validateHmac().
     *
     * @param string $hmac
     *
     * @return bool
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
    protected function checkStaticTemplateIsIncluded()
    {
        if (!isset($this->settings['dateFormat'])) {
            $this->addFlashMessage(
                'Basic configuration settings are missing. It seems, that the Static Extension TypoScript is not loaded to your TypoScript configuration. Please add the calendarize TS to your TS settings.',
                'Configuration Error',
                FlashMessage::ERROR
            );
        }
    }

    /**
     * Change the page title.
     *
     * @param string $title
     */
    protected function changePageTitle($title)
    {
        /** @var TypoScriptFrontendController $frontendController */
        $frontendController = $GLOBALS['TSFE'];
        $frontendController->page['title'] = $title;
        $frontendController->indexedDocTitle = $title;
    }

    /**
     * Add cache tags.
     *
     * @param array $tags
     */
    protected function addCacheTags(array $tags)
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
