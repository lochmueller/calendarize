<?php

/**
 * BackendController.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Controller;

use HDNET\Calendarize\Domain\Model\Request\OptionRequest;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;

/**
 * BackendController.
 */
class BackendController extends AbstractController
{
    const OPTIONS_KEY = 'calendarize_be';

    /**
     * Basic backend list.
     */
    public function listAction(OptionRequest $options = null, int $currentPage = 1)
    {
        $this->settings['timeFormat'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'] ?? 'H:i';
        $this->settings['dateFormat'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?? 'd-m-y';

        if (null === $options) {
            $options = $this->getOptions();
        } else {
            $this->setOptions($options);
        }

        $typeLocations = $this->getDifferentTypesAndLocations();

        $pids = $this->getPids($typeLocations);
        if ($pids) {
            $indices = $this->indexRepository->findAllForBackend($options, $pids);
            $paginator = new QueryResultPaginator($indices, $currentPage, 50);
        } else {
            $indices = [];
            $paginator = new ArrayPaginator($indices, $currentPage, 50);
        }
        $pagination = new SimplePagination($paginator);

        $this->view->assignMultiple([
            'indices' => $indices,
            'typeLocations' => $typeLocations,
            'pids' => $this->getPageTitles($pids),
            'settings' => $this->settings,
            'options' => $options,
            'paginator' => $paginator,
            'pagination' => $pagination,
            'totalAmount' => \count($indices),
        ]);
    }

    protected function getPids(array $typeLocations)
    {
        $pids = [];
        foreach ($typeLocations as $locations) {
            $pids = array_merge($pids, array_keys($locations));
        }
        $pids = array_unique($pids);

        return array_combine($pids, $pids);
    }

    protected function getPageTitles(array $pids): array
    {
        $results = [];
        foreach ($pids as $pageId) {
            $row = BackendUtility::getRecord('pages', $pageId);
            if ($row) {
                $title = BackendUtility::getRecordTitle('pages', $row);
                $results[$pageId] = '"' . $title . '" (#' . $pageId . ')';
                continue;
            }
            // fallback to uid
            $results[$pageId] = '#' . $pageId;
        }

        return $results;
    }

    /**
     * Get option request.
     *
     * @return OptionRequest
     */
    protected function getOptions(): OptionRequest
    {
        try {
            $info = $GLOBALS['BE_USER']->getSessionData(self::OPTIONS_KEY) ?? '';
            if ('' !== $info) {
                $object = @unserialize($info, ['allowed_classes' => [OptionRequest::class]]);
                if ($object instanceof OptionRequest) {
                    return $object;
                }
            }
        } catch (\Exception $exception) {
        }

        return new OptionRequest();
    }

    /**
     * Persists options data.
     *
     * @param OptionRequest $options
     */
    protected function setOptions(OptionRequest $options)
    {
        $GLOBALS['BE_USER']->setAndSaveSessionData(self::OPTIONS_KEY, serialize($options));
    }

    /**
     * Get the differnet locations for new entries.
     *
     * @return array
     */
    protected function getDifferentTypesAndLocations()
    {
        /**
         * @var array<int>
         */
        $mountPoints = $this->getAllowedDbMounts();

        $typeLocations = [];
        foreach ($this->indexRepository->findDifferentTypesAndLocations() as $entry) {
            $pageId = (int)$entry['pid'];
            if ($this->isPageAllowed($pageId, $mountPoints)) {
                $typeLocations[$entry['foreign_table']][$pageId] = $entry['unique_register_key'];
            }
        }

        return $typeLocations;
    }

    /**
     * Check if access to page is allowed for current user.
     *
     * @param int   $pageId
     * @param array $mountPoints
     *
     * @return bool
     */
    protected function isPageAllowed(int $pageId, array $mountPoints): bool
    {
        if ($this->getBackendUser()->isAdmin()) {
            return true;
        }

        // check if any mountpoint is in rootline
        $rootline = BackendUtility::BEgetRootLine($pageId, '');
        foreach ($rootline as $entry) {
            if (\in_array((int)$entry['uid'], $mountPoints)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get allowed mountpoints. Returns temporary mountpoint when temporary mountpoint is used.
     *
     * copied from core TreeController
     *
     * @return int[]
     */
    protected function getAllowedDbMounts(): array
    {
        $dbMounts = (int)($this->getBackendUser()->uc['pageTree_temporaryMountPoint'] ?? 0);
        if (!$dbMounts) {
            $dbMounts = array_map('intval', $this->getBackendUser()->returnWebmounts());

            return array_unique($dbMounts);
        }

        return [$dbMounts];
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
