<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\grant;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class RevokeStatement extends PrivilegeStatement
{

	public static function declareFlag()
	{
		return array_merge(parent::declareFlags(), [
			"all"
		]);
	}

	public function setAllPrivilegesFlag($value = true)
	{
		return $this->setFlag("all", $value);
	}

	public function getAllPrivilegesFlag()
	{
		return $this->getFlag("all");
	}

	public static function all()
	{
		$revoke = new RevokeStatement();
		$revoke->setAllPrivilegesFlag(true);
		return $revoke;
	}

	public function getQueryStatementString()
	{
		$f = __METHOD__; //RevokeStatement::getShortClass()."(".static::getShortClass().")->getQueryStatementString()";
		// REVOKE
		$string = "revoke ";
		if ($this->hasPrivileges()) {
			// priv_type [(column_list)] [, priv_type [(column_list)]] ... ON
			$string .= implode(',', $this->getPrivileges()) . " on ";
			// [object_type]
			if ($this->hasObjectType()) {
				$string .= $this->getObjectType() . " ";
			}
			// priv_level
			if ($this->hasDatabaseName()) {
				$string .= back_quote($this->getDatabaseName()) . ".";
			}
			$string .= back_quote($this->getTableName());
		} elseif ($this->getAllPrivilegesFlag()) {
			// REVOKE ALL [PRIVILEGES], GRANT OPTION
			$string .= "all, grant option";
		} elseif ($this->hasProxyUser()) {
			// REVOKE PROXY ON user_or_role
			$string .= "proxy on " . $this->getProxyUser();
		} elseif ($this->hasRoles()) {
			// REVOKE role [, role ] ...
			$string .= implode(',', $this->getRoles());
		} else {
			Debug::error("{$f} none of the above");
		}
		// FROM user_or_role [, user_or_role] ...
		$string .= " from " . implode(',', $this->getUsers());
		return $string;
	}
}
