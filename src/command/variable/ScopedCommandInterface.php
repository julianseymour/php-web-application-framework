<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\variable;

interface ScopedCommandInterface
{

	public function getScope(): Scope;

	public function hasScope(): bool;

	public function setScope(?Scope $scope): ?Scope;
}
