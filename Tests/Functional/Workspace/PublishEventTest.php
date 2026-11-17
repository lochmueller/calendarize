<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Functional\Workspace;

use HDNET\Calendarize\Service\IndexerService;
use HDNET\Calendarize\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\WorkspaceAspect;
use TYPO3\CMS\Core\Database\ReferenceIndex;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Publishing or discarding an event in a workspace must update the index of the live
 * record and remove the index records of the workspace.
 *
 * @see https://github.com/lochmueller/calendarize/issues/662
 */
class PublishEventTest extends AbstractFunctionalTestCase
{
    protected const EVENT_TABLE = 'tx_calendarize_domain_model_event';
    protected const CONFIGURATION_TABLE = 'tx_calendarize_domain_model_configuration';
    protected const WORKSPACE_ID = 91;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/tx_calendarize_domain_model_configuration.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/tx_calendarize_domain_model_event.csv');

        GeneralUtility::makeInstance(IndexerService::class)->reindexAll();
        // The workspace dependency resolver uses the reference index to find the
        // configurations (inline children) that belong to a published event.
        GeneralUtility::makeInstance(ReferenceIndex::class)->updateIndex(false);

        $this->setWorkspace(self::WORKSPACE_ID);
    }

    public function testPublishingAnEventWithAChangedConfigurationUpdatesTheLiveIndex(): void
    {
        $this->modifyInWorkspace(
            [self::EVENT_TABLE => [1 => ['title' => 'Live Event (changed)']]]
            + [self::CONFIGURATION_TABLE => [1 => ['start_date' => '2026-04-13']]],
        );

        $this->publish(1);

        self::assertSame(
            ['1 @ 2026-04-13 | ws 0 | state 0'],
            $this->getIndices(),
        );
    }

    public function testPublishingAnEventWithoutAChangedConfigurationKeepsTheLiveIndex(): void
    {
        $this->modifyInWorkspace([self::EVENT_TABLE => [1 => ['title' => 'Live Event (changed)']]]);

        $this->publish(1);

        self::assertSame(
            ['1 @ 2026-04-01 | ws 0 | state 0'],
            $this->getIndices(),
        );
    }

    public function testDiscardingAnEventRemovesTheWorkspaceIndices(): void
    {
        $this->modifyInWorkspace(
            [self::EVENT_TABLE => [1 => ['title' => 'Live Event (changed)']]]
            + [self::CONFIGURATION_TABLE => [1 => ['start_date' => '2026-04-13']]],
        );

        $this->discard($this->getVersionId(1));

        self::assertSame(
            ['1 @ 2026-04-01 | ws 0 | state 0'],
            $this->getIndices(),
        );
    }

    public function testPublishingAWorkspaceOnlyEventCreatesTheLiveIndex(): void
    {
        // A record that only exists in the workspace is published with itself
        $this->publish(10, 10);

        self::assertSame(
            ['10 @ 2026-05-05 | ws 0 | state 0'],
            $this->getIndices(10),
        );
    }

    /**
     * Modify the live records in the workspace (creates the workspace versions).
     */
    protected function modifyInWorkspace(array $dataMap): void
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start($dataMap, [], $GLOBALS['BE_USER']);
        $dataHandler->process_datamap();
        self::assertSame([], $dataHandler->errorLog);
    }

    /**
     * Publish a single record, like the "Publish version of the record" action of the
     * workspace module does.
     */
    protected function publish(int $liveUid, ?int $versionUid = null): void
    {
        $this->processCommand($liveUid, [
            'action' => 'publish',
            'swapWith' => $versionUid ?? $this->getVersionId($liveUid),
        ]);
    }

    /**
     * Discard the workspace version of a record.
     */
    protected function discard(int $versionUid): void
    {
        $this->processCommand($versionUid, ['action' => 'clearWSID']);
    }

    protected function processCommand(int $uid, array $version): void
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([], [self::EVENT_TABLE => [$uid => ['version' => $version]]], $GLOBALS['BE_USER']);
        $dataHandler->process_cmdmap();
        self::assertSame([], $dataHandler->errorLog);
    }

    protected function getVersionId(int $liveUid): int
    {
        $row = $this->getConnectionPool()
            ->getConnectionForTable(self::EVENT_TABLE)
            ->select(['uid'], self::EVENT_TABLE, ['t3ver_oid' => $liveUid, 't3ver_wsid' => self::WORKSPACE_ID])
            ->fetchAssociative();

        return (int)($row['uid'] ?? 0);
    }

    /**
     * @return string[] all index records of the given event, live and workspace ones
     */
    protected function getIndices(int $foreignUid = 1): array
    {
        $rows = $this->getConnectionPool()
            ->getConnectionForTable(IndexerService::TABLE_NAME)
            ->select(['*'], IndexerService::TABLE_NAME, ['foreign_uid' => $foreignUid, 'foreign_table' => self::EVENT_TABLE])
            ->fetchAllAssociative();

        $result = [];
        foreach ($rows as $row) {
            $result[] = sprintf(
                '%d @ %s | ws %d | state %d',
                $row['foreign_uid'],
                $row['start_date'],
                $row['t3ver_wsid'],
                $row['t3ver_state'],
            );
        }
        sort($result);

        return $result;
    }

    protected function setWorkspace(int $workspaceId): void
    {
        $GLOBALS['BE_USER']->workspace = $workspaceId;
        GeneralUtility::makeInstance(Context::class)->setAspect('workspace', new WorkspaceAspect($workspaceId));
    }
}
