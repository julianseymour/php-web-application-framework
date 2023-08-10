<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\role;

use JulianSeymour\PHPWebApplicationFramework\common\arr\ArrayPropertyTrait;

trait MultipleRolesTrait
{

	use ArrayPropertyTrait;

	public function setRoles($values)
	{
		return $this->setArrayProperty("roles", $values);
	}

	public function pushRoles(...$values)
	{
		return $this->pushArrayProperty("roles", ...$values);
	}

	public function mergeRoles($values)
	{
		return $this->mergeArrayProperty("roles", $values);
	}

	public function hasRoles()
	{
		return $this->hasArrayProperty("roles");
	}

	public function getRoles()
	{
		return $this->getProperty("roles");
	}

	public function getRoleCount()
	{
		return $this->getProperty("roles");
	}
}