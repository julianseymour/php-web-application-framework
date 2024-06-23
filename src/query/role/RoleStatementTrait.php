<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\role;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

/**
 * trait for query statements that have a SetRoleStatement or SetDefaultRoleStatement as a subquery
 * (AlterUserStatement and GrantStatment)
 *
 * @author j
 */
trait RoleStatementTrait{

	protected $roleStatement;

	public function setRoleStatement($rs){
		$f = __METHOD__;
		if($rs == null){
			unset($this->roleStatement);
			return null;
		}elseif(!$rs instanceof RoleStatement){
			Debug::error("{$f} role statement must be an instanceof RoleStatement");
		}
		return $this->roleStatement = $rs;
	}

	public function hasRoleStatement():bool{
		return isset($this->roleStatement) && $this->roleStatement instanceof RoleStatement;
	}

	public function getRoleStatment(){
		$f = __METHOD__;
		if(!$this->hasRoleStatement()){
			Debug::error("{$f} role statement is undefined");
		}
		return $this->roleStatement;
	}
}
