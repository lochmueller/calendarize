<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Widgets\DataProvider;

use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Domain\Model\Request\OptionRequest;
use HDNET\Calendarize\Domain\Repository\IndexRepository;
use HDNET\Calendarize\Utility\DateTimeUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Dashboard\Widgets\ListDataProviderInterface;
use TYPO3Fluid\Fluid\View\ViewInterface;

class NextEventsDataProvider implements ListDataProviderInterface
{
    public function __construct(
        protected IndexRepository $indexRepository,
        protected ViewFactoryInterface $viewFactory,
    ) {}

    public function getItems(): array
    {
        $options = new OptionRequest();
        $options->setStartDate(DateTimeUtility::getNow());

        $query = $this->indexRepository->findAllForBackend($options)->getQuery();
        $query->setLimit(15);
        $indices = $query->execute()->toArray();

        return array_map(function (Index $index) {
            try {
                $viewFactoryData = new ViewFactoryData(
                    [],
                    [
                        'EXT:calendarize/Resources/Private/Partials/',
                        'EXT:calendarize_premium/Resources/Private/Partials/',
                    ],
                    [],
                );

                /** @var ViewInterface $view */
                $view = $this->viewFactory->create($viewFactoryData);

                $titlePartial = $index->getConfiguration()['partialIdentifier'] . '/Title';

                return $view->renderPartial($titlePartial, null, ['index' => $index]);
            } catch (\Exception $exception) {
                return $exception->getMessage();
            }
        }, $indices);
    }
}
