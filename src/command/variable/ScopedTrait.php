<?php

namespace JulianSeymour\PHPWebApplicationFramework\command\variable;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\event\ReleaseScopeEvent;

trait ScopedTrait{
	
	protected $scope;

	public function releaseScope(bool $deallocate=false){
		$f = __METHOD__;
		if(!$this->hasScope()){
			Debug::error("{$f} scope is undefined");
		}
		$scope = $this->scope;
		unset($this->scope);
		if($this->hasAnyEventListener(EVENT_RELEASE_SCOPE)){
			$this->dispatchEvent(new ReleaseScopeEvent($scope, $deallocate));
		}
		$this->release($scope, $deallocate);
	}
	
	public function setScope(?Scope $scope):?Scope{
		if($this->hasScope()){
			$this->releaseScope();
		}
		return $this->scope = $this->claim($scope);
	}

	public function hasScope():bool{
		return isset($this->scope);
	}

	public function getScope():Scope{
		$f = __METHOD__;
		if(!$this->hasScope()){
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} scope is undefined. Declared {$decl}");
		}
		return $this->scope;
	}
}
