<?php
/**
 * CoolUri
 *
 * @author  Tim Lochmüller
 */

namespace HDNET\Calendarize\Service\Url;

use Bednarik\Cooluri\Core\Functions;

/**
 * CoolUri
 */
class CoolUri extends AbstractUrl
{

    /**
     * Convert the given information
     *
     * @param $param1
     * @param $param2
     *
     * @return string
     */
    public function convert($param1, $param2)
    {
        return $this->coolUri($param1, $param2);
    }

    /**
     * Generate the cooluri segment
     *
     * @param string $xml
     * @param int    $value
     *
     * @return string
     * @throws \HDNET\Calendarize\Exception
     */
    public function coolUri($xml, $value)
    {
        $alias = $this->getIndexBase((int)$value);
        $alias = Functions::URLize($alias);
        $alias = Functions::sanitize_title_with_dashes($alias);
        return $alias;
    }
}
