<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\user;

use function JulianSeymour\PHPWebApplicationFramework\hasMinimumMySQLVersion;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\IfExistsFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\query\role\RoleStatementTrait;
use Exception;

class AlterUserStatement extends UserStatement{

	use IfExistsFlagBearingTrait;
	use RoleStatementTrait;

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"all",
			"currentUser",
			"if exists",
			"noDefaultRole"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"all",
			"currentUser",
			"if exists",
			"noDefaultRole"
		]);
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		if($this->hasRoleStatement()){
			$this->release($this->roleStatement, $deallocate);
		}
	}
	
	public function setCurrentUserFlag(bool $value = true):bool{
		return $this->setFlag("currentUser", $value);
	}

	public function getCurrentUserFlag():bool{
		return $this->getFlag("currentUser");
	}

	public function setNoDefaultRoleFlag(bool $value=true):bool{
		return $this->setFlag("noDefaultRole", $value);
	}

	public function getNoDefaultRoleFlag():bool{
		return $this->getFlag("noDefaultRole");
	}
	
	public function defaultRoleNone():AlterUserStatement{
		$this->setNoDefaultRoleFlag(true);
		return $this;
	}

	public function setAllDefaultRolesFlag(bool $value=true):bool{
		return $this->setFlag("all", $value);
	}

	public function getAllDefaultRolesFlag():bool{
		return $this->getFlag("all");
	}
	
	public function defafultRoleAll():AlterUserStatement{
		$this->setAllDefaultRolesFlag(true);
		return $this;
	}

	public function defaultRole(...$role){
		if(count($role) === 1){
			$role = $role[0];
			if(is_string($role)){
				switch(strtolower($role)){
					case "all":
						$this->setAllDefaultRolesFlag(true);
						return $role;
					case "none":
						$this->setNoDefaultRoleFlag(true);
						return $role;
					default:
				}
			}
		}
		return parent::defaultRole(...$role);
	}

	public function getQueryStatementString(){
		$f = __METHOD__;
		try{
			// ALTER USER
			$string = "alter user ";
			// [IF EXISTS]
			if($this->getIfExistsFlag()){
				$string . "if exists ";
			}
			if($this->getCurrentUserFlag()){
				// ALTER USER [IF EXISTS]
				// USER() user_func_auth_option
				// user_func_auth_option: {
				// IDENTIFIED BY 'auth_string' [REPLACE 'current_auth_string'] [RETAIN CURRENT PASSWORD]
				// | DISCARD OLD PASSWORD
				// }
				$string .= "user() " . $this->getUser(0)->getAuthOptionString();
			}elseif($this->hasRoleStatement()){
				// ALTER USER [IF EXISTS] user [DEFAULT ROLE {NONE | ALL | role [, role ] ...}]
				if($this->getUserCount() !== 1){
					Debug::error("{$f} cannot set default roles for multiple users");
				}
				$user = $this->getUser(0);
				$string .= $user . " " . $this->getRoleStatement()->getRoleStatementString();
			}else{
				// user [auth_option] [, user [auth_option]] ...
				$string .= implode(',', $this->getUsers());
				// [REQUIRE {NONE | tls_option [[AND] tls_option] ...}]
				if($this->getRequireNoneFlag() || $this->hasTLSOptions()){
					$string .= " " . $this->getTLSOptionsString();
				}
				// [WITH resource_option [resource_option] ...]
				if($this->hasResourceOptions()){
					$string .= $this->getResourceOptionsString();
				}
				// [password_option | lock_option] ...
				if($this->hasPasswordOptions()){
					$string .= $this->getPasswordOptionsString();
				}
				// [COMMENT 'comment_string' | ATTRIBUTE 'json_object']
				if(($this->hasComment() && hasMinimumMySQLVersion("8.0.21")) || $this->hasAttribute()){
					$string .= $this->getCommentAttributeString();
				}
			}
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}