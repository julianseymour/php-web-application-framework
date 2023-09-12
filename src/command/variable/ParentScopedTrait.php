<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\variable;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ParentScopedTrait
{

	protected $parentScope;

	public function hasParentScope(): bool
	{
		return isset($this->parentScope);
	}

	public function setParentScope(?Scope $parent): ?Scope
	{
		if($parent == null) {
			unset($this->parentScope);
			return null;
		}
		return $this->parentScope = $parent;
	}

	public function getParentScope(): Scope
	{
		$f = __METHOD__; //"ParentScopedTrait(".static::getShortClass().")->getParentScope()";
		if(!$this->hasParentScope()) {
			Debug::error("{$f} parent scope is undefined");
		}
		return $this->parentScope;
	}
}