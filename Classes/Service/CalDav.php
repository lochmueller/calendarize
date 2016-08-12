<?php

/**
 * Wrapper for the Cal Dav structure
 */

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Service\CalDav\AuthBackendTypo3;
use HDNET\Calendarize\Service\CalDav\BackendTypo3;
use HDNET\Calendarize\Service\CalDav\PrincipalBackendTypo3;
use Sabre\CalDAV\CalendarRoot;
use Sabre\CalDAV\Plugin;
use Sabre\CalDAV\Principal\Collection;
use Sabre\DAV\Auth\Plugin as AuthPlugin;
use Sabre\DAV\Browser\Plugin as BrowserPlugin;
use Sabre\DAV\Server;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Utility\EidUtility;

/**
 * Wrapper for the Cal Dav structure
 */
class CalDav extends AbstractService
{

    /**
     * Build up the Cal DAV wrapper
     *
     * @throws \Exception
     */
    public function __construct()
    {

        $calendarConfigurations = [
            [
                'calendar_id' => 1,
                'user_pid'    => 118,
            ],
        ];

        // Base Configuration
        // load this in your composer configuration: sabre/dav ~3.1.0

        // .htaccess configuration
        // RewriteRule ^CalDav/ /index.php?eID=CalDav [L]

        // require_once(ExtensionManagementUtility::extPath('calendarize', 'Resources/External/vendor/autoload.php'));

        // check Cal Dav infrastructure
        $this->checkEnvironment();
    }

    /**
     * Build up the TSFE
     */
    public function buildFrontend()
    {
        global $TSFE;
        EidUtility::initLanguage();

        /** @var TypoScriptFrontendController $TSFE */
        $TSFE = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            $GLOBALS['TYPO3_CONF_VARS'],
            0,
            0
        );
        EidUtility::initLanguage();

        // Get FE User Information
        $TSFE->initFEuser();
        // Important: no Cache for Ajax stuff
        $TSFE->set_no_cache();
        $TSFE->initTemplate();
        // $TSFE->getConfigArray();
        Bootstrap::getInstance()
            ->loadCachedTca();
        $TSFE->cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $TSFE->settingLanguage();
        $TSFE->settingLocale();
    }

    /**
     * Run the server
     */
    public function runServer()
    {
        $pdo = $this->getPdoConnection();
        $principalBackend = new PrincipalBackendTypo3($pdo);

        $tree = [
            new Collection($principalBackend),
            new CalendarRoot($principalBackend, new BackendTypo3($pdo)),
        ];

        $server = new Server($tree);
        $server->setBaseUri('/CalDav/');

        /* Server Plugins */
        $authPlugin = new AuthPlugin(new AuthBackendTypo3($pdo));
        $server->addPlugin($authPlugin);

        #$aclPlugin = new \Sabre\DAVACL\Plugin();
        #$server->addPlugin($aclPlugin);

        $caldavPlugin = new Plugin();
        $server->addPlugin($caldavPlugin);

        if (GeneralUtility::getApplicationContext()
            ->isDevelopment()
        ) {
            $server->addPlugin(new BrowserPlugin());
        }

        $server->exec();
    }

    /**
     * Get the PDO connection
     *
     * @return \PDO
     */
    protected function getPdoConnection()
    {
        $dbSettings = $GLOBALS['TYPO3_CONF_VARS']['DB'];
        $pdo = new \PDO(
            'mysql:host=' . $dbSettings['host'] . ';dbname=' . $dbSettings['database'],
            $dbSettings['username'],
            $dbSettings['password']
        );
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $pdo;

    }

    /**
     * Check the environment
     *
     * @throws \Exception
     */
    protected function checkEnvironment()
    {
        $class = Server::class;
        if (!class_exists($class)) {
            throw new \Exception('No ' . $class . ' class found. So there is no valid CalDav configuration', 278346238);
        }
    }
}
