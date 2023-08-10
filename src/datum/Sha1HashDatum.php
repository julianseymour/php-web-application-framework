<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\is_sha1;

class Sha1HashDatum extends CharDatum
{

	public function __construct($name)
	{
		parent::__construct($name, 40);
	}

	public static function validateStatic($value): int
	{
		return is_sha1($value) ? SUCCESS : FAILURE;
	}

	public function getUrlEncodedValue()
	{
		return $this->getValue();
	}

	public function getHumanWritableValue()
	{
		return $this->getValue();
	}

	public function getHumanReadableValue()
	{
		return $this->getValue();
	}
}
