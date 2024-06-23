<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\user;

use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\common\MultipleNameChangesTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;

class RenameUserStatement extends QueryStatement{

	use MultipleDatabaseUserDefinitionsTrait;
	use MultipleNameChangesTrait;

	public function __construct(){
		parent::__construct();
		$this->requirePropertyType("users", RenameUserDefinition::class);
	}

	public function getNameChanges(){
		$f = __METHOD__;
		if($this->hasUsers()){
			$usernames = [];
			foreach($this->getUsers() as $user){
				$usernames[$user->getUsernameHostString()] = $user->getNewUsernameHostString();
			}
			if($this->hasNameChanges()){
				$usernames = array_merge($usernames, $this->nameChanges);
			}
			return $usernames;
		}elseif(!$this->hasNameChanges()){
			Debug::error("{$f} usernames are undefined");
		}
		return $this->nameChanges;
	}

	public function getQueryStatementString():string{
		// RENAME USER old_user TO new_user [, old_user TO new_user] ...
		$string = "rename user ";
		$i = 0;
		foreach($this->getNameChanges() as $oldname => $newname){
			if($i ++ > 0){
				$string .= ",";
			}
			$string .= "{$oldname} to {$newname}";
		}
		return $string;
	}

	public function dispose(bool $deallocate=false): void{
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		parent::dispose($deallocate);
		$this->release($this->propertyTypes, $deallocate);
		$this->release($this->nameChanges, $deallocate);
	}
}
