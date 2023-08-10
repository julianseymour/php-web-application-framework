<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ParameterCountingTrait
{

	public abstract function inferParameterCount();

	protected $parameterCount;

	public function getParameterCount(): ?int
	{
		$f = __METHOD__; //"ParameterCountingTrait(".static::getShortClass().")->getParameterCount()";
		if (! $this->hasParameterCount()) {
			Debug::error("{$f} parameter count is undefined");
		}
		return $this->parameterCount;
	}

	public function setParameterCount($count): ?int
	{
		$f = __METHOD__; //"ParameterCountingTrait(".static::getShortClass().")->setParameterCount()";
		$print = false;
		if ($count === null) {
			if ($print) {
				Debug::print("{$f} destroying parameter count");
			}
			unset($this->parameterCount);
			return null;
		} elseif (! is_int($count)) {
			Debug::error("{$f} value count must be a non-negative integer");
		} elseif ($count < 0) {
			Debug::error("{$f} value count must be non-negative");
		}
		return $this->parameterCount = $count;
	}

	public function hasParameterCount(): bool
	{
		return (isset($this->parameterCount) && is_int($this->parameterCount));
	}

	public function withParameterCount($count)
	{
		$this->setParameterCount($count);
		return $this;
	}
}
