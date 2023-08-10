<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

class SerialNumberDatum extends UnsignedIntegerDatum
{

	public function __construct($name)
	{
		parent::__construct($name, 64);
	}

	public function getColumnTypeString(): string
	{
		return "serial";
	}
}
