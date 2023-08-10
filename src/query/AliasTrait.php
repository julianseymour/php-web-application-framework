<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait AliasTrait
{

	protected $alias;

	public function setAlias($alias)
	{
		$f = __METHOD__; //"AliasTrait(".static::getShortClass().")->setAlias()";
		if ($alias == null) {
			unset($this->alias);
			return null;
		} elseif (! is_string($alias)) {
			Debug::error("{$f} alias must be a string");
		} elseif (empty($alias)) {
			Debug::error("{$f} alias must not be an empty string");
		}
		return $this->alias = $alias;
	}

	public function hasAlias()
	{
		return isset($this->alias) && is_string($this->alias) && ! empty($this->alias);
	}

	public function as($alias)
	{
		$this->setAlias($alias);
		return $this;
	}

	public function getAlias()
	{
		return $this->alias;
	}
}