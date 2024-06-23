<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\variable;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ParentScopedTrait{

	protected $parentScope;

	public function hasParentScope(): bool{
		return isset($this->parentScope);
	}

	public function setParentScope(?Scope $parent): ?Scope{
		if($this->hasParentScope()){
			$this->release($this->parentScope);
		}
		return $this->parentScope = $this->claim($parent);
	}

	public function getParentScope(): Scope{
		$f = __METHOD__;
		if(!$this->hasParentScope()){
			Debug::error("{$f} parent scope is undefined");
		}
		return $this->parentScope;
	}
}