<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\grant;

use function JulianSeymour\PHPWebApplicationFramework\comma_separate_sql;
use function JulianSeymour\PHPWebApplicationFramework\hasMinimumMySQLVersion;
use function JulianSeymour\PHPWebApplicationFramework\starts_ends_with;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\role\DatabaseRoleData;
use JulianSeymour\PHPWebApplicationFramework\query\role\MultipleRolesTrait;
use JulianSeymour\PHPWebApplicationFramework\query\role\RoleStatementTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\FullTableName;

class GrantStatement extends PrivilegeStatement implements StaticPropertyTypeInterface{

	use MultipleRolesTrait;
	use RoleStatementTrait;
	use StaticPropertyTypeTrait;

	protected $asUsername;

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"admin",
			"grant"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"admin",
			"grant"
		]);
	}
	
	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array{
		return [
			"roles" => 's',
			"privileges" => DatabasePrivilege::class,
			"users" => DatabaseRoleData::class
		];
	}

	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		if($this->hasAsUsername()){
			$this->release($this->asUsername, $deallocate);
		}
		if($this->hasRoleStatement()){
			$this->release($this->roleStatement, $deallocate);
		}
	}
	
	public function setGrantOptionFlag(bool $value = true):bool{
		return $this->setFlag("grant");
	}

	public function getGrantOptionFlag():bool{
		return $this->getFlag("grant");
	}

	public function withGrantOption(bool $value=true):GrantStatement{
		$this->setGrantOptionFlag($value);
		return $this;
	}

	public function setAsUsername($name){
		$f = __METHOD__;
		if(!hasMinimumMySQLVersion("8.0.16")){
			Debug::error("{$f} insufficient MySQL version");
		}elseif(!is_string($name)){
			Debug::error("{$f} username must be a string");
		}elseif($this->hasTableName() && starts_ends_with("*", $this->getTableName())){
			Debug::error("{$f} the AS clause is only supported for global privileges");
		}elseif($this->hasAsUsername()){
			$this->release($this->asUsername);
		}
		return $this->asUsername = $this->claim($name);
	}

	public function hasAsUsername():bool{
		return isset($this->asUsername);
	}

	public function getAsUsername(){
		$f = __METHOD__;
		if(!$this->hasAsUsername()){
			Debug::error("{$f} as username is undefined");
		}
		return $this->asUsername;
	}

	public function setAdminOptionFlag(bool $value = true):bool{
		return $this->setFlag("admin", $value);
	}

	public function getAdminOptionFlag():bool{
		return $this->getFlag("admin");
	}

	public function withAdminOption(bool $value=true){
		$this->setAdminOptionFlag($value);
		return $this;
	}

	public function to(...$users): GrantStatement{
		$this->setUsers([
			...$users
		]);
		return $this;
	}

	public function getQueryStatementString():string{
		$f = __METHOD__;
		// GRANT
		$string = "grant ";
		if($this->hasPrivileges()){
			// priv_type [(column_list)] [, priv_type [(column_list)]] ...
			$string .= comma_separate_sql($this->getPrivileges());
			// ON [object_type]
			$string .= " on ";
			if($this->hasObjectType()){
				$string .= $this->getObjectType() . " ";
			}
			// priv_level
			if($this->hasDatabaseName()){
				$db = $this->getDatabaseName();
			}else{
				$db = null;
			}
			if($this->hasTableName()){
				$table = $this->getTableName();
			}else{
				$table = null;
			}
			$ftn = new FullTableName($db, $table);
			$string .= $ftn->toSQL();
			// TO user_or_role [, user_or_role] ...
			$string .= " to ";
			$count = 0;
			foreach($this->getUsers() as $user){
				if($count > 0){
					$string .= ",";
				}
				$string .= $user->getUsernameHostString();
				$count ++;
			} // .implode(',', $this->getUsers());
			  // [WITH GRANT OPTION]
			if($this->getGrantOptionFlag()){
				$string .= " with grant option";
			}
			if($this->hasAsUsername() && hasMinimumMySQLVersion("8.0.16")){
				// [AS user
				$string .= " as " . $this->getAsUsername();
				if($this->hasSetRoleStatement()){
					// [WITH ROLE {DEFAULT | NONE | ALL | [ALL EXCEPT] role [, role ] ... }]
					if($this->hasRoleStatement()){
						$string .= " with " . $this->getRoleStatement()->getRoleStatementString();
					}
				}
			}
		}elseif($this->hasProxyUser()){
			// GRANT PROXY ON user_or_role TO user_or_role [, user_or_role] ...
			$string .= "proxy on " . $this->getProxyUser() . " to ";
			$count = 0;
			foreach($this->getUsers() as $user){
				if($count > 0){
					$string .= ",";
				}
				$string .= $user->getUsernameHostString();
				$count ++;
			} // .implode(',', $this->getUsers());
			  // [WITH GRANT OPTION]
			if($this->getGrantOptionFlag()){
				$string .= " with grant option";
			}
		}elseif($this->hasRoles()){
			// GRANT role [, role] ... TO user_or_role [, user_or_role] ...
			$string .= comma_separate_sql($this->getRoles()) . " to ";
			$count = 0;
			foreach($this->getUsers() as $user){
				if($count > 0){
					$string .= ",";
				}
				$string .= $user->getUsernameHostString();
				$count ++;
			} // .implode(',', $this->getUsers());
			if($this->hasAdminOptionFlag()){
				// [WITH ADMIN OPTION]
				$string .= " with admin option";
			}
		}else{
			Debug::error("{$f} none of the above");
		}
		return $string;
	}
}
