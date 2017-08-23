<?php
/**
 * AbstractUrl.
 */

namespace HDNET\Calendarize\Service\Url;

use HDNET\Calendarize\Domain\Model\Index;
use HDNET\Calendarize\Domain\Repository\IndexRepository;
use HDNET\Calendarize\Features\SpeakingUrlInterface;
use HDNET\Calendarize\Service\AbstractService;
use HDNET\Calendarize\Utility\HelperUtility;

/**
 * AbstractUrl.
 */
abstract class AbstractUrl extends AbstractService
{
    /**
     * Convert the given information.
     *
     * @param $param1
     * @param $param2
     */
    abstract public function convert($param1, $param2);

    /**
     * Build the speaking base.
     *
     * @param int $indexUid
     *
     * @return string
     */
    protected function getIndexBase($indexUid)
    {
        $indexRepository = HelperUtility::create(IndexRepository::class);
        $index = $indexRepository->findByUid((int) $indexUid);
        if (!($index instanceof Index)) {
            return 'idx-' . $indexUid;
        }

        $originalObject = $index->getOriginalObject();
        if (!($originalObject instanceof SpeakingUrlInterface)) {
            return 'idx-' . $indexUid;
        }

        $base = $originalObject->getRealUrlAliasBase();
        $datePart = $index->isAllDay() ? 'Y-m-d' : 'Y-m-d-h-i';

        return $base . '-' . $index->getStartDateComplete()
            ->format($datePart);
    }
}
