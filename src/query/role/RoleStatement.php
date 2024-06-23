<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\role;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;

abstract class RoleStatement extends QueryStatement{

	use MultipleRolesTrait;

	public abstract function getRoleStatementString():string;

	protected $roleType;

	public function __construct(...$roles){
		parent::__construct();
		$this->requirePropertyType("roles", DatabaseRoleData::class);
		if(isset($roles)){
			$this->setRoles($roles);
		}
	}

	public function setRoleType($type){
		$f = __METHOD__;
		if(!is_string($type)){
			Debug::error("{$f} as user role type must be a string");
		}
		$type = strtolower($type);
		switch($type){
			case "all except":
				if($this instanceof SetRoleStatement){
					$this->setAllExceptFlag(true);
					return $type;
				}
			case CONST_DEFAULT:
				if($this instanceof SetDefaultRoleStatement){
					Debug::error("{$f} SetDefaultRoleStatement does not support default role type or 'all except'");
				}
			case CONST_NONE:
			case CONST_ALL:
				break;
			default:
				Debug::error("{$f} invalid as user role type \"{$type}\"");
		}
		if($this->hasRoleType()){
			$this->release($this->roleType);
		}
		return $this->roleType = $this->claim($type);
	}

	public function hasRoleType():bool{
		return isset($this->roleType);
	}

	public function getRoleType(){
		$f = __METHOD__;
		if(!$this->hasRoleType()){
			Debug::error("{$f} as user role type is undefined");
		}
		return $this->roleType;
	}

	public function setTemporaryRole(...$role){
		$f = __METHOD__;
		if(count($role) > 1){
			return $this->withRoles($role);
		}
		$role = $role[0];
		if(is_array($role)){
			return $this->withRole(...$role);
		}elseif(!is_string($role)){
			if($role instanceof DatabaseRoleData){
				$role = $role->getUsername();
			}else{
				Debug::error("{$f} role name is not a string");
			}
		}
		switch($role){
			case CONST_DEFAULT:
			case CONST_NONE:
			case CONST_ALL:
				$this->setRoleType($role);
				return $this;
			default:
				return $this->withRoles([
					$role
				]);
		}
	}

	public function getQueryStatementString():string{
		return "set " . $this->getRoleStatementString();
	}
}
