<?php
/**
 * Backend for events
 *
 * @author  Tim Lochmüller
 */
namespace HDNET\Calendarize\Service\CalDav;

use HDNET\Calendarize\Utility\HelperUtility;
use Sabre\CalDAV\Backend\AbstractBackend;
use Sabre\CalDAV\Plugin;
use Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet;
use Sabre\DAV\PropPatch;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Backend for events
 */
class BackendTypo3 extends AbstractBackend
{

    /**
     * The table name that will be used for calendars
     *
     * @var string
     */
    protected $calendarTableName;

    /**
     * The table name that will be used for calendar objects
     *
     * @var string
     */
    protected $calendarObjectTableName;

    /**
     * List of CalDAV properties, and how they map to database fieldnames
     *
     * Add your own properties by simply adding on to this array
     *
     * @var array
     */
    public $propertyMap = [
        '{DAV:}displayname'                                   => 'title',
        '{urn:ietf:params:xml:ns:caldav}calendar-description' => 'tx_caldav_data',
        '{urn:ietf:params:xml:ns:caldav}calendar-timezone'    => 'timezone',
        '{http://apple.com/ns/ical/}calendar-order'           => 'calendarorder',
        '{http://apple.com/ns/ical/}calendar-color'           => 'calendarcolor'
    ];

    /**
     * Creates the backend
     *
     * @param string $calendarTableName
     * @param string $calendarObjectTableName
     */
    public function __construct($calendarTableName = 'calendars', $calendarObjectTableName = 'calendarobjects')
    {
        $this->calendarTableName = $calendarTableName;
        $this->calendarObjectTableName = $calendarObjectTableName;
    }

    /**
     * Returns a list of calendars for a principal.
     *
     * Every project is an array with the following keys:
     * * id, a unique id that will be used by other functions to modify the
     * calendar. This can be the same as the uri or a database key.
     * * uri, which the basename of the uri with which the calendar is
     * accessed.
     * * principalUri. The owner of the calendar. Almost always the same as
     * principalUri passed to this method.
     *
     * Furthermore it can contain webdav properties in clark notation. A very
     * common one is '{DAV:}displayname'.
     *
     * @param string $principalUri
     *
     * @return array
     */
    public function getCalendarsForUser($principalUri)
    {
        $principalUriParts = explode('/', $principalUri);
        $databaseConnection = HelperUtility::getDatabaseConnection();
        // $databaseConnection->
        die('getCalendarsForUser');
        $stmt = $this->pdo->prepare('SELECT uid, tx_cal_calendar FROM fe_users WHERE username = ? AND deleted=0');
        $stmt->execute([
            array_pop($principalUriParts)
        ]);

        $calendars = [];

        while ($user = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $stmt = $this->pdo->prepare('SELECT * FROM tx_cal_calendar WHERE uid in (' . $user ['tx_cal_calendar'] . ')');
            $stmt->execute();

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $components = explode(',', 'VEVENT,VTODO');

                $calendar = [
                    'id'                                                          => $row ['uid'],
                    'uri'                                                         => $row ['title'],
                    'principaluri'                                                => $principalUri,
                    '{' . Plugin::NS_CALENDARSERVER . '}getctag'                  => $row ['tstamp'] ? $row ['tstamp'] : '0',
                    '{' . Plugin::NS_CALDAV . '}supported-calendar-component-set' => new SupportedCalendarComponentSet($components),
                    '{DAV:}displayname'                                           => $row ['title'],
                    '{urn:ietf:params:xml:ns:caldav}calendar-description'         => '',
                    '{urn:ietf:params:xml:ns:caldav}calendar-timezone'            => null,
                    '{http://apple.com/ns/ical/}calendar-order'                   => 0,
                    '{http://apple.com/ns/ical/}calendar-color'                   => null
                ];

                $calendars [] = $calendar;
            }
        }

        return $calendars;
    }

    /**
     * Creates a new calendar for a principal.
     *
     * If the creation was a success, an id must be returned that can be used to reference
     * this calendar in other methods, such as updateCalendar
     *
     * @param string $principalUri
     * @param string $calendarUri
     * @param array  $properties
     *
     * @return mixed
     * @throws Sabre_DAV_Exception
     */
    public function createCalendar($principalUri, $calendarUri, array $properties)
    {
        $fieldNames = [
            'principaluri',
            'uri',
            'ctag'
        ];
        $values = [
            ':principaluri' => $principalUri,
            ':uri'          => $calendarUri,
            ':ctag'         => 1
        ];

        // Default value
        $sccs = '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set';
        $fieldNames [] = 'components';
        if (!isset($properties [$sccs])) {
            $values [':components'] = 'VEVENT,VTODO';
        } else {
            if (!($properties [$sccs] instanceof Sabre_CalDAV_Property_SupportedCalendarComponentSet)) {
                throw new Sabre_DAV_Exception('The ' . $sccs . ' property must be of type: Sabre_CalDAV_Property_SupportedCalendarComponentSet');
            }
            $values [':components'] = implode(',', $properties [$sccs]->getValue());
        }

        foreach ($this->propertyMap as $xmlName => $dbName) {
            if (isset($properties [$xmlName])) {
                $myValue = $properties [$xmlName];
                $values [':' . $dbName] = $properties [$xmlName];
                $fieldNames [] = $dbName;
            }
        }

        $stmt = $this->pdo->prepare('INSERT INTO tx_cal_calendar (' . implode(', ', $fieldNames) . ') VALUES (' . implode(
            ', ',
            array_keys($values)
        ) . ')');
        $stmt->execute($values);

        return $this->pdo->lastInsertId();
    }

    /**
     * Updates a calendars properties
     *
     * The properties array uses the propertyName in clark-notation as key,
     * and the array value for the property value. In the case a property
     * should be deleted, the property value will be null.
     *
     * This method must be atomic. If one property cannot be changed, the
     * entire operation must fail.
     *
     * If the operation was successful, true can be returned.
     * If the operation failed, false can be returned.
     *
     * Deletion of a non-existant property is always succesful.
     *
     * Lastly, it is optional to return detailed information about any
     * failures. In this case an array should be returned with the following
     * structure:
     *
     * array(
     * 403 => array(
     * '{DAV:}displayname' => null,
     * ),
     * 424 => array(
     * '{DAV:}owner' => null,
     * )
     * )
     *
     * In this example it was forbidden to update {DAV:}displayname.
     * (403 Forbidden), which in turn also caused {DAV:}owner to fail
     * (424 Failed Dependency) because the request needs to be atomic.
     *
     * @param string               $calendarId
     * @param PropPatch $properties
     *
     * @return bool|array
     */
    public function updateCalendar($calendarId, PropPatch $properties)
    {
        $newValues = [];
        $result = [
            200 => [], // Ok
            403 => [], // Forbidden
            424 => []  // Failed Dependency
        ];

        $hasError = false;

        foreach ($properties as $propertyName => $propertyValue) {
            // We don't know about this property.
            if (!isset($this->propertyMap [$propertyName])) {
                $hasError = true;
                $result [403] [$propertyName] = null;
                unset($properties [$propertyName]);
                continue;
            }

            $fieldName = $this->propertyMap [$propertyName];
            $newValues [$fieldName] = $propertyValue;
        }

        // If there were any errors we need to fail the request
        if ($hasError) {
            // Properties has the remaining properties
            foreach ($properties as $propertyName => $propertyValue) {
                $result [424] [$propertyName] = null;
            }

            // Removing unused statuscodes for cleanliness
            foreach ($result as $status => $properties) {
                if (is_array($properties) && count($properties) === 0) {
                    unset($result [$status]);
                }
            }
            return $result;
        }

        // Success

        // Now we're generating the sql query.
        $valuesSql = [];
        foreach ($newValues as $fieldName => $value) {
            $valuesSql [] = $fieldName . ' = ?';
        }
        $valuesSql [] = time();

        $stmt = $this->pdo->prepare('UPDATE tx_cal_calendar SET ' . implode(', ', $valuesSql) . ' WHERE id = ?');
        $newValues ['id'] = $calendarId;
        $stmt->execute(array_values($newValues));

        $stmt = $this->pdo->prepare('SELECT * FROM tx_cal_calendar WHERE uid = ?');
        $stmt->execute([
            $calendarId
        ]);
        $calendarRow = $stmt->fetch();
        $this->clearCache($calendarRow ['pid']);

        return true;
    }

    /**
     * Delete a calendar and all it's objects
     *
     * @param string $calendarId
     *
     * @return void
     */
    public function deleteCalendar($calendarId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tx_cal_calendar WHERE uid = ?');
        $stmt->execute([
            $calendarId
        ]);
        $calendarRow = $stmt->fetch();

        $stmt = $this->pdo->prepare('DELETE FROM tx_cal_event WHERE calendar_id = ?');
        $stmt->execute([
            $calendarId
        ]);

        $stmt = $this->pdo->prepare('DELETE FROM tx_cal_calendar WHERE uid = ?');
        $stmt->execute([
            $calendarId
        ]);
        $this->clearCache($calendarRow ['pid']);
    }

    /**
     * Returns all calendar objects within a calendar object.
     *
     * Every item contains an array with the following keys:
     * * id - unique identifier which will be used for subsequent updates
     * * calendardata - The iCalendar-compatible calnedar data
     * * uri - a unique key which will be used to construct the uri. This can be any arbitrary string.
     * * lastmodified - a timestamp of the last modification time
     *
     * @param string $calendarId
     *
     * @return array
     */
    public function getCalendarObjects($calendarId)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tx_cal_event WHERE calendar_id = ? AND deleted = 0');
        $stmt->execute([
            $calendarId
        ]);
        $eventArray = $stmt->fetchAll();
        $preparedArray = [];
        foreach ($eventArray as $eventRow) {
            if ($eventRow ['tx_caldav_uid'] == '' && $eventRow ['icsUid'] == '') {
                $eventRow ['tx_caldav_uid'] = 'a1b2c3_' . $eventRow ['calendar_id'] . '_' . $eventRow ['uid'];
                $eventRow ['icsUid'] = $eventRow ['tx_caldav_uid'];
                $stmt = $this->pdo->prepare('UPDATE tx_cal_event SET tx_caldav_uid = ?, icsUid = ? WHERE uid = ?');
                $stmt->execute([
                    $eventRow ['tx_caldav_uid'],
                    $eventRow ['icsUid'],
                    $eventRow ['uid']
                ]);
            } elseif ($eventRow ['tx_caldav_uid'] == '') {
                $eventRow ['tx_caldav_uid'] = $eventRow ['icsUid'];
                $stmt = $this->pdo->prepare('UPDATE tx_cal_event SET tx_caldav_uid = ? WHERE uid = ?');
                $stmt->execute([
                    $eventRow ['tx_caldav_uid'],
                    $eventRow ['uid']
                ]);
            } elseif ($eventRow ['icsUid'] == '') {
                $eventRow ['icsUid'] = $eventRow ['tx_caldav_uid'];
                $stmt = $this->pdo->prepare('UPDATE tx_cal_event SET icsUid = ? WHERE uid = ?');
                $stmt->execute([
                    $eventRow ['icsUid'],
                    $eventRow ['uid']
                ]);
            }
            $preparedArray [] = [
                'id'           => $eventRow ['uid'],
                'displayname'  => $eventRow ['title'],
                'calendardata' => $eventRow ['tx_caldav_data'],
                'uri'          => $eventRow ['tx_caldav_uid'],
                'calendarid'   => $calendarId,
                'lastmodified' => $eventRow ['tstamp']
            ];
        }
        return $preparedArray;
    }

    /**
     * Returns information from a single calendar object, based on it's object uri.
     *
     * @param string $calendarId
     * @param string $objectUri
     *
     * @return array
     */
    public function getCalendarObject($calendarId, $objectUri)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tx_cal_event WHERE calendar_id = ? AND tx_caldav_uid = ? AND deleted = 0');
        $stmt->execute([
            $calendarId,
            $objectUri
        ]);
        $eventRow = $stmt->fetch();
        if (empty($eventRow)) {
            return [];
        }
        return [
            'id'           => $eventRow ['uid'],
            'displayname'  => $eventRow ['title'],
            'calendardata' => $eventRow ['tx_caldav_data'],
            'uri'          => $eventRow ['icsUid'],
            'calendarid'   => $calendarId,
            'lastmodified' => $eventRow ['tstamp']
        ];
    }

    /**
     * Creates a new calendar object.
     *
     * @param string $calendarId
     * @param string $objectUri
     * @param string $calendarData
     *
     * @return void
     */
    public function createCalendarObject($calendarId, $objectUri, $calendarData)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tx_cal_calendar WHERE uid = ?');
        $stmt->execute([
            $calendarId
        ]);
        $calendarRow = $stmt->fetch();

        $stmt = $this->pdo->prepare('INSERT INTO tx_cal_event (pid,calendar_id, tx_caldav_uid, tx_caldav_data, tstamp) VALUES (?,?,?,?,?)');
        $uid = $this->pdo->lastInsertId();
        $stmt->execute([
            $calendarRow ['pid'],
            $calendarId,
            $objectUri,
            $calendarData,
            time()
        ]);
        $stmt = $this->pdo->prepare('UPDATE tx_cal_calendar SET tstamp = tstamp + 1 WHERE uid = ? AND deleted = 0');
        $stmt->execute([
            $calendarId
        ]);
        $this->updateCalEvent($calendarId, $objectUri, $calendarData);
        $this->clearCache($calendarRow ['pid']);
    }

    /**
     * Updates an existing calendarobject, based on it's uri.
     *
     * @param string $calendarId
     * @param string $objectUri
     * @param string $calendarData
     *
     * @return void
     */
    public function updateCalendarObject($calendarId, $objectUri, $calendarData)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tx_cal_event WHERE calendar_id = ?');
        $stmt->execute([
            $calendarId
        ]);
        $calendarRow = $stmt->fetch();
        $stmt = $this->pdo->prepare('UPDATE tx_cal_event SET tx_caldav_data = ?, tstamp = ? WHERE calendar_id = ? AND icsUid = ? AND deleted = 0');
        $stmt->execute([
            $calendarData,
            time(),
            $calendarId,
            $objectUri
        ]);
        $stmt = $this->pdo->prepare('UPDATE tx_cal_calendar SET tstamp = tstamp + 1 WHERE uid = ? AND deleted = 0');
        $stmt->execute([
            $calendarId
        ]);
        $this->updateCalEvent($calendarId, $objectUri, $calendarData);
        $this->clearCache($calendarRow ['pid']);
    }

    /**
     * Deletes an existing calendar object.
     *
     * @param string $calendarId
     * @param string $objectUri
     *
     * @return void
     */
    public function deleteCalendarObject($calendarId, $objectUri)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tx_cal_event WHERE calendar_id = ?');
        $stmt->execute([
            $calendarId
        ]);
        $calendarRow = $stmt->fetch();

        $stmt = $this->pdo->prepare('DELETE FROM tx_cal_event WHERE calendar_id = ? AND icsUid = ? AND deleted = 0');
        $stmt->execute([
            $calendarId,
            $objectUri
        ]);
        $stmt = $this->pdo->prepare('UPDATE tx_cal_calendar SET tstamp = tstamp + 1 WHERE uid = ? AND deleted = 0');
        $stmt->execute([
            $calendarId
        ]);
        $this->clearCache($calendarRow ['pid']);
    }

    /**
     * Update cal event
     *
     * @param $calendarId
     * @param $objectUri
     * @param $calendarData
     */
    private function updateCalEvent($calendarId, $objectUri, $calendarData)
    {
        // var_dump($calendarId);
        // var_dump($objectUri);
        // var_dump($calendarData);
        // die();
    }

    /**
     * Clear cache
     *
     * @param int $pid
     */
    private function clearCache($pid)
    {
        $pageTSConf = BackendUtility::getPagesTSconfig($pid);
        $pageIDForPlugin = $pid;

        if ($pageTSConf ['TCEMAIN.'] ['clearCacheCmd']) {
            $pageIDForPlugin = $pageTSConf ['TCEMAIN.'] ['clearCacheCmd'];
        }

        $tce = GeneralUtility::makeInstance(DataHandler::class);
        // 		$tce->clear_cacheCmd ( $pageIDForPlugin ); // ID of the page for which to clear the cache
    }
}
