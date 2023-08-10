<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait CharacterSetTrait
{

	protected $characterSet;

	public function setCharacterSet($set)
	{
		return $this->characterSet = $set;
	}

	public function hasCharacterSet()
	{
		return isset($this->characterSet);
	}

	public function getCharacterSet()
	{
		$f = __METHOD__; //"CharacterSetTrait(".static::getShortClass().")->getCharacterSet()";
		if (! $this->hasCharacterSet()) {
			Debug::error("{$f} character set is undefined");
		}
		return $this->characterSet;
	}
}
