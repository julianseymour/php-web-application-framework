<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\is_base64;
use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\input\HiddenInput;

/**
 * A datum that is converted to base64 format prior to storage so it doesn't break formatting in the mysql command line console
 *
 * @author j
 */
class Base64Datum extends TextDatum implements StaticElementClassInterface{

	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string{
		return HiddenInput::class;
	}

	public function getUrlEncodedValue(){
		return $this->getDatabaseEncodedValue();
	}

	public static function getDatabaseEncodedValueStatic($value){
		$p = parent::getDatabaseEncodedValueStatic($value);
		$value = base64_encode($p);
		return $value;
	}

	public function parseValueFromSuperglobalArray($value){
		$f = __METHOD__;
		if(!is_base64($value)){
			Debug::error("{$f} value is not base 64");
		}
		return base64_decode($value);
	}

	public function parseValueFromQueryResult($v){
		if($v === null){
			return null;
		}
		return base64_decode($v);
	}
}
