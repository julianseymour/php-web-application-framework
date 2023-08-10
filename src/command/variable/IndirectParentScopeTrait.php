<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\variable;

trait IndirectParentScopeTrait
{

	use ScopedTrait;

	public function hasParentScope(): bool
	{
		return $this->hasScope() && $this->getScope()->hasParentScope();
	}

	public function getParentScope(): Scope
	{
		if (! $this->hasScope()) {
			$this->setScope(new Scope());
		}
		return $this->getScope()->getParentScope();
	}

	public function setParentScope(?Scope $scope): ?Scope
	{
		if (! $this->hasScope()) {
			$this->setScope($scope);
		}
		return $this->getScope()->setParentScope($scope);
	}
}
