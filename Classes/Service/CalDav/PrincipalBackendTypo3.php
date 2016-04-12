<?php
/**
 * PrincipalBackendTypo3
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service\CalDav;

use Sabre\DAV\Exception;
use Sabre\DAV\PropPatch;
use Sabre\DAVACL\PrincipalBackend\BackendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * PrincipalBackendTypo3
 */
class PrincipalBackendTypo3 implements BackendInterface
{

    /**
     * pdo
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     * PDO table name for 'principals'
     *
     * @var string
     */
    protected $tableName;

    /**
     * PDO table name for 'group members'
     *
     * @var string
     */
    protected $groupMembersTableName;

    /**
     * Sets up the backend.
     *
     * @param \PDO   $pdo
     * @param string $tableName
     * @param string $groupMembersTableName
     */
    public function __construct(\PDO $pdo, $tableName = 'fe_users', $groupMembersTableName = 'fe_groups')
    {

        $this->pdo = $pdo;
        $this->tableName = $tableName;
        $this->groupMembersTableName = $groupMembersTableName;

    }

    /**
     * Returns a list of principals based on a prefix.
     *
     * This prefix will often contain something like 'principals'. You are only
     * expected to return principals that are in this base path.
     *
     * You are expected to return at least a 'uri' for every user, you can
     * return any additional properties if you wish so. Common properties are:
     *   {DAV:}displayname
     *   {http://sabredav.org/ns}email-address - This is a custom SabreDAV
     *     field that's actualy injected in a number of other properties. If
     *     you have an email address, use this property.
     *
     * @param string $prefixPath
     *
     * @return array
     */
    public function getPrincipalsByPrefix($prefixPath)
    {
        $result = $this->pdo->query('SELECT username, email, name FROM `' . $this->tableName . '`');
        $principals = [];
        while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
            // Checking if the principal is in the prefix
            list($rowPrefix) = \Sabre\Uri\split('principals/' . $row['username']);
            if ($rowPrefix !== $prefixPath) {
                continue;
            }
            $principals[] = [
                'uri'                                   => 'principals/' . $row['username'],
                '{DAV:}displayname'                     => $row['name'] ? $row['name'] : basename('principals/' . $row['username']),
                '{http://sabredav.org/ns}email-address' => $row['email'],
            ];
        }

        return $principals;

    }

    /**
     * Returns a specific principal, specified by it's path.
     * The returned structure should be the exact same as from
     * getPrincipalsByPrefix.
     *
     * @param string $path
     *
     * @return array
     */
    public function getPrincipalByPath($path)
    {
        $pathParts = GeneralUtility::trimExplode('/', $path);
        $name = $pathParts[1];
        $stmt = $this->pdo->prepare('SELECT uid, username, email, name FROM `' . $this->tableName . '` WHERE username = ?');
        $stmt->execute([$name]);

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return;
        }
        $return = [
            'id'                                    => $row['uid'],
            'uri'                                   => 'principals/' . $row['username'],
            '{DAV:}displayname'                     => $row['name'] ? $row['name'] : basename($row['username']),
            '{http://sabredav.org/ns}email-address' => $row['email'],
        ];

        return $return;

    }

    /**
     * Returns the list of members for a group-principal
     *
     * @param $principal
     *
     * @return array
     * @throws Exception
     */
    public function getGroupMemberSet($principal)
    {
        var_dump('getGroupMemberSet');
        $principal = $this->getPrincipalByPath($principal);
        if (!$principal) {
            throw new Exception('Principal not found');
        }

        // calendar title
        $result[] = $principal['uri'] . '/test';

        return $result;

    }

    /**
     * Returns the list of groups a principal is a member of
     *
     * @param string $principal
     *
     * @return array
     * @throws Exception
     */
    public function getGroupMembership($principal)
    {
        var_dump('getGroupMembership');
        $principal = $this->getPrincipalByPath($principal);
        if (!$principal) {
            throw new Exception('Principal not found');
        }

        $result[] = $principal['uri'];
        return $result;
    }

    /**
     * Updates the list of group members for a group principal.
     *
     * The principals should be passed as a list of uri's.
     *
     * @param string $principal
     * @param array  $members
     *
     * @throws Sabre_DAV_Exception
     */
    public function setGroupMemberSet($principal, array $members)
    {
        var_dump('setGroupMemberSet');
        // Grabbing the list of principal id's.
        $stmt = $this->pdo->prepare('SELECT id, uri FROM `' . $this->tableName . '` WHERE uri IN (? ' . str_repeat(', ? ',
                count($members)) . ');');
        $stmt->execute(array_merge([$principal], $members));

        $memberIds = [];
        $principalId = null;

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['uri'] == $principal) {
                $principalId = $row['id'];
            } else {
                $memberIds[] = $row['id'];
            }
        }
        if (!$principalId) {
            throw new Sabre_DAV_Exception('Principal not found');
        }

        // Wiping out old members
        $stmt = $this->pdo->prepare('DELETE FROM `' . $this->groupMembersTableName . '` WHERE principal_id = ?;');
        $stmt->execute([$principalId]);

        foreach ($memberIds as $memberId) {

            $stmt = $this->pdo->prepare('INSERT INTO `' . $this->groupMembersTableName . '` (principal_id, member_id) VALUES (?, ?);');
            $stmt->execute([$principalId, $memberId]);

        }

    }

    /**
     * Updates one ore more webdav properties on a principal.
     *
     * The list of mutations is stored in a Sabre\DAV\PropPatch object.
     * To do the actual updates, you must tell this object which properties
     * you're going to process with the handle() method.
     *
     * Calling the handle method is like telling the PropPatch object "I
     * promise I can handle updating this property".
     *
     * Read the PropPatch documenation for more info and examples.
     *
     * @param string               $path
     * @param PropPatch $propPatch
     *
     * @return void
     */
    function updatePrincipal($path, PropPatch $propPatch)
    {
        var_dump('updatePrincipal');
        // TODO: Implement updatePrincipal() method.
    }

    /**
     * This method is used to search for principals matching a set of
     * properties.
     *
     * This search is specifically used by RFC3744's principal-property-search
     * REPORT.
     *
     * The actual search should be a unicode-non-case-sensitive search. The
     * keys in searchProperties are the WebDAV property names, while the values
     * are the property values to search on.
     *
     * By default, if multiple properties are submitted to this method, the
     * various properties should be combined with 'AND'. If $test is set to
     * 'anyof', it should be combined using 'OR'.
     *
     * This method should simply return an array with full principal uri's.
     *
     * If somebody attempted to search on a property the backend does not
     * support, you should simply return 0 results.
     *
     * You can also just return 0 results if you choose to not support
     * searching at all, but keep in mind that this may stop certain features
     * from working.
     *
     * @param string $prefixPath
     * @param array  $searchProperties
     * @param string $test
     *
     * @return array
     */
    function searchPrincipals($prefixPath, array $searchProperties, $test = 'allof')
    {
        var_dump('searchPrincipals');
        // TODO: Implement searchPrincipals() method.
    }

    /**
     * Finds a principal by its URI.
     *
     * This method may receive any type of uri, but mailto: addresses will be
     * the most common.
     *
     * Implementation of this API is optional. It is currently used by the
     * CalDAV system to find principals based on their email addresses. If this
     * API is not implemented, some features may not work correctly.
     *
     * This method must return a relative principal path, or null, if the
     * principal was not found or you refuse to find it.
     *
     * @param string $uri
     * @param string $principalPrefix
     *
     * @return string
     */
    function findByUri($uri, $principalPrefix)
    {
        var_dump('findByUri');
        // TODO: Implement findByUri() method.
    }
}
