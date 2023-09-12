<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\variable;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ScopedTrait
{

	protected $scope;

	public function setScope(?Scope $scope): ?Scope
	{
		$f = __METHOD__; //"ScopedTrait(".static::getShortClass().")->setScope()";
		if($scope == null) {
			unset($this->scope);
			return null;
		}elseif(!$scope instanceof Scope) {
			Debug::error("{$f} scope must be an instanceof Scope");
		}
		return $this->scope = $scope;
	}

	public function hasScope(): bool
	{
		return isset($this->scope);
	}

	public function getScope(): Scope
	{
		$f = __METHOD__; //"ScopedTrait(".static::getShortClass().")->getScope()";
		if(!$this->hasScope()) {
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} scope is undefined. Declared {$decl}");
		}
		return $this->scope;
	}
}
