<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

abstract class IntegerDatum extends AbstractNumericDatum
{

	protected $bitCount;

	public function __construct($name, $bit_count)
	{
		parent::__construct($name);
		$this->setUnsigned(false);
		$this->setBitCount($bit_count);
	}

	public function cast($v)
	{
		if (is_string($v) && $this->hasApoptoticSignal() && $this->getApoptoticSignal() === $v) {
			return $v;
		}
		return intval($v);
	}

	public function getConstructorParams(): ?array
	{
		return [
			$this->getColumnName(),
			$this->getBitCount()
		];
	}

	public function getUrlEncodedValue()
	{
		return $this->getValue();
	}

	public function setBitCount($bits)
	{
		$f = __METHOD__; //IntegerDatum::getShortClass()."(".static::getShortClass().")->setBitCount()";
		if (! is_int($bits)) {
			Debug::error("{$f} received a non-integer value");
		}
		return $this->bitCount = $bits;
	}

	public function hasBitCount()
	{
		return isset($this->bitCount) && is_int($this->bitCount);
	}

	public function getHumanReadableValue()
	{
		return $this->getValue();
	}

	public function setUnsigned($unsigned)
	{
		return $this->setFlag('unsigned', $unsigned);
	}

	public function isUnsigned()
	{
		return $this->getFlag('unsigned');
	}

	public function getHumanWritableValue()
	{
		return $this->getValue();
	}

	public function setAutoIncrement($auto)
	{
		return $this->setFlag('autoIncrement', $auto);
	}

	public function getAutoIncrementFlag()
	{
		return $this->getFlag("autoIncrement");
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"autoIncrement",
			"unsigned"
		]);
	}

	public function parseValueFromSuperglobalArray($value)
	{
		$f = __METHOD__; //IntegerDatum::getShortClass()."(".static::getShortClass().")->parseValueFromSuperglobalArray()";
		try {
			if ($value === null) {
				if ($this->isNullable()) {
					return $value;
				}
				return 0;
			}
			return is_int($value) ? $value : intval($value);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function validateStatic($value): int
	{
		if (! is_int($value)) {
			return FAILURE;
		}
		return SUCCESS;
	}

	public static function getTypeSpecifier()
	{
		return 'i';
	}

	public function getBitCount()
	{
		if (! $this->hasBitCount()) {
			return 4;
		}
		return $this->bitCount;
	}

	public function getColumnTypeString(): string
	{
		$f = __METHOD__; //IntegerDatum::getShortClass()."(".static::getShortClass().")->getColumnTypeString()";
		$suffix = "";
		$prefix = "";
		$bit_count = $this->getBitCount();
		if ($bit_count < 8) {
			$prefix = "TINY";
		} else {
			$byte_count = $bit_count / 8;
			$suffix = "({$byte_count})";
			switch ($byte_count) {
				case 1:
					$prefix = "TINY";
					break;
				case 2:
					$prefix = "SMALL";
					break;
				case 3:
					$prefix = "MEDIUM";
					break;
				case 4:
					break;
				case 8:
					$prefix = "BIG";
					break;
				default:
					$name = $this->getColumnName();
					Debug::error("{$f} invalid byte count \"{$byte_count}\" for datum \"{$name}\"");
					break;
			}
		}
		return "{$prefix}INT{$suffix}";
	}

	/*
	 * public function getColumnDeclarationString(){
	 * $f = __METHOD__; //IntegerDatum::getShortClass()."(".static::getShortClass().")->getColumnDeclarationString()";
	 * try{
	 * $ret = parent::getColumnDeclarationString();
	 * if($this->isUnsigned()){
	 * $ret .= " unsigned";
	 * }
	 * if(!$this->isNullable()){
	 * $ret .= " not null";
	 * }
	 * if($this->isPrimaryKey()){
	 * $ret .= " primary key";
	 * if($this instanceof SerialNumberDatum){
	 * $ret .= " auto_increment";
	 * }
	 * }
	 * if($this->hasDefaultValue()){
	 * $default = $this->getDefaultValue();
	 * if($default === null){
	 * $default = "NULL";
	 * }elseif($default === false){
	 * $default = 0;
	 * }elseif($default === true){
	 * $default = 1;
	 * }
	 * $ret .= " default {$default}";
	 * }
	 * return $ret;
	 * }catch(Exception $x){
	 * x($f, $x);
	 * }
	 * }
	 */
	public function parseValueFromQueryResult($v)
	{
		$f = __METHOD__; //IntegerDatum::getShortClass()."(".static::getShortClass().")->parseValueFromQueryResult()";
		try {
			if ($v === null) {
				return $this->isNullable() ? null : 0;
			}
			return ! is_int($v) ? intval($v) : $v;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function parseString(string $v)
	{
		return intval($v);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->bitCount);
	}
}
