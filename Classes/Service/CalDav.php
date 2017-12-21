<?php

/**
 * Wrapper for the Cal Dav structure.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service;

use HDNET\Calendarize\Service\CalDav\AuthBackendTypo3;
use HDNET\Calendarize\Service\CalDav\BackendTypo3;
use HDNET\Calendarize\Service\CalDav\PrincipalBackendTypo3;
use HDNET\Calendarize\Utility\HelperUtility;
use Sabre\CalDAV\CalendarRoot;
use Sabre\CalDAV\Plugin;
use Sabre\CalDAV\Principal\Collection;
use Sabre\DAV\Auth\Plugin as AuthPlugin;
use Sabre\DAV\Browser\Plugin as BrowserPlugin;
use Sabre\DAV\Server;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Wrapper for the Cal Dav structure.
 */
class CalDav extends AbstractService
{
    /**
     * Build up the Cal DAV wrapper.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->checkEnvironment();
    }

    /**
     * Run the server.
     *
     * @param mixed $calendarId
     */
    public function runServer($calendarId)
    {
        $configuration = $this->getConfiguration($calendarId);
        if (!\is_array($configuration)) {
            throw new \Exception('Invalid configuration key', 123176283);
        }
        $principalBackend = new PrincipalBackendTypo3();

        $tree = [
            new Collection($principalBackend),
            new CalendarRoot($principalBackend, new BackendTypo3()),
        ];

        $server = new Server($tree);
        $server->setBaseUri('/CalDav/');

        /* Server Plugins */
        $authPlugin = new AuthPlugin(new AuthBackendTypo3());
        $server->addPlugin($authPlugin);

        // $aclPlugin = new \Sabre\DAVACL\Plugin();
        // $server->addPlugin($aclPlugin);

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
     * @param $calendarId
     *
     * @return array|false|null
     */
    protected function getConfiguration($calendarId)
    {
        $db = HelperUtility::getDatabaseConnection();
        $table = 'tx_calendarize_domain_model_caldav';

        return $db->exec_SELECTgetSingleRow('*', $table, 'title=' . $db->fullQuoteStr($calendarId, $table));
    }

    /**
     * Check the environment.
     *
     * @throws \Exception
     */
    protected function checkEnvironment()
    {
        $class = Server::class;
        if (!\class_exists($class)) {
            throw new \Exception('No ' . $class . ' class found. 
            So there is no valid CalDav framework. 
            Please run "composer require sabre/dav ~3.2.0" in your installation', 278346238);
        }
    }
}
