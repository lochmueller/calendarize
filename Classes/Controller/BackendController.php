<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Controller;

use HDNET\Calendarize\Domain\Model\Request\OptionRequest;
use HDNET\Calendarize\Register;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\Controller;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;

/**
 * BackendController.
 */
#[Controller]
class BackendController extends AbstractController
{
    protected LanguageService $languageService;

    private const PATH_CORE_LOCALLANG = 'LLL:EXT:core/Resources/Private/Language/locallang_common.xlf';

    public const OPTIONS_KEY = 'calendarize_be';

    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly LanguageServiceFactory $languageServiceFactory,
    ) {
        $this->languageService = $this->languageServiceFactory->createFromUserPreferences($this->getBackendUser());
    }

    public function initializeListAction(): void
    {
        $optionsConfiguration = $this->arguments->getArgument('options')->getPropertyMappingConfiguration();

        $optionsConfiguration->forProperty('startDate')
            ->setTypeConverterOption(
                DateTimeConverter::class,
                DateTimeConverter::CONFIGURATION_DATE_FORMAT,
                'Y-m-d',
            );
        $optionsConfiguration->forProperty('endDate')
            ->setTypeConverterOption(
                DateTimeConverter::class,
                DateTimeConverter::CONFIGURATION_DATE_FORMAT,
                'Y-m-d',
            );
    }

    /**
     * Basic backend list.
     */
    public function listAction(?OptionRequest $options = null, int $currentPage = 1): ResponseInterface
    {
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

        $view = $this->moduleTemplateFactory->create($this->request);
        $view->assignMultiple([
            'indices' => $indices,
            'typeLocations' => $typeLocations,
            'types' => $this->getTypes(),
            'pids' => $this->getPageTitles($pids),
            'settings' => $this->settings,
            'options' => $options,
            'paginator' => $paginator,
            'pagination' => $pagination,
            'totalAmount' => \count($indices),
            'filterOptions' => [
                'asc' => $this->languageService->sL(self::PATH_CORE_LOCALLANG . ':ascending') ?: 'ascending',
                'desc' => $this->languageService->sL(self::PATH_CORE_LOCALLANG . ':descending') ?: 'descending',
            ],
        ]);

        return $view->renderResponse('Backend/List');
    }

    protected function getPids(array $typeLocations): array
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
     */
    protected function getOptions(): OptionRequest
    {
        try {
            $info = $GLOBALS['BE_USER']->getSessionData(self::OPTIONS_KEY) ?? '';
            if ('' !== $info) {
                $object = @unserialize($info, ['allowed_classes' => [OptionRequest::class, \DateTime::class]]);
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
     */
    protected function setOptions(OptionRequest $options): void
    {
        $GLOBALS['BE_USER']->setAndSaveSessionData(self::OPTIONS_KEY, serialize($options));
    }

    /**
     * Get the different locations for new entries.
     */
    protected function getDifferentTypesAndLocations(): array
    {
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
     * Get the different types.
     */
    protected function getTypes(): array
    {
        return array_column(Register::getRegister(), 'title', 'uniqueRegisterKey');
    }

    /**
     * Check if access to page is allowed for current user.
     */
    protected function isPageAllowed(int $pageId, array $mountPoints): bool
    {
        if ($this->getBackendUser()->isAdmin()) {
            return true;
        }

        // check if any mountpoint is in rootline
        $rootline = BackendUtility::BEgetRootLine($pageId);
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

    protected function getBackendUser(): ?BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
