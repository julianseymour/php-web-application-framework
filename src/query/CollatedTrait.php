<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait CollatedTrait
{

	protected $collationName;

	public function setCollationName($cn)
	{
		return $this->collationName = $cn;
	}

	public function hasCollationName()
	{
		return isset($this->collationName);
	}

	public function getCollationName()
	{
		$f = __METHOD__; //"CharacterSetTrait(".static::getShortClass().")->getCollationName()";
		if(!$this->hasCollationName()) {
			Debug::error("{$f} collation name is undefined");
		}
		return $this->collationName;
	}
}