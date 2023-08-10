<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\input\NumberInput;

/**
 * Datum that contains a number value.
 * If you are looking for the fixed-precision SQL Numeric type see DecimalDatum.
 *
 * @author j
 *        
 */
abstract class AbstractNumericDatum extends Datum implements StaticElementClassInterface
{

	protected $maximumValue;

	protected $minimumValue;

	/*
	 * public function __construct($name){
	 * $f = __METHOD__; //AbstractNumericDatum::getShortClass()."(".static::getShortClass().")->__construct()";
	 * $count = Debug::getFunctionNestingLevel();
	 * if($count >= 224){
	 * Debug::error("{$f} function nesting level is {$count}");
	 * }
	 * parent::__construct($name);
	 * $this->setElementClass(NumberInput::class);
	 * }
	 */
	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string
	{
		return NumberInput::class;
	}

	public function hasMaximumValue()
	{
		return isset($this->maximumValue);
	}

	public function hasMinimumValue()
	{
		return isset($this->minimumValue);
	}

	public function getMaxmimumValue()
	{
		return $this->maximumValue;
	}

	public function getMinimumValue()
	{
		return $this->minimumValue;
	}

	public function setMaximumValue($max)
	{
		return $this->maximumValue = $max;
	}

	public function setMinimumValue($min)
	{
		return $this->minimumValue = $min;
	}

	public function addValue($value)
	{
		return $this->setValue($this->getValue() + $value);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->minimumValue);
		unset($this->maximumValue);
	}
}
