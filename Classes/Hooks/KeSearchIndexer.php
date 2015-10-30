<?php

/**
 * KE Search Indexer
 */

namespace HDNET\Calendarize\Hooks;

use HDNET\Autoloader\Utility\IconUtility;
use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Utility\HelperUtility;

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
     * @param \tx_kesearch_indexer $indexerObject Reference to indexer class.
     * @return string|null
     */
    public function customIndexer(&$indexerConfig, &$indexerObject)
    {
        if ($indexerConfig['type'] !== 'calendarize') {
            return null;
        }

        /** @var \HDNET\Calendarize\Domain\Repository\IndexRepository $indexRepository */
        $indexRepository = HelperUtility::create('HDNET\\Calendarize\\Domain\\Repository\\IndexRepository');
        $indexObjects = $indexRepository->findList()->toArray();

        foreach ($indexObjects as $index) {
            /** @var $index Index */

            // @todo implement
            return;

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
                $record['sys_language_uid'],
                $record['starttime'],
                $record['endtime'],
                $record['fe_group'],
                false,
                $additionalFields
            );
        }

        return '<p><b>Custom Indexer "' . $indexerConfig['title'] . '": ' . sizeof($indexObjects) . ' elements have been indexed.</b></p>';
    }
}