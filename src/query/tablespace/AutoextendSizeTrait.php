<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\tablespace;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait AutoextendSizeTrait
{

	protected $autoextendSizeValue;

	public function setAutoextendSize($value)
	{
		$f = __METHOD__; //"AutoextendSizeTrait(".static::getShortClass().")->setAutoextendSize()";
		if(!is_int($value)) {
			Debug::error("{$f} this function accepts integers only");
		}
		return $this->autoextendSizeValue = $value;
	}

	public function hasAutoextendSize()
	{
		return $this->autoextendSizeValue;
	}

	public function getAutoextendSize()
	{
		$f = __METHOD__; //"AutoextendSizeTrait(".static::getShortClass().")->getAutoextendSize()";
		if(!$this->hasAutoextendSize()) {
			Debug::error("{$f} autoextend size is undefined");
		}
		return $this->autoextendSizeValue;
	}

	public function autoextendSize($value)
	{
		$this->setAutoextendSize($value);
		return $this;
	}
}