<?php

namespace JulianSeymour\PHPWebApplicationFramework\account;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use Exception;
use mysqli;

trait UserKeyColumnTrait{

	public function getUserKey():string{
		$f = __METHOD__;
		try{
			$print = false;
			if($this->hasColumn('userKey') && $this->hasColumnValue('userKey')){
				if($print){
					Debug::print("{$f} key was already defined");
				}
				return $this->getColumnValue('userKey');
			}elseif($this->hasUserData()){
				if($print){
					Debug::print("{$f} key was not defined, but user object is");
				}
				$key = $this->getUserData()->getIdentifierValue();
			}else{
				if($this->hasColumn("userKey")){
					Debug::print("{$f} yes, a user key column exists");
				}else{
					Debug::print("{$f} user key column does not exist");
					Debug::printArray($this->getColumnNames());
				}
				Debug::error("{$f} user and parent objects are both undefined");
			}
			if($this->hasColumn('userKey')){
				return $this->setUserKey($key);
			}elseif($print){
				Debug::print("{$f} returning \"{$key}\"");
			}
			return $key;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setUserKey(string $key): string{
		return $this->setColumnValue('userKey', $key);
	}

	public function hasUserKey():bool{
		if($this->hasColumn("userKey")){
			return $this->hasColumnValue('userKey');
		}
		return $this->hasUserData() && $this->getUserData()->hasIdentifierValue();
	}

	public function getUserKeyCommand():GetColumnValueCommand{
		return new GetColumnValueCommand($this, "userKey");
	}

	public function setUserAccountType(string $type):string{
		return $this->setColumnValue('userAccountType', $type);
	}

	public function getUserAccountType():string{
		if(
			$this->hasColumn("userAccountType") 
			&& !$this->getColumn("userAccountType") instanceof VirtualDatum 
			&& $this->hasColumnValue("userAccountType")
		){
			return $this->getColumnValue("userAccountType");
		}
		$type = $this->getUserData()->getAccountType();
		if($this->hasConcreteColumn("userAccountType")){
			return $this->setUserAccountType($type);
		}
		return $type;
	}

	public function hasUserAccountType():bool{
		return $this->hasColumnValue("userAccountType");
	}

	public function getUserAccountTypeCommand():GetColumnValueCommand{
		return new GetColumnValueCommand($this, "userAccountType");
	}

	/**
	 *
	 * @param UserData $user
	 * @return UserData
	 */
	public function setUserData(UserData $user):UserData{
		$f = __METHOD__;
		try{
			$print = false;
			if(!isset($user)){
				Debug::error("{$f} null user object");
				$this->setObjectStatus(ERROR_NULL_USER_OBJECT);
			}
			if($this->hasColumn('userKey') && $user->hasIdentifierValue()){
				$ck = $user->getIdentifierValue();
				$this->setUserKey($ck);
			}
			if($this->hasColumn("userAccountType") && $user->hasSubtype()){
				$this->setUserAccountType($user->getSubtype());
			}elseif($print){
				if(!$this->hasColumn("userAccountType")){
					Debug::print("{$f} this ".$this->getDebugString()." does not have a userAccountType columns");
				}
				if(!$user->hasSubtype()){
					Debug::print("{$f} user ".$user->getDebugString()." does not have a subtype");
				}
			}
			if(!$user->isUninitialized()){
				if($print){
					$status = $user->getObjectStatus();
					$err = ErrorMessage::getResultMessage($status);
					Debug::print("{$f} user data is not uninitialized; status is \"{$err}\"");
				}
				if($this->hasColumn("userMasterAccountKey") && $user->hasColumn("masterAccountKey") && $user->hasMasterAccountKey()){
					if($print){
						Debug::print("{$f} about to call getUserMasterAccountKey()");
					}
					$mak = $user->getMasterAccountKey();
					if($print){
						Debug::print("{$f} returned from getUserMasterAccountKey()");
					}
					$this->setUserMasterAccountKey($mak);
				}
				if($this->hasColumn("userHardResetCount") && $user->hasColumn('hardResetCount')){
					$resets = $user->getUserHardResetCount();
					$this->setUserHardResetCount($resets);
				}
			}elseif($print){
				Debug::print("{$f} user is uninitialized");
			}
			$this->setForeignDataStructure('userKey', $user);
			return $this->getUserData();
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function acquireUserData(mysqli $mysqli):?UserData{
		$f = __METHOD__;
		if($this->hasUserData()){
			return $this->getUserData();
		}elseif(!$this->hasColumn("userKey")){
			Debug::error("{$f} userKey datum is undefined");
		}
		$user = $this->acquireForeignDataStructure($mysqli, "userKey");
		return $this->setUserData($user);
	}

	public function hasUserData():bool{
		return $this->hasForeignDataStructure('userKey');
	}

	/**
	 *
	 * @return UserData
	 */
	public function getUserData():UserData{
		$f = __METHOD__;
		if(!$this->hasUserData()){
			$dsc = $this->getShortClass();
			$key = $this->getIdentifierValue();
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} user data is undefined for {$dsc} with key \"{$key}\"; declared {$decl}");
		}
		return $this->getForeignDataStructure('userKey');
	}
}
