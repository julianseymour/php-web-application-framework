<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait LimitOffsetTrait
{

	use LimitedTrait;

	protected $offsetRowCount;

	public function offset($offset): QueryStatement
	{
		$this->setOffset($offset);
		return $this;
	}

	public function setOffset($offsetRowCount): int
	{
		$f = __METHOD__; //"LimitOffsetTrait(".static::getShortClass().")->offsetRowCount()";
		if (! $this->hasLimit()) {
			Debug::error("{$f} assign limit before assigning offset plz");
		} elseif (empty($offsetRowCount) || ! is_int($offsetRowCount)) {
			Debug::error("{$f} invalid offset value");
		}
		return $this->offsetRowCount = $offsetRowCount;
	}

	public function hasOffset(): bool
	{
		return $this->hasLimit() && ! empty($this->offsetRowCount) && is_int($this->offsetRowCount);
	}

	public function getOffset(): int
	{
		$f = __METHOD__; //"LimitOffsetTrait(".static::getShortClass().")->getOffset()";
		if (! $this->hasLimit()) {
			Debug::error("{$f} should not be here without a limit");
		} elseif (! $this->hasOffset()) {
			Debug::error("{$f} offset is undefined");
		}
		return $this->offsetRowCount;
	}
}
