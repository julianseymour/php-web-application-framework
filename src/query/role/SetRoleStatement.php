<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\role;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class SetRoleStatement extends RoleStatement
{

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"all except"
		]);
	}

	public function setAllExceptFlag($value = true)
	{
		return $this->setFlag("all except");
	}

	public function getAllExceptFlag()
	{
		return $this->getFlag("all except");
	}

	public function withRoleAllExcept(...$role)
	{
		$this->setAllExceptFlag(true);
		return $this->withRole(...$role);
	}

	public function getRoleStatementString()
	{
		$f = __METHOD__; //SetRoleStatement::getShortClass()."(".static::getShortClass().")->getRoleStatementString()";
		$string = "role ";
		if($this->hasRoleType()) {
			$string .= $this->getRoleType();
		}elseif($this->hasRoles()) {
			if($this->getAllExceptFlag()) {
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
