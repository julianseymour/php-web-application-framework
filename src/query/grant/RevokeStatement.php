<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\grant;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;

class RevokeStatement extends PrivilegeStatement{

	public static function declareFlag(){
		return array_merge(parent::declareFlags(), [
			"all"
		]);
	}

	public function setAllPrivilegesFlag(bool $value = true):bool{
		return $this->setFlag("all", $value);
	}

	public function getAllPrivilegesFlag():bool{
		return $this->getFlag("all");
	}

	public function all(bool $value=true):RevokeStatement{
		$this->setAllPrivilegesFlag($value);
		return $this;
	}

	public function from(...$user):RevokeStatement{
		$this->setUsers($user);
		return $this;
	}
	
	public function getQueryStatementString(){
		$f = __METHOD__;
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
				$db = $this->getDatabaseName();
				if($db instanceof SQLInterface){
					$db = $db->toSQL();
				}
				if($db !== "*"){
					$db = back_quote($db);
				}
			}else{
				$db = "*";
			}
			if($this->hasTableName()){
				$table = $this->getTableName();
				if($table instanceof SQLInterface){
					$table = $table->toSQL();
				}
				if($table !== "*"){
					$table = back_quote($table);
				}
			}else{
				$table = "*";
			}
			$string .=  "{$db}.{$table}";
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
		$string .= " from ";
		$count = 0;
		foreach($this->getUsers() as $user){
			if($count++ > 0){
				$string .= ",";
			}
			$string .= $user->toSQL();
		}
		return $string;
	}
}
