<?php
/**
 * TYPO3 Auth backend
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service\CalDav;

use HDNET\Calendarize\Domain\Repository\CalDavRepository;
use HDNET\Calendarize\Service\CalDav;
use HDNET\Calendarize\Utility\HelperUtility;
use Sabre\DAV\Auth\Backend\AbstractBasic;
use Sabre\DAV\Exception;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * TYPO3 Auth backend
 */
class AuthBackendTypo3 extends AbstractBasic
{

    /**
     * PDO table name we'll be using
     *
     * @var string
     */
    public $tableName = 'fe_users';

    /**
     * Validates a username and password
     *
     * If the username and password were correct, this method must return
     * an array with at least a 'uri' key.
     *
     * If the credentials are incorrect, this method must return false.
     *
     * @param string $username
     * @param string $password
     *
     * @return array|bool
     */
    protected function validateUserPass($username, $password)
    {
        $configuration = $this->findMatchingCalDavConfiguration($username);
        if ($configuration === false) {
            return false;
        }

        $_GET['logintype'] = 'login';
        $_GET['user'] = $username;
        $_GET['pass'] = $password;
        $_GET['challenge'] = '';
        $_GET['pid'] = $configuration['user_storage'];
        $GLOBALS['TYPO3_CONF_VARS']['FE']['loginSecurityLevel'] = 'normal';

        /** @var CalDav $calDav */
        $calDav = GeneralUtility::makeInstance(\HDNET\Calendarize\Service\CalDav::class);
        $calDav->buildFrontend();

        $feUserObj = $GLOBALS['TSFE']->fe_user;

        if (is_array($feUserObj->user) && $feUserObj->user['uid'] && $feUserObj->user['is_online']) {
            $user = [
                'uri'         => 'principals/' . $username,
                'digestHash'  => md5($username . ':' . 'SabreDAV' . ':' . $username),
                'calendar_id' => $configuration['uid']
            ];

            if ($feUserObj->user['email']) {
                $user['{http://sabredav.org/ns}email-address'] = $feUserObj->user['email'];
            }

            return $user;
        } else {
            return false;
        }
    }

    /**
     * Find matching against CalDav configuration
     *
     * @param string $username
     *
     * @return bool|\HDNET\Calendarize\Domain\Model\CalDav
     */
    protected function findMatchingCalDavConfiguration($username)
    {
        $userRecord = $this->getUserRow($username);
        if (!isset($userRecord['pid'])) {
            return false;
        }
        /** @var CalDavRepository $repository */
        $repository = HelperUtility::create(\HDNET\Calendarize\Domain\Repository\CalDavRepository::class);
        return $repository->findByUserStorage($userRecord['pid']);
    }

    /**
     * Returns a users' information
     *
     * @param string $realm
     * @param string $username
     *
     * @return string
     */
    public function getUserInfo($realm, $username)
    {
        $configuration = $this->findMatchingCalDavConfiguration($username);
        if ($configuration === false) {
            return false;
        }
        $userRow = $this->getUserRow($username);
        if (!isset($userRecord['pid'])) {
            return false;
        }
        $user = [
            'uri'         => 'principals/' . $userRow['username'],
            'digestHash'  => md5($userRow['username'] . ':' . 'SabreDAV' . ':' . $userRow['password']),
            'calendar_id' => $configuration['uid']
        ];
        $this->username = $username;
        if ($userRow['email']) {
            $user['{http://sabredav.org/ns}email-address'] = $userRow['email'];
        }
        return $user;

    }

    /**
     * Get the user record
     *
     * @param string $userName
     *
     * @return array|FALSE|NULL
     */
    protected function getUserRow($userName)
    {
        $dbConnection = HelperUtility::getDatabaseConnection();
        $where = 'username = ' . $dbConnection->fullQuoteStr($userName,
                'fe_users') . BackendUtility::deleteClause($this->tableName) . BackendUtility::BEenableFields($this->tableName);
        return $dbConnection->exec_SELECTgetSingleRow('*', 'fe_users', $where);
    }

}
