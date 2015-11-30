<?php

/**
 * KE Search Indexer
 */

namespace HDNET\Calendarize\Hooks;

use HDNET\Autoloader\Utility\IconUtility;
use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Features\KeSearchIndexInterface;
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
        // @todo select only $indexerConfig['storagepid']
        $indexObjects = $indexRepository->findList()->toArray();

        foreach ($indexObjects as $index) {
            /** @var $index Index */
            /** @var KeSearchIndexInterface $originalObject */
            $originalObject = $index->getOriginalObject();
            if (!($originalObject instanceof KeSearchIndexInterface)) {
                continue;
            }

            $title = strip_tags($originalObject->getKeSearchTitle($index));
            $abstract = strip_tags($originalObject->getKeSearchAbstract($index));
            $content = strip_tags($originalObject->getKeSearchContent($index));
            $fullContent = $title . "\n" . $abstract . "\n" . $content;

            // @todo Add year and month information
            $additionalFields = [];

            $indexerObject->storeInIndex(
                $indexerConfig['storagepid'],
                $title,
                'calendarize',
                $indexerConfig['targetpid'],
                $fullContent,
                '',
                '&tx_calendarize_calendar[index]=' . $index->getUid(),
                $abstract,
                0,
                0,
                0,
                '',
                false,
                $additionalFields
            );
        }

        return '<p><b>Custom Indexer "' . $indexerConfig['title'] . '": ' . sizeof($indexObjects) . ' elements have been indexed.</b></p>';
    }
}