<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait KeyBlockSizeTrait
{

	protected $keyBlockSizeValue;

	public function setKeyBlockSize($size)
	{
		return $this->keyBlockSizeValue = $size;
	}

	public function hasKeyBlockSize()
	{
		return isset($this->keyBlockSizeValue);
	}

	public function getKeyBlockSize()
	{
		$f = __METHOD__; //"KeyBlockSizeTrait(".static::getShortClass().")->getKeyBlockSize()";
		if (! $this->hasKeyBlockSize()) {
			Debug::error("{$f} key block size is undefined");
		}
		return $this->keyBlockSizeValue;
	}
}
