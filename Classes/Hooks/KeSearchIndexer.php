<?php

/**
 * KE Search Indexer
 */

namespace HDNET\Calendarize\Hooks;

use HDNET\Autoloader\Utility\IconUtility;
use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Domain\Repository\IndexRepository;
use HDNET\Calendarize\Features\KeSearchIndexInterface;
use HDNET\Calendarize\Utility\HelperUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * KE Search Indexer
 *
 * @hook TYPO3_CONF_VARS|EXTCONF|ke_search|registerIndexerConfiguration
 * @hook TYPO3_CONF_VARS|EXTCONF|ke_search|customIndexer
 */
class KeSearchIndexer extends AbstractHook
{

    /**
     * Register the indexer configuration
     *
     * @param array  $params
     * @param object $pObj
     */
    public function registerIndexerConfiguration(&$params, $pObj)
    {
        $newArray = [
            'Calendarize Indexer',
            'calendarize',
            IconUtility::getByExtensionKey('calendarize')
        ];
        $params['items'][] = $newArray;
    }

    /**
     * Calendarize indexer for ke_search
     *
     * @param array                $indexerConfig Configuration from TYPO3 Backend
     * @param \tx_kesearch_indexer $indexerObject Reference to indexer class.
     *
     * @return string|null
     */
    public function customIndexer(&$indexerConfig, &$indexerObject)
    {
        if ($indexerConfig['type'] !== 'calendarize') {
            return null;
        }

        /** @var IndexRepository $indexRepository */
        $indexRepository = HelperUtility::create(IndexRepository::class);
        $indexRepository->setOverridePageIds(GeneralUtility::intExplode(',', $indexerConfig['sysfolder']));
        $indexObjects = $indexRepository->findList()
            ->toArray();

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
            $additionalFields = [
                'sortdate' => $index->getStartDateComplete()->getTimestamp(),
                'orig_uid' => $index->getUid(),
                'orig_pid' => $index->getPid(),
            ];

            $indexerObject->storeInIndex(
                $indexerConfig['storagepid'],
                $title,
                'calendarize',
                $indexerConfig['targetpid'],
                $fullContent,
                $originalObject->getKeSearchTags($index),
                "&tx_calendarize_calendar[index]={$index->getUid()}",
                $abstract,
                $index->_getProperty('_languageUid'),   // $index always has a "_languageUid" - if the $originalObject does not use translations, it is 0
                $index->_hasProperty('starttime') ? $index->_getProperty('starttime') : 0,
                $index->_hasProperty('endtime') ? $index->_getProperty('endtime') : 0,
                $index->_hasProperty('fe_group') ? $index->_getProperty('fe_group') : '',
                false,  // debugOnly
                $additionalFields
            );
        }

        return '<p><b>Custom Indexer "' . $indexerConfig['title'] . '": ' . count($indexObjects) . ' elements have been indexed.</b></p>';
    }
}
