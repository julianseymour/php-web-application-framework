<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class FloatingPointDatum extends AbstractNumericDatum
{

	protected $precision;

	protected $scaleValue;

	public function setScale($value)
	{
		$f = __METHOD__; //FloatingPointDatum::getShortClass()."(".static::getShortClass().")->setScale()";
		if($value === null) {
			unset($this->scaleValue);
			return null;
		}elseif(!is_int($value)) {
			Debug::error("{$f} input parameter must be a positive integer");
		}elseif($value < 0) {
			Debug::error("{$f} input parameter must be positive");
		}
		return $this->scaleValue = $value;
	}

	public function hasScale()
	{
		return isset($this->scaleValue);
	}

	public function getScale()
	{
		$f = __METHOD__; //FloatingPointDatum::getShortClass()."(".static::getShortClass().")->getScale()";
		if(!$this->hasScale()) {
			Debug::error("{$f} scale is undefined");
		}
		return $this->scaleValue;
	}

	public function setPrecision($precision)
	{
		return $this->precision = $precision;
	}

	public function hasPrecision()
	{
		return isset($this->precision);
	}

	public function getPrecision()
	{
		$f = __METHOD__; //FloatingPointDatum::getShortClass()."(".static::getShortClass().")->getPrecision()";
		if(!$this->hasPrecision()) {
			Debug::error("{$f} precision is undefined");
		}
		return $this->precision;
	}

	public function parseValueFromSuperglobalArray($value)
	{
		return doubleval($value);
	}

	public function getHumanReadableValue(){
		return $this->getValue();
	}

	public function getHumanWritableValue(){
		return $this->getValue();
	}

	public function getConstructorParams(): ?array{
		return [
			$this->getName()
		];
	}

	public static function getTypeSpecifier():string{
		return "d";
	}

	public static function validateStatic($value): int
	{
		if(is_double($value)) {
			return SUCCESS;
		}elseif(is_float($value)) {
			return SUCCESS;
		}elseif(is_int($value)) {
			return SUCCESS;
		}
		return FAILURE;
	}

	public function parseValueFromQueryResult($raw){
		return floatval($raw);
	}

	public static function parseString(string $string){
		return floatval($string);
	}

	public function getUrlEncodedValue(){
		return $this->getValue();
	}

	public function getColumnTypeString(): string{
		if($this->hasPrecision() && $this->getPrecision() > 24) {
			$string = "double";
		}else{
			$string = "float";
		}
		return $string;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->precision);
		unset($this->scaleValue);
	}
}
