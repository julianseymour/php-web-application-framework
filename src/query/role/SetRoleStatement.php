<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\role;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class SetRoleStatement extends RoleStatement{

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"all except"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"all except"
		]);
	}
	
	public function setAllExceptFlag(bool $value = true):bool{
		return $this->setFlag("all except");
	}

	public function getAllExceptFlag():bool{
		return $this->getFlag("all except");
	}

	public function withRoleAllExcept(...$role){
		$this->setAllExceptFlag(true);
		return $this->withRole(...$role);
	}

	public function getRoleStatementString(){
		$f = __METHOD__;
		$string = "role ";
		if($this->hasRoleType()){
			$string .= $this->getRoleType();
		}elseif($this->hasRoles()){
			if($this->getAllExceptFlag()){
				$string .= " all except";
			}
			$string .= " " . implode(',', $this->getRoles());
		}else{
			Debug::error("{$f} neither of the above");
			return null;
		}
		return $string;
	}
}
