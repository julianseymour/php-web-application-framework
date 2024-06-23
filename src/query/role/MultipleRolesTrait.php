<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\role;

use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;

trait MultipleRolesTrait{

	use ArrayPropertyTrait;

	public function setRoles($values){
		return $this->setArrayProperty("roles", $values);
	}

	public function pushRoles(...$values):int{
		return $this->pushArrayProperty("roles", ...$values);
	}

	public function mergeRoles($values){
		return $this->mergeArrayProperty("roles", $values);
	}

	public function hasRoles():bool{
		return $this->hasArrayProperty("roles");
	}

	public function getRoles(){
		return $this->getProperty("roles");
	}

	public function getRoleCount():int{
		return $this->getProperty("roles");
	}
}
