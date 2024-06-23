<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\role;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\user\MultipleDatabaseUserDefinitionsTrait;

class SetDefaultRoleStatement extends RoleStatement{

	use MultipleDatabaseUserDefinitionsTrait;

	public function getRoleStatementString():string{
		$f = __METHOD__;
		$string = "default role ";
		if($this->hasRoleType()){
			$string .= $this->getRoleType();
		}elseif($this->hasRoles()){
			$string .= " " . implode(',', $this->getRoles());
		}else{
			Debug::error("{$f} neither of the above");
			return null;
		}
		return $string;
	}

	public function getQueryStatementString():string{
		return parent::getQueryStatementString() . " to " . implode(',', $this->getUsers());
	}
}
