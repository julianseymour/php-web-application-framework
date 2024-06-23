<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\user;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\auth\password\PasswordTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\role\DatabaseRoleData;
use Exception;

class DatabaseUserDefinition extends DatabaseRoleData{

	use PasswordTrait;
	
	protected $oldPassword;

	protected $queryStatement;

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"discard old password",
			"random password",
			"retain current password"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"discard old password",
			"random password",
			"retain current password"
		]);
	}
	
	public function setQueryStatement($q){
		$f = __METHOD__;
		if(!$q instanceof UserStatement){
			Debug::error("{$f} statement must be an instanceof UserStatement");
		}elseif($this->hasQueryStatement()){
			$this->release($this->queryStatement);
		}
		return $this->queryStatement = $this->claim($q);
	}

	public function hasQueryStatement():bool{
		return isset($this->queryStatement);
	}

	public function getQueryStatement(){
		$f = __METHOD__;
		if(!$this->hasQueryStatement()){
			Debug::error("{$f} query statement is undefined");
		}
		return $this->queryStatement;
	}

	public function by($password): DatabaseUserDefinition{
		$this->setPassword($password);
		return $this;
	}

	public function setOldPassword($pw){
		$f = __METHOD__;
		if(!is_string($pw)){
			Debug::error("{$f} current password must be a string");
		}elseif($this->hasOldPassword()){
			$this->release($this->oldPassword);
		}
		return $this->oldPassword = $this->claim($pw);
	}

	public function hasOldPassword():bool{
		return isset($this->oldPassword);
	}

	public function getOldPassword(){
		$f = __METHOD__;
		if(!$this->hasOldPassword()){
			Debug::error("{$f} current password is undefined");
		}
		return $this->oldPassword;
	}

	public function replace($current): DatabaseUserDefinition{
		$this->setOldPassword($current);
		return $this;
	}

	public function setRandomPasswordFlag($value = true){
		$f = __METHOD__;
		if($this->getStatementType() === STATEMENT_TYPE_ALTER_CURRENT_USER){
			Debug::error("{$f} random password is unsupported by alter current user statement");
			return false;
		}
		$this->setRequiredMySQLVersion("8.0.18");
		return $this->setFlag("random password", $value);
	}

	public function getRandomPasswordFlag():bool{
		return $this->getFlag("random password");
	}

	public function byRandomPassword(): DatabaseUserDefinition{
		$this->setRandomPasswordFlag(true);
		return $this;
	}

	public function setDiscardOldPasswordFlag(bool $value = true):bool{
		return $this->setFlag("discard old password", $value);
	}

	public function getDiscardOldPasswordFlag():bool{
		return $this->getFlag("discard old password");
	}

	public function discardOldPassword(): DatabaseUserDefinition{
		$this->setDiscardOldPasswordFlag(true);
		return $this;
	}

	public function setRetainCurrentPasswordFlag(bool $value = true):bool{
		return $this->setFlag("retain current password", $value);
	}

	public function getRetainCurrentPasswordFlag():bool{
		return $this->getFlag("retain current password");
	}

	public function retainCurrentPassword(): DatabaseUserDefinition{
		$this->setRetainCurrentPasswordFlag(true);
		return $this;
	}

	public function getAuthOptionString():string{
		$f = __METHOD__;
		$string = "";
		if($this->getDiscardOldPasswordFlag()){
			$string .= " discard old password";
			return $string;
		}elseif($this->hasAuthPlugin() || $this->hasPassword() || $this->getRandomPasswordFlag()){
			$string .= " identified";
			$qs = $this->hasQueryStatement() ? $this->getQueryStatement() : null;
			$current_user = $qs instanceof AlterUserStatement && $qs->getCurrentUserFlag();
			if(!$current_user && $this->hasAuthPlugin()){
				$string .= " with " . $this->getAuthPlugin();
			}
			if($this->hasPassword() || $this->getRandomPasswordFlag()){
				if($this->getRandomPasswordFlag()){
					if($current_user){
						Debug::error("{$f} cannot have random password for alter current user");
					}
					$string .= " by random password";
				}elseif($this->hasPassword()){
					$string .= " by " . single_quote($this->getPassword());
				}else{
					Debug::error("{$f} neither of the above");
				}
				if(!$qs instanceof CreateUserStatement){
					if($this->hasOldPassword()){
						$string .= " replace " . single_quote($this->getOldPassword());
					}
					if($this->getRetainCurrentPasswordFlag()){
						$string .= " retain current password";
					}
				}
			}
		}
		return $string;
	}

	public function toSQL(): string{
		$f = __METHOD__;
		try{
			return parent::toSQL().$this->getAuthOptionString();
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->oldPassword, $deallocate);
		$this->release($this->password, $deallocate);
		$this->release($this->queryStatement, $deallocate);
	}
}
