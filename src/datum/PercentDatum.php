<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

/**
 * contains a number between (0, 1)
 *
 * @author j
 */
class PercentDatum extends DoubleDatum
{

	public function hasMaximumValue()
	{
		return true;
	}

	public function hasMinimumValue()
	{
		return true;
	}

	public function getMaximumValue()
	{
		return 1.0;
	}

	public function getMinimumValue()
	{
		return 0;
	}
}
