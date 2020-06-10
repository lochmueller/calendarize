<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Typolink;

use HDNET\Calendarize\Domain\Repository\IndexRepository;
use HDNET\Calendarize\Register;
use TYPO3\CMS\Core\Utility\ClassNamingUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;

/**
 * DatabaseRecordLinkBuilder.
 */
class DatabaseRecordLinkBuilder extends \TYPO3\CMS\Frontend\Typolink\DatabaseRecordLinkBuilder
{
    public function build(array &$linkDetails, string $linkText, string $target, array $conf): array
    {
        if (isset($linkDetails['identifier']) && \in_array($linkDetails['identifier'], $this->getEventTables(), true)) {
            $eventId = $linkDetails['uid'];
            $defaultPid = (int)($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_calendarize.']['settings.']['defaultDetailPid'] ?? 0);
            if ($defaultPid <= 0) {
                throw new \Exception('You have to configure calendarize:defaultDetailPid to use the linkhandler function');
            }

            $typoScriptConfiguration = [
                'parameter' => $defaultPid,
                'additionalParams' => '&tx_calendarize_calendar[index]=' . $this->getIndexForEventUid($linkDetails['identifier'], $eventId),
            ];

            $localContentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
            $localContentObjectRenderer->parameters = $this->contentObjectRenderer->parameters;
            $link = $localContentObjectRenderer->typoLink($linkText, $typoScriptConfiguration);

            $this->contentObjectRenderer->lastTypoLinkLD = $localContentObjectRenderer->lastTypoLinkLD;
            $this->contentObjectRenderer->lastTypoLinkUrl = $localContentObjectRenderer->lastTypoLinkUrl;
            $this->contentObjectRenderer->lastTypoLinkTarget = $localContentObjectRenderer->lastTypoLinkTarget;

            // nasty workaround so typolink stops putting a link together, there is a link already built
            throw new UnableToLinkException('', 1491130170, null, $link);
        }

        parent::build($linkDetails, $linkText, $target, $conf);
    }

    protected function getIndexForEventUid($table, $uid): int
    {
        $indexRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(IndexRepository::class);
        $register = Register::getRegister();

        $event = null;
        foreach ($register as $key => $value) {
            if ($value['tableName'] === $table) {
                $repositoryName = ClassNamingUtility::translateModelNameToRepositoryName($value['modelName']);
                if (\class_exists($repositoryName)) {
                    $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
                    $repository = $objectManager->get($repositoryName);
                    $event = $repository->findByUid($uid);
                }
            }
        }

        if (!($event instanceof DomainObjectInterface)) {
            return 0;
        }

        $fetchEvent = $indexRepository->findByEventTraversing($event, true, false, 1)->toArray();
        if (\count($fetchEvent) <= 0) {
            $fetchEvent = $indexRepository->findByEventTraversing($event, false, true, 1, QueryInterface::ORDER_DESCENDING)->toArray();
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
            $tables = \array_map(function ($config) {
                return $config['tableName'];
            }, GeneralUtility::makeInstance(Register::class)->getRegister());
        }

        return $tables;
    }
}
