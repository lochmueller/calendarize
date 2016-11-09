<?php
/**
 * Abstract controller
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Controller;

use HDNET\Calendarize\Property\TypeConverter\AbstractBookingRequest;
use HDNET\Calendarize\Service\PluginConfigurationService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Abstract controller
 */
abstract class AbstractController extends ActionController
{
    /**
     * The index repository
     *
     * @var \HDNET\Calendarize\Domain\Repository\IndexRepository
     * @inject
     */
    protected $indexRepository;

    /**
     * The feed formats and content types
     *
     * @var array
     */
    protected $feedFormats = [
        'ics' => 'text/calendar',
        'xml' => 'application/xml',
        'atom' => 'application/rss+xml',
    ];

    /**
     * @param ConfigurationManagerInterface $configurationManager
     * @return void
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;

        $this->settings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);

        $objectManager = new ObjectManager();
        $pluginConfigurationService = $objectManager->get(PluginConfigurationService::class);
        $this->settings = $pluginConfigurationService->respectPluginConfiguration($this->settings);
    }


    /**
     * Init all actions
     */
    public function initializeAction()
    {
        parent::initializeAction();
        AbstractBookingRequest::setConfigurations(GeneralUtility::trimExplode(',', $this->settings['configuration']));
    }

    /**
     * Calls the specified action method and passes the arguments.
     *
     * If the action returns a string, it is appended to the content in the
     * response object. If the action doesn't return anything and a valid
     * view exists, the view is rendered automatically.
     *
     * @return void
     * @api
     */
    protected function callActionMethod()
    {
        parent::callActionMethod();
        if (isset($this->feedFormats[$this->request->getFormat()])) {
            $this->sendHeaderAndFilename($this->feedFormats[$this->request->getFormat()], $this->request->getFormat());
        }
    }

    /**
     * Send the content type header and the right file extension in front of the content
     *
     * @param $contentType
     * @param $fileExtension
     */
    protected function sendHeaderAndFilename($contentType, $fileExtension)
    {
        $testMode = (bool)$this->settings['feed']['debugMode'];
        if ($testMode) {
            header('Content-Type: text/plain; charset=utf-8');
        } else {
            header('Content-Type: ' . $contentType . '; charset=utf-8');
            header('Content-Disposition: inline; filename=calendar.' . $fileExtension);
        }
        echo $this->response->getContent();
        HttpUtility::setResponseCodeAndExit(HttpUtility::HTTP_STATUS_200);
    }

    /**
     * Extend the view by the slot class and name and assign the variable to the view
     *
     * @param array $variables
     * @param string $signalClassName
     * @param string $signalName
     */
    protected function slotExtendedAssignMultiple(array $variables, $signalClassName, $signalName)
    {
        // use this variable in your extension to add more custom variables
        $variables['extended'] = [];
        $variables['settings'] = $this->settings;

        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->objectManager->get(Dispatcher::class);
        $variables = $dispatcher->dispatch($signalClassName, $signalName, $variables);

        $this->view->assignMultiple($variables);
    }

    /**
     * Check if the static template is included
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
}
