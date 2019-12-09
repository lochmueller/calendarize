<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Typolink;

/**
 * DatabaseRecordLinkBuilder.
 */
class DatabaseRecordLinkBuilder extends \TYPO3\CMS\Frontend\Typolink\DatabaseRecordLinkBuilder
{
    public function build(array &$linkDetails, string $linkText, string $target, array $conf): array
    {
        if (isset($linkDetails['identifier']) && 'tx_calendarize_domain_model_event' === $linkDetails['identifier']) {
            $eventId = $linkDetails['uid'];

            // @todo handle Link Building
        }

        parent::build($linkDetails, $linkText, $target, $conf);
    }
}
