<?php
/**
 * TYPO3 Auth backend
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service\CalDav;

use HDNET\Calendarize\Service\CalDav;
use Sabre\DAV\Auth\Backend\AbstractBasic;
use Sabre\DAV\Exception;
use Sabre\DAV\Exception\NotAuthenticated;
use Sabre\DAV\Server;
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
     * @return bool|array
     */
    protected function validateUserPass($username, $password)
    {
        $_GET['logintype'] = 'login';
        $_GET['user'] = $username;
        $_GET['pass'] = $password;
        $_GET['challenge'] = '';
        // @todo
        $_GET['pid'] = 118;
        $GLOBALS['TYPO3_CONF_VARS']['FE']['loginSecurityLevel'] = 'normal';

        /** @var CalDav $calDav */
        $calDav = GeneralUtility::makeInstance('HDNET\\Calendarize\\Service\\CalDav');
        $calDav->buildFrontend();

        $feUserObj = $GLOBALS['TSFE']->fe_user;

        if (is_array($feUserObj->user) && $feUserObj->user['uid'] && $feUserObj->user['is_online']) {
            $user = array(
                'uri'         => 'principals/' . $username,
                'digestHash'  => md5($username . ':' . 'SabreDAV' . ':' . $username),
                // @todo
                'calendar_id' => 1
            );

            if ($feUserObj->user['email']) {
                $user['{http://sabredav.org/ns}email-address'] = $feUserObj->user['email'];
            }

            return $user;
        } else {
            return false;
        }
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
        $stmt = $this->pdo->prepare('SELECT username, password, email FROM fe_users WHERE username = ?');
        $stmt->execute(array($username));
        $result = $stmt->fetchAll();

        if (!count($result)) {
            return false;
        }
        $user = array(
            'uri'         => 'principals/' . $result[0]['username'],
            'digestHash'  => md5($result[0]['username'] . ':' . 'SabreDAV' . ':' . $result[0]['password']),
            'calendar_id' => $result[0]['tx_cal_calendar']
        );
        $this->username = $username;
        if ($result[0]['email']) {
            $user['{http://sabredav.org/ns}email-address'] = $result[0]['email'];
        }
        return $user;

    }

    /**
     * Authenticates the user based on the current request.
     *
     * If authentication is succesful, true must be returned.
     * If authentication fails, an exception must be thrown.
     *
     * @param Server $server
     * @param        $realm
     *
     * @return bool
     * @throws Exception
     * @throws NotAuthenticated
     */
    public function authenticate(Server $server, $realm)
    {
        $auth = new \Sabre_HTTP_BasicAuth();
        $auth->setHTTPRequest($server->httpRequest);
        $auth->setHTTPResponse($server->httpResponse);
        $auth->setRealm($realm);
        $userpass = $auth->getUserPass();
        if (!$userpass) {
            $auth->requireLogin();
            throw new NotAuthenticated('No basic authentication headers were found');
        }

        // Authenticates the user
        if (!($userData = $this->validateUserPass($userpass[0], $userpass[1]))) {
            $auth->requireLogin();
            throw new NotAuthenticated('Username or password does not match');
        }
        if (!isset($userData['uri'])) {
            throw new Exception('The returned array from validateUserPass must contain at a uri element');
        }
        $this->currentUser = $userpass[0];
        return true;
    }

}