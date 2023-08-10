<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

class UnsignedIntegerDatum extends IntegerDatum
{

	public static function validateStatic($value): int
	{
		return parent::validateStatic($value) === SUCCESS ? ($value >= 0 ? SUCCESS : FAILURE) : FAILURE;
	}

	public function isUnsigned()
	{
		return true;
	}

	public function setUnsigned($value)
	{
		return $value;
	}

	public function hasMinimumValue()
	{
		return true;
	}

	public function getMinimumValue()
	{
		if (! isset($this->minimumValue)) {
			return 0;
		}
		return parent::getMinimumValue();
	}
}
