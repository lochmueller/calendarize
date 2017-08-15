<?php
/**
 * EventLinkHandler
 *
 */
namespace HDNET\Calendarize\LinkHandling;

use TYPO3\CMS\Core\LinkHandling\LinkHandlingInterface;

/**
 * EventLinkHandler
 */
class EventLinkHandler implements LinkHandlingInterface
{

    /**
     * The Base URN for this link handling to act on
     *
     * @var string
     */
    protected $baseUrn = 't3://event';

    /**
     * Returns all valid parameters for linking to a TYPO3 page as a string
     *
     * @param array $parameters
     * @return string
     */
    public function asString(array $parameters): string
    {
        $urn = $this->baseUrn;
        if (isset($parameters['pagealias']) && $parameters['pagealias'] !== 'current') {
            $urn .= '?alias=' . $parameters['pagealias'];
        } else {
            $urn .= '?uid=' . $parameters['pageuid'];
        }
        $urn = rtrim($urn, ':');
        if (!empty($parameters['pagetype'])) {
            $urn .= '&type=' . $parameters['pagetype'];
        }
        if (!empty($parameters['parameters'])) {
            $urn .= '&' . ltrim($parameters['parameters'], '?&');
        }
        if (!empty($parameters['fragment'])) {
            $urn .= '#' . $parameters['fragment'];
        }

        return $urn;
    }

    /**
     * Returns all relevant information built in the link to a page (see asString())
     *
     * @param array $data
     * @return array
     */
    public function resolveHandlerData(array $data): array
    {
        $result = [];
        if (isset($data['uid'])) {
            $result['pageuid'] = $data['uid'];
            unset($data['uid']);
        }
        if (isset($data['alias'])) {
            $result['pagealias'] = $data['alias'];
            unset($data['alias']);
        }
        if (isset($data['type'])) {
            $result['pagetype'] = $data['type'];
            unset($data['type']);
        }
        if (!empty($data)) {
            $result['parameters'] = http_build_query($data);
        }
        if (empty($result)) {
            $result['pageuid'] = 'current';
        }

        return $result;
    }
}
