<?php

/**
 * CoolUri.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service\Url;

use Bednarik\Cooluri\Core\Functions;

/**
 * CoolUri.
 */
class CoolUri extends AbstractUrl
{
    /**
     * Convert the given information.
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
     * Convert the given information (Static)
     * https://github.com/bednee/cooluri/blob/907b298775ab9cef9a852b89de45456145829aa6/Classes/Core/Functions.php#L321
     * Why the f*** Cooluri call all the functions statical?!? *grrrr*.
     *
     * This is just a wrapper, because CoolUri handle "userfunc" other
     * than the core handle "userFunc" :(
     *
     * @param $param1
     * @param $param2
     *
     * @return string
     */
    public static function convertStatic($param1, $param2)
    {
        $object = new self();

        return $object->convert($param1, $param2);
    }

    /**
     * Generate the cooluri segment.
     *
     * @param string $xml
     * @param int    $value
     *
     * @throws \HDNET\Calendarize\Exception
     *
     * @return string
     */
    public function coolUri($xml, $value)
    {
        $alias = $this->getIndexBase((int)$value);
        $alias = $this->prepareBase($alias);
        $alias = Functions::URLize($alias);
        $alias = Functions::sanitize_title_with_dashes($alias);

        return $alias;
    }
}
