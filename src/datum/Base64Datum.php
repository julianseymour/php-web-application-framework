<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\is_base64;
use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;

/**
 * A datum that is converted to base64 format prior to storage so it doesn't fuck up the mysql command line console
 *
 * @author j
 */
class Base64Datum extends TextDatum implements StaticElementClassInterface
{

	/*
	 * public function __construct($name=null){
	 * parent::__construct($name);
	 * $this->setElementClass(HiddenInput::class);
	 * }
	 */
	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string
	{
		return HiddenInput::class;
	}

	public function getUrlEncodedValue()
	{
		return $this->getDatabaseEncodedValue();
	}

	public static function getDatabaseEncodedValueStatic($value)
	{
		$p = parent::getDatabaseEncodedValueStatic($value);
		$value = base64_encode($p);
		return $value;
	}

	public function parseValueFromSuperglobalArray($value)
	{
		$f = __METHOD__; //Base64Datum::getShortClass()."(".static::getShortClass().")->parseValueFromSuperglobalArray()";
		if (! is_base64($value)) {
			Debug::error("{$f} value is not base 64");
		}
		return base64_decode($value);
	}

	public function parseValueFromQueryResult($v)
	{
		return base64_decode($v);
	}
}
