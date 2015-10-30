<?php

/**
 * KE Search Indexer
 */

namespace HDNET\Calendarize\Hooks;

use HDNET\Autoloader\Utility\IconUtility;

/**
 * KE Search Indexer
 *
 * @hook TYPO3_CONF_VARS|EXTCONF|ke_search|registerIndexerConfiguration
 * @hook TYPO3_CONF_VARS|EXTCONF|ke_search|customIndexer
 */
class KeSearchIndexer
{
    /**
     * Register the indexer configuration
     *
     * @param array $params
     * @param object $pObj
     */
    function registerIndexerConfiguration(&$params, $pObj)
    {
        $newArray = array(
            'Calendarize Indexer',
            'calendarize',
            IconUtility::getByExtensionKey('calendarize')
        );
        $params['items'][] = $newArray;
        $GLOBALS['TCA']['tx_kesearch_indexerconfig']['columns']['sysfolder']['displayCond'] .= ',calendarize';
    }

    /**
     * Calendarize indexer for ke_search
     *
     * @param array $indexerConfig Configuration from TYPO3 Backend
     * @param array $indexerObject Reference to indexer class.
     * @return string|null
     * @todo finish the integration
     */
    public function customIndexer(&$indexerConfig, &$indexerObject)
    {
        if ($indexerConfig['type'] !== 'calendarize') {
            return null;
        }
        $content = '';

        // get all the entries to index
        // don't index hidden or deleted elements, BUT
        // get the elements with frontend user group access restrictions
        // or time (start / stop) restrictions.
        // Copy those restrictions to the index.
        $fields = '*';
        $table = 'tt_news';
        $where = 'pid IN (' . $indexerConfig['sysfolder'] . ') AND hidden = 0 AND deleted = 0';
        $groupBy = '';
        $orderBy = '';
        $limit = '';
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, $where, $groupBy, $orderBy, $limit);

        $resCount = $GLOBALS['TYPO3_DB']->sql_num_rows($res);

        // Loop through the records and write them to the index.
        if ($resCount) {
            while (($record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {

                // compile the information which should go into the index
                // the field names depend on the table you want to index!
                $title = strip_tags($record['title']);
                $abstract = strip_tags($record['short']);
                $content = strip_tags($record['description']);
                $fullContent = $title . "\n" . $abstract . "\n" . $content;
                $params = '&tx_ttnews[tt_news]=' . $record['uid'];
                $tags = '#example_tag_1#,#example_tag_2#';
                $additionalFields = array(
                    'sortdate' => $record['crdate'],
                    'orig_uid' => $record['uid'],
                    'orig_pid' => $record['pid'],
                    'sortdate' => $record['datetime'],
                );

                // add something to the title, just to identify the entries
                // in the frontend
                $title = '[CUSTOM INDEXER] ' . $title;

                // ... and store the information in the index
                $indexerObject->storeInIndex(
                    $indexerConfig['storagepid'], // storage PID
                    $title, // record title
                    'calendarize', // content type
                    $indexerConfig['targetpid'], // target PID: where is the single view?
                    $fullContent, // indexed content, includes the title (linebreak after title)
                    $tags, // tags for faceted search
                    $params, // typolink params for singleview
                    $abstract, // abstract; shown in result list if not empty
                    $record['sys_language_uid'], // language uid
                    $record['starttime'], // starttime
                    $record['endtime'], // endtime
                    $record['fe_group'], // fe_group
                    false, // debug only?
                    $additionalFields // additionalFields
                );
            }
            $content = '<p><b>Custom Indexer "' . $indexerConfig['title'] . '": ' . $resCount . 'Elements have been indexed.</b></p>';
        }
        return $content;

    }
}