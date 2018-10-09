<?php

namespace JMBTechnologyLimited\ICalDissect;

/**
 *
 * @link https://github.com/JMB-Technology-Limited/ICalDissect
 * @license https://raw.github.com/JMB-Technology-Limited/ICalDissect/master/LICENSE.txt 3-clause BSD
 * @copyright (c) 2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class ICalExDate
{

	protected $properties;
	protected $values;

	function __construct($values, $properties)
	{
		$this->properties = $properties;
		$this->values = $values;
	}

	/**
	 * @return mixed
	 */
	public function getProperties()
	{
		return $this->properties;
	}

	/**
	 * @return mixed
	 */
	public function getValues()
	{
		return $this->values;
	}



}
