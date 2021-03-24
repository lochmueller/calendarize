<?php

/**
 * BackendController.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Controller;

use HDNET\Calendarize\Domain\Model\Request\OptionRequest;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Messaging\FlashMessage;

/**
 * BackendController.
 */
class BackendController extends AbstractController
{
    /**
     * Basic backend list.
     */
    public function listAction()
    {
        $this->settings['timeFormat'] = 'H:i';
        $this->settings['dateFormat'] = 'd.m.Y';

        $options = $this->getOptions();
        $typeLocations = $this->getDifferentTypesAndLocations();

        $pids = $this->getPids($typeLocations);
        if ($pids) {
            $indices = $this->indexRepository->findAllForBackend($options, $pids);
        } else {
            $indices = [];
        }

        $this->view->assignMultiple([
            'indices' => $indices,
            'typeLocations' => $typeLocations,
            'pids' => $this->getPageTitles($pids),
            'settings' => $this->settings,
            'options' => $options,
        ]);
    }

    /**
     * Option action.
     *
     * @param \HDNET\Calendarize\Domain\Model\Request\OptionRequest $options
     */
    public function optionAction(OptionRequest $options)
    {
        $GLOBALS['BE_USER']->setAndSaveSessionData('calendarize_be', serialize($options));
        $this->addFlashMessage('Options saved', '', FlashMessage::OK, true);
        $this->forward('list');
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
    protected function getOptions()
    {
        try {
            $info = $GLOBALS['BE_USER']->getSessionData('calendarize_be');
            $object = @unserialize((string)$info);
            if ($object instanceof OptionRequest) {
                return $object;
            }

            return new OptionRequest();
        } catch (\Exception $exception) {
            return new OptionRequest();
        }
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
            $pageId = $entry['pid'];
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
