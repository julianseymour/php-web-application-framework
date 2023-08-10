<?php
namespace JulianSeymour\PHPWebApplicationFramework\json;

use function JulianSeymour\PHPWebApplicationFramework\f;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

class JsonDatum extends TextDatum
{

	public function getColumnTypeString(): string
	{
		return "JSON";
	}

	public static function getDatabaseEncodedValueStatic($value)
	{
		if (empty($value)) {
			return $value;
		}
		return json_encode($value);
	}

	public function parseValueFromQueryResult($value)
	{
		$f = __METHOD__; //JsonDatum::getShortClass()."(".static::getShortClass().")->parseValueFromQueryResult()";
		if (empty($value)) {
			return $value;
		}
		$decoded = json_decode($value, true);
		if (! is_array($decoded)) {
			Debug::warning("{$f} JSON decoded value is not an array");
			Debug::print($decoded);
			Debug::printStackTrace();
		}
		// Debug::print("{$f} returning normally");
		return $decoded;
	}

	/*
	 * public function setKeyValue($key, $value){
	 * if(!$this->hasKeyValue($key) || $this->getKeyValue($key) !== $value){
	 * $this->setUpdateFlag(true);
	 * }
	 * return $this->value[$key] = $value;
	 * }
	 *
	 * public function getKeyValue($key){
	 * $f = __METHOD__; //JsonDatum::getShortClass()."(".static::getShortClass().")->getKeyValue()";
	 * if(!$this->hasKeyValue($key)){
	 * Debug::error("{$f} value for key \"{$key}\" is undefined");
	 * }
	 * return $this->value[$key];
	 * }
	 *
	 * public function hasKeyValue($key):bool{
	 * return $this->hasValue() && is_array($this->value) && array_key_exists($key, $this->value);
	 * }
	 *
	 * public function setOriginalKeyValue($key, $value){
	 * $f = __METHOD__; //JsonDatum::getShortClass()."(".static::getShortClass().")->setOriginalKeyValue()";
	 * if(is_int($key) || $key == "0"){
	 * Debug::error("{$f} key is undefined");
	 * }
	 * $this->setUpdateFlag(true);
	 * return $this->originalValue[$key] = $value;
	 * }
	 *
	 * public function getOriginalKeyValue($key){
	 * $f = __METHOD__; //JsonDatum::getShortClass()."(".static::getShortClass().")->getOriginalKeyValue()";
	 * if(!$this->hasOriginalKeyValue($key)){
	 * Debug::error("{$f} original value for key \"{$key}\" is undefined");
	 * }
	 * return $this->originalValue[$key];
	 * }
	 *
	 * public function hasOriginalKeyValue($key):bool{
	 * return $this->hasOriginalValue()
	 * && is_array($this->originalValue)
	 * && array_key_exists($key, $this->originalValue);
	 * }
	 */
	public function getHumanReadableValue()
	{
		ErrorMessage::unimplemented(f());
	}

	/*
	 * public function setSubkeyValue($key, $subkey, $value){
	 * $this->setUpdateFlag(true);
	 * return $this->value[$key][$subkey] = $value;
	 * }
	 */
}
