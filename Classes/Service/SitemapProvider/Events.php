<?php

namespace HDNET\Calendarize\Service\SitemapProvider;

use FRUIT\GoogleServices\Controller\SitemapController;
use FRUIT\GoogleServices\Domain\Model\Node;
use FRUIT\GoogleServices\Service\SitemapProviderInterface;
use HDNET\Calendarize\Domain\Repository\IndexRepository;
use HDNET\Calendarize\Utility\DateTimeUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Events
 */
class Events implements SitemapProviderInterface
{

    /**
     * Get the Records
     *
     * @param integer $startPage
     * @param array $basePages
     * @param SitemapController $obj
     *
     * @return array of Node objects
     */
    public function getRecords($startPage, $basePages, SitemapController $obj): array
    {
        $nodes = [];
        foreach ($this->getIndizies() as $index) {

            $additionalParams = [
                'tx_calendarize_calendar' => [
                    'index' => $index->getUid()
                ],
            ];

            // Build URL
            $url = $obj->getUriBuilder()
                ->setTargetPageUid($startPage)
                ->setArguments($additionalParams)
                ->setCreateAbsoluteUri(true)
                ->build();

            // Build Node
            $node = new Node();
            $node->setLoc($url);
            $node->setPriority(0.9);
            $node->setChangefreq('monthly');

            $nodes[] = $node;
        }

        return $nodes;
    }

    /**
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    protected function getIndizies()
    {
        $objectManager = new ObjectManager();
        $indexRepository = $objectManager->get(IndexRepository::class);
        $now = new \DateTime();
        return $indexRepository->findByTimeSlot($now->getTimestamp(), $now->getTimestamp() + DateTimeUtility::SECONDS_YEAR);
    }
}
