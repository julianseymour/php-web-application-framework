<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use Exception;

abstract class IntegerDatum extends AbstractNumericDatum{

	protected $bitCount;

	public function __construct($name, $bit_count){
		parent::__construct($name);
		$this->setUnsigned(false);
		$this->setBitCount($bit_count);
	}

	public function cast($v){
		if (is_string($v) && $this->hasApoptoticSignal() && $this->getApoptoticSignal() === $v) {
			return $v;
		}
		return intval($v);
	}

	public function getConstructorParams(): ?array{
		return [
			$this->getName(),
			$this->getBitCount()
		];
	}

	public function getUrlEncodedValue(){
		return $this->getValue();
	}

	public function setBitCount(?int $bits):?int{
		$f = __METHOD__;
		if (! is_int($bits)) {
			Debug::error("{$f} received a non-integer value");
		}
		return $this->bitCount = $bits;
	}

	public function hasBitCount():bool{
		return isset($this->bitCount) && is_int($this->bitCount);
	}

	public function getHumanReadableValue(){
		return $this->getValue();
	}

	public function setUnsigned(bool $unsigned=true):bool{
		return $this->setFlag('unsigned', $unsigned);
	}

	public function isUnsigned():bool{
		return $this->getFlag('unsigned');
	}

	public function getHumanWritableValue(){
		return $this->getValue();
	}

	public function setAutoIncrement(bool $auto=true):bool{
		return $this->setFlag('autoIncrement', $auto);
	}

	public function getAutoIncrementFlag():bool{
		return $this->getFlag("autoIncrement");
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"autoIncrement",
			"unsigned"
		]);
	}

	public function parseValueFromSuperglobalArray($value){
		$f = __METHOD__;
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

	public static function validateStatic($value): int{
		if (! is_int($value)) {
			return FAILURE;
		}
		return SUCCESS;
	}

	public static function getTypeSpecifier():string{
		return 'i';
	}

	public function getBitCount():int{
		if (! $this->hasBitCount()) {
			return 32;
		}
		return $this->bitCount;
	}

	public function getColumnTypeString(): string{
		$f = __METHOD__;
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
					$name = $this->getName();
					Debug::error("{$f} invalid byte count \"{$byte_count}\" for datum \"{$name}\"");
					break;
			}
		}
		return "{$prefix}INT{$suffix}";
	}

	public function parseValueFromQueryResult($v){
		$f = __METHOD__;
		try {
			if ($v === null) {
				return $this->isNullable() ? null : 0;
			}
			return ! is_int($v) ? intval($v) : $v;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function parseString(string $v){
		return intval($v);
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->bitCount);
	}
}
