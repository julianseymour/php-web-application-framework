<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait LimitedTrait
{

	protected $limitCount;

	public function limit($limit): object
	{
		$this->setLimit($limit);
		return $this;
	}

	public function setLimit($limitCount): int
	{
		$f = __METHOD__; //"LimitedTrait(".static::getShortClass().")->setLimit()";
		if(empty($limitCount) || ! is_int($limitCount)) {
			Debug::error("{$f} invalid limit value");
		}
		return $this->limitCount = $limitCount;
	}

	public function hasLimit(): bool
	{
		return ! empty($this->limitCount) && is_int($this->limitCount) && $this->limitCount > 0;
	}

	public function getLimit(): int
	{
		$f = __METHOD__; //"LimitedTrait(".static::getShortClass().")->getLimit()";
		if(!$this->hasLimit()) {
			Debug::error("{$f} limit is undefined");
		}
		return $this->limitCount;
	}
}
