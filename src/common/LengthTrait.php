<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait LengthTrait
{

	protected $lengthValue;

	public function setLength($length)
	{
		$f = __METHOD__; //"LengthTrait(".static::getShortClass().")->setLength()";
		if ($length === null) {
			unset($this->lengthValue);
			return null;
		} elseif (! is_int($length)) {
			Debug::error("{$f} length must be a positive integer");
		} elseif ($length < 1) {
			Debug::error("{$f} length must be positive");
		}
		return $this->lengthValue = $length;
	}

	public function hasLength()
	{
		return isset($this->lengthValue) && is_int($this->lengthValue);
	}

	public function getLength()
	{
		$f = __METHOD__; //"LengthTrait(".static::getShortClass().")->getLength()";
		if (! $this->hasLength()) {
			Debug::error("{$f} length is undefined");
		}
		return $this->lengthValue;
	}

	public function length($length)
	{
		$this->setLength($length);
		return $this;
	}
}
