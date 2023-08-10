<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait PrioritizedTrait
{

	protected $priorityLevel;

	public function setPriority($p)
	{
		$f = __METHOD__; //"PrioritizedTrait(".static::getShortClass().")->setPriority()";
		if ($p == null) {
			unset($this->priorityLevel);
			return null;
		} elseif (! is_string($p)) {
			Debug::error("{$f} priority must be a string");
		}
		return $this->priorityLevel = strtolower($p);
	}

	public function hasPriority()
	{
		return isset($this->priorityLevel) && is_string($this->priorityLevel) && ! empty($this->priorityLevel);
	}

	public function getPriority()
	{
		$f = __METHOD__; //"PrioritizedTrait(".static::getShortClass().")->getPriority()";
		if (! $this->hasPriority()) {
			Debug::error("{$f} priority is undefined");
		}
		return $this->priorityLevel;
	}

	public function priority($p)
	{
		$this->setPriority($p);
		return $this;
	}
}