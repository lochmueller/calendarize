<?php

/**
 * KE Search Indexer.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Hooks;

use HDNET\Autoloader\Annotation\Hook;
use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Domain\Model\Request\OptionRequest;
use HDNET\Calendarize\Domain\Repository\IndexRepository;
use HDNET\Calendarize\Features\KeSearchIndexInterface;
use Tpwd\KeSearch\Indexer\IndexerRunner;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;

/**
 * KE Search Indexer.
 *
 * @Hook(locations={"TYPO3_CONF_VARS|EXTCONF|ke_search|registerIndexerConfiguration", "TYPO3_CONF_VARS|EXTCONF|ke_search|customIndexer"})
 */
class KeSearchIndexer extends AbstractHook
{
    const KEY = 'calendarize';

    /**
     * Register the indexer configuration.
     *
     * @param array  $params
     * @param object $pObj
     */
    public function registerIndexerConfiguration(&$params, $pObj)
    {
        $newArray = [
            'Calendarize Indexer',
            self::KEY,
            'EXT:calendarize/Resources/Public/Icons/Extension.svg',
        ];
        $params['items'][] = $newArray;
    }

    /**
     * Calendarize indexer for ke_search.
     *
     * @param array         $indexerConfig Configuration from TYPO3 Backend
     * @param IndexerRunner $indexerObject reference to indexer class
     *
     * @return string|null
     */
    public function customIndexer(array &$indexerConfig, IndexerRunner &$indexerObject): string
    {
        if (self::KEY !== $indexerConfig['type']) {
            return '';
        }

        /** @var IndexRepository $indexRepository */
        $indexRepository = GeneralUtility::makeInstance(IndexRepository::class);
        $indexRepository->setOverridePageIds(GeneralUtility::intExplode(',', $indexerConfig['sysfolder']));
        $options = new OptionRequest();
        $indexObjects = $indexRepository->findAllForBackend($options)
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

            $additionalFields = [
                'sortdate' => $index->getStartDateComplete()->getTimestamp(),
                'orig_uid' => $index->getUid(),
                'orig_pid' => $index->getPid(),
            ];

            $params = HttpUtility::buildQueryString([
                'tx_calendarize_calendar' => [
                    'index' => $index->getUid(),
                    'controller' => 'Calendar',
                    'action' => 'detail',
                ],
            ], '&');

            $storeArguments = [
                $indexerConfig['storagepid'],               // storage PID
                $title,                                     // record title
                self::KEY,                                  // content type
                $indexerConfig['targetpid'],                // target PID: where is the single view?
                $fullContent,                               // indexed content, includes the title (linebreak after title)
                $originalObject->getKeSearchTags($index),   // tags for faceted search
                $params,                                    // typolink params for singleview
                $abstract,                                  // abstract; shown in result list if not empty
                $index->_getProperty('_languageUid'), // $index always has a "_languageUid" - if the $originalObject does not use translations, it is 0
                $index->_hasProperty('starttime') ? $index->_getProperty('starttime') : 0,
                $index->_hasProperty('endtime') ? $index->_getProperty('endtime') : 0,
                $index->_hasProperty('fe_group') ? $index->_getProperty('fe_group') : '',
                false,                                      // debug only?
                $additionalFields,                          // additionalFields
            ];

            $indexerObject->storeInIndex(...$storeArguments);
        }

        return 'Custom Indexer "' . $indexerConfig['title'] . '": ' . \count($indexObjects) . ' elements have been indexed.';
    }
}
