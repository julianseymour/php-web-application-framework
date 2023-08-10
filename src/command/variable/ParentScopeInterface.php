<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\variable;

interface ParentScopeInterface
{

	// extends ScopedCommandInterface{
	public function getParentScope(): Scope;

	public function hasParentScope(): bool;

	public function setParentScope(?Scope $scope): ?Scope;
}
