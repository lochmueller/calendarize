<?php

declare(strict_types=1);

namespace HDNET\Calendarize\DataProcessing;

use HDNET\Calendarize\Service\IndexerService;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

/**
 * Some of this source code is based on the Typo3 extension news, which can be found under
 * https://github.com/georgringer/news/blob/ade53b52fbcb35423b2359af08a6c2650f50560e/Classes/DataProcessing/DisableLanguageMenuProcessor.php.
 * The project is licensed under GNU General Public License 2.
 */

/**
 * Disable language item on a detail page if the event is not translated.
 *
 * 21 = HDNET\Calendarize\DataProcessing\DisableLanguageMenuProcessor
 * 21.menus = languageNavigation
 */
class DisableLanguageMenuProcessor implements DataProcessorInterface
{
    public const TABLE = IndexerService::TABLE_NAME;

    /**
     * Process content object data.
     *
     * @param ContentObjectRenderer $cObj                       The data of the content element or page
     * @param array                 $contentObjectConfiguration The configuration of Content Object
     * @param array                 $processorConfiguration     The configuration of this processor
     * @param array                 $processedData              Key/value store of processed data
     *                                                          (e.g. to be passed to a Fluid View)
     *
     * @return array the processed data as key/value store
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData,
    ): array {
        if (!$processorConfiguration['menus']) {
            return $processedData;
        }
        $indexId = $this->getIndexId($cObj->getRequest());
        if (0 === $indexId) {
            return $processedData;
        }
        $availableLanguages = $this->getAvailableLanguages($indexId);
        if (\in_array(-1, $availableLanguages)) {
            // Skip check if languages = [ALL] is selected
            return $processedData;
        }

        $menus = GeneralUtility::trimExplode(',', $processorConfiguration['menus'], true);
        foreach ($menus as $menu) {
            if (isset($processedData[$menu])) {
                $this->handleMenu($processedData[$menu], $availableLanguages);
            }
        }

        return $processedData;
    }

    protected function getAvailableLanguages(int $indexId): array
    {
        $languageField = $GLOBALS['TCA'][self::TABLE]['ctrl']['languageField'] ?? '';
        $transOrigPointerField = $GLOBALS['TCA'][self::TABLE]['ctrl']['transOrigPointerField'] ?? '';

        $fields = 'uid,' . $languageField . ',' . $transOrigPointerField;

        // The live index is marked as deleted in the WS with the delete-placeholder.
        // In this case it does not make sense to use BackendUtility::getLiveVersionOfRecord as a fist step here,
        // since there is never a live version.
        $row = BackendUtility::getRecord(self::TABLE, $indexId, $fields);
        $uid = $row['uid'];

        // if the current page is translated, get the parent id
        if ((int)$row[$languageField] && $row[$transOrigPointerField]) {
            $uid = $row[$transOrigPointerField];
        }

        if (-1 === $row[$languageField]) {
            // Skip second query if languages = [ALL] is selected
            return [-1];
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE);
        $queryBuilder->getRestrictions()->add(
            GeneralUtility::makeInstance(WorkspaceRestriction::class),
        );

        $result = $queryBuilder
            ->select($languageField)
            ->from(self::TABLE)
            ->where(
                $queryBuilder->expr()->or(
                    // Current language of the record
                    $queryBuilder->expr()->eq(
                        'uid',
                        $queryBuilder->createNamedParameter($indexId, Connection::PARAM_INT),
                    ),
                    // Translated versions of the records (found by l10n_parent)
                    $queryBuilder->expr()->eq(
                        $transOrigPointerField,
                        $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT),
                    ),
                ),
            )
            ->executeQuery();

        return $result->fetchFirstColumn();
    }

    protected function handleMenu(array &$menu, array $availableLanguages): void
    {
        foreach ($menu as &$item) {
            if (!$item['available']) {
                continue;
            }
            try {
                $availability = \in_array((int)$item['languageId'], $availableLanguages);
                if (!$availability) {
                    $item['available'] = false;
                    $item['availableReason'] = 'calendarize';
                }
            } catch (\Exception $exception) {
            }
        }
    }

    protected function getIndexId(ServerRequestInterface $request): int
    {
        $indexId = 0;
        /** @var PageArguments $pageArguments */
        $pageArguments = $request->getAttribute('routing');
        if (isset($pageArguments->getRouteArguments()['tx_calendarize_calendar']['index'])) {
            $indexId = (int)$pageArguments->getRouteArguments()['tx_calendarize_calendar']['index'];
        } elseif (isset($request->getQueryParams()['tx_calendarize_calendar']['index'])) {
            $indexId = (int)$request->getQueryParams()['tx_calendarize_calendar']['index'];
        }

        return $indexId;
    }
}
