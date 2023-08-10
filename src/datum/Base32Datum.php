<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

class Base32Datum extends TextDatum
{

	public function getUrlEncodedValue()
	{
		return $this->getValue();
	}

	public function getHumanReadableValue()
	{
		return $this->getValue();
	}

	public function getHumanWritableValue()
	{
		return $this->getValue();
	}
}
