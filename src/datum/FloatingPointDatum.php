<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\common\PrecisionTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class FloatingPointDatum extends AbstractNumericDatum{

	use PrecisionTrait;
	
	protected $scaleValue;

	public function cast($v){
		if(is_string($v) && $this->hasApoptoticSignal() && $this->getApoptoticSignal() === $v){
			return $v;
		}
		return floatval($v);
	}
	
	public function setScale($value){
		$f = __METHOD__;
		if(!is_int($value)){
			Debug::error("{$f} input parameter must be a positive integer");
		}elseif($value < 0){
			Debug::error("{$f} input parameter must be positive");
		}elseif($this->hasScale()){
			$this->release($this->scaleValue);
		}
		return $this->scaleValue = $this->claim($value);
	}

	public function hasScale():bool{
		return isset($this->scaleValue);
	}

	public function getScale(){
		$f = __METHOD__;
		if(!$this->hasScale()){
			Debug::error("{$f} scale is undefined");
		}
		return $this->scaleValue;
	}

	public function parseValueFromSuperglobalArray($value){
		return static::parseString($value);
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

	public static function validateStatic($value): int{
		$f = __METHOD__;
		$print = false;
		if($print){
			$type = gettype($value);
			Debug::print("{$f} entered for {$type} value \"{$value}\"");
		}
		if(is_double($value)){
			if($print){
				Debug::print("{$f} {$type} {$value} is a double");
			}
			return SUCCESS;
		}elseif(is_float($value)){
			if($print){
				Debug::print("{$f} {$type} {$value} is a float");
			}
			return SUCCESS;
		}elseif(is_int($value)){
			if($print){
				Debug::print("{$f} {$type} {$value} is an integer");
			}
			return SUCCESS;
		}elseif($print){
			Debug::print("{$f} {$type} {$value} is none of the above");
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
		if($this->hasPrecision() && $this->getPrecision() > 24){
			$string = "double";
		}else{
			$string = "float";
		}
		return $string;
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->precision, $deallocate);
		$this->release($this->scaleValue, $deallocate);
	}
}
