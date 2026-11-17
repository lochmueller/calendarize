<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Tests\Functional\Domain\Repository;

use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Domain\Model\Request\OptionRequest;
use HDNET\Calendarize\Domain\Repository\IndexRepository;
use HDNET\Calendarize\Service\IndexerService;
use HDNET\Calendarize\Tests\Functional\AbstractFunctionalTestCase;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\WorkspaceAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The backend module (and the dashboard widget) must only show the index records
 * of the current workspace.
 *
 * @see https://github.com/lochmueller/calendarize/issues/659
 */
class BackendIndexWorkspaceTest extends AbstractFunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../../Indexing/Fixtures/tx_calendarize_domain_model_configuration.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Indexing/Fixtures/tx_calendarize_domain_model_event.csv');

        GeneralUtility::makeInstance(IndexerService::class)->reindexAll();
    }

    public function testLiveViewContainsNoWorkspaceIndices(): void
    {
        self::assertSame(
            [
                '4 @ 2026-01-01',
                '5 @ 2026-01-12',
                '6 @ 2026-02-01',
                // Live version of the event, that is modified in workspace 91
                '1 @ 2026-04-01',
                // The index of the workspace only event (3) and the workspace
                // version of event 1 (2026-04-13) must not be visible here
            ],
            $this->findAllForBackend(),
        );
    }

    public function testWorkspaceViewContainsTheWorkspaceIndices(): void
    {
        $this->setWorkspace(91);

        $indices = $this->findAllForBackend();

        // Workspace version of event 1 instead of the live version (2026-04-01)
        self::assertContains('1 @ 2026-04-13', $indices);
        self::assertNotContains('1 @ 2026-04-01', $indices);
        // Event that only exists in the workspace
        self::assertContains('3 @ 2026-04-26', $indices);
        // Events that are not touched in the workspace
        self::assertContains('4 @ 2026-01-01', $indices);
        self::assertContains('5 @ 2026-01-12', $indices);
        // Event that is deleted in the workspace
        self::assertNotContains('6 @ 2026-02-01', $indices);
    }

    public function testOtherWorkspacesAreNotVisibleInAWorkspace(): void
    {
        $this->setWorkspace(90);

        $indices = $this->findAllForBackend();

        self::assertNotContains('1 @ 2026-04-13', $indices);
        self::assertNotContains('3 @ 2026-04-26', $indices);
        self::assertContains('1 @ 2026-04-01', $indices);
    }

    protected function setWorkspace(int $workspaceId): void
    {
        $GLOBALS['BE_USER']->workspace = $workspaceId;
        GeneralUtility::makeInstance(Context::class)->setAspect('workspace', new WorkspaceAspect($workspaceId));
    }

    /**
     * @return string[] "foreignUid @ startDate" of all index records of the backend module
     */
    protected function findAllForBackend(): array
    {
        $repository = GeneralUtility::makeInstance(IndexRepository::class);

        $result = [];
        /** @var Index $index */
        foreach ($repository->findAllForBackend(new OptionRequest()) as $index) {
            $result[] = $index->getForeignUid() . ' @ ' . $index->getStartDate()->format('Y-m-d');
        }

        return $result;
    }
}
