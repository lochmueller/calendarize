<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Typolink;

use HDNET\Calendarize\Domain\Repository\IndexRepository;
use HDNET\Calendarize\Register;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\ClassNamingUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Frontend\Typolink\DatabaseRecordLinkBuilder as BaseDatabaseRecordLinkBuilderAlias;
use TYPO3\CMS\Frontend\Typolink\LinkResultInterface;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;

/**
 * DatabaseRecordLinkBuilder.
 */
class DatabaseRecordLinkBuilder extends BaseDatabaseRecordLinkBuilderAlias
{
    public function build(array &$linkDetails, string $linkText, string $target, array $conf): LinkResultInterface
    {
        if (isset($linkDetails['identifier']) && \in_array($linkDetails['identifier'], $this->getEventTables(), true)) {
            $eventId = $linkDetails['uid'];
            $typoScriptArray = $this->contentObjectRenderer->getRequest()->getAttribute('frontend.typoscript')?->getSetupArray() ?? [];
            $defaultPid = (int)($typoScriptArray['plugin.']['tx_calendarize.']['settings.']['defaultDetailPid'] ?? 0);
            if ($defaultPid <= 0) {
                throw new \RuntimeException('You have to configure calendarize:defaultDetailPid to use the linkhandler function');
            }

            $indexUid = $this->getIndexForEventUid($linkDetails['identifier'], $eventId);

            if (!$indexUid) {
                throw new UnableToLinkException('Indices not found for "' . $linkDetails['typoLinkParameter'] . '", so "' . $linkText . '" was not linked.', 1699909349, null, $linkText);
            }

            $this->populateRecordLinkConfiguration($linkDetails['identifier'], $defaultPid, $indexUid);
        }

        return parent::build($linkDetails, $linkText, $target, $conf);
    }

    /**
     * We simply have to provide the configuration for the record links of the current event type.
     * The rest is done by our parent which takes care of stuff like CSS classes added in the BE to a link.
     */
    protected function populateRecordLinkConfiguration(string $eventTable, int $pageUid, int $indexUid): void
    {
        /** @var FrontendTypoScript $frontendTypoScript */
        $frontendTypoScript = $this->contentObjectRenderer->getRequest()->getAttribute('frontend.typoscript');
        if (null === $frontendTypoScript) {
            return;
        }

        $typoScriptArray = $frontendTypoScript->getSetupArray() ?? [];
        $typoScriptArray['config.']['recordLinks.'][$eventTable . '.']['typolink.'] = [
            'parameter' => $pageUid,
            'additionalParams' => HttpUtility::buildQueryString([
                'tx_calendarize_calendar' => [
                    'index' => $indexUid,
                    'controller' => 'Calendar',
                    'action' => 'detail',
                ],
            ], '&'),
        ];
        $frontendTypoScript->setSetupArray($typoScriptArray);
    }

    protected function getIndexForEventUid($table, $uid): int
    {
        $indexRepository = GeneralUtility::makeInstance(IndexRepository::class);

        $event = null;
        foreach (Register::getRegister() as $value) {
            if ($value['tableName'] === $table) {
                $repositoryName = ClassNamingUtility::translateModelNameToRepositoryName($value['modelName']);
                if (class_exists($repositoryName)) {
                    $repository = GeneralUtility::makeInstance($repositoryName);
                    $event = $repository->findByUid($uid);
                }
            }
        }

        if (!($event instanceof DomainObjectInterface)) {
            return 0;
        }

        $fetchEvent = $indexRepository->findByEventTraversing($event, true, false, 1)->toArray();
        if (\count($fetchEvent) <= 0) {
            $fetchEvent = $indexRepository
                ->findByEventTraversing($event, false, true, 1, QueryInterface::ORDER_DESCENDING)
                ->toArray();
        }

        if (empty($fetchEvent)) {
            return 0;
        }

        return (int)$fetchEvent[0]->getUid();
    }

    protected function getEventTables(): array
    {
        static $tables;
        if (!\is_array($tables)) {
            $tables = array_map(static function ($config) {
                return $config['tableName'];
            }, Register::getRegister());
        }

        return $tables;
    }
}
