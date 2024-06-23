<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\input\NumberInput;

/**
 * Datum that contains a number value.
 * If you are looking for the fixed-precision SQL Numeric type see DecimalDatum.
 *
 * @author j
 *        
 */
abstract class AbstractNumericDatum extends Datum implements StaticElementClassInterface{

	protected $maximumValue;

	protected $minimumValue;

	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string{
		return NumberInput::class;
	}

	public function hasMaximumValue():bool{
		return isset($this->maximumValue);
	}

	public function hasMinimumValue():bool{
		return isset($this->minimumValue);
	}

	public function getMaxmimumValue(){
		return $this->maximumValue;
	}

	public function getMinimumValue(){
		return $this->minimumValue;
	}

	public function setMaximumValue($max){
		if($this->hasMaximumValue()){
			$this->release($this->maximumValue);
		}
		return $this->maximumValue = $this->claim($max);
	}

	public function setMinimumValue($min){
		if($this->hasMinimumValue()){
			$this->release($this->minimumValue);
		}
		return $this->minimumValue = $this->claim($min);
	}

	public function addValue($value){
		return $this->setValue($this->getValue() + $value);
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->minimumValue, $deallocate);
		$this->release($this->maximumValue, $deallocate);
	}
}
