<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\user;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\role\DatabaseRoleData;

class RenameUserDefinition extends DatabaseRoleData{

	protected $newUsername;

	protected $newHost;

	public function setNewUsername($name){
		$f = __METHOD__;
		if(!is_string($name)){
			Debug::error("{$f} ");
		}elseif($this->hasNewUsername()){
			$this->release($this->newUsername);
		}
		return $this->newUsername = $this->claim($name);
	}

	public function hasNewUsername():bool{
		return isset($this->newUsername);
	}

	public function getNewUsername(){
		if(!$this->hasNewUsername()){
			return $this->getUsername();
		}
		return $this->newUsername;
	}

	public function setNewHost($host){
		$f = __METHOD__;
		if(!is_string($host)){
			Debug::error("{$f} new host must be a string");
		}elseif($this->hasNewHost()){
			$this->release($this->newHost);
		}
		return $this->newHost = $this->claim($host);
	}

	public function hasNewHost():bool{
		return isset($this->newHost);
	}

	public function getNewHost(){
		if(!$this->hasNewHost()){
			return $this->getHost();
		}
		return $this->newHost;
	}

	public function getArrayKey(int $count){
		return $this->getUsernameHostString();
	}

	public function getNewUsernameHostString():string{
		$user = single_quote($this->getNewUsername());
		$host = single_quote($this->getNewHost());
		return "'{$user}'@'{$host}'";
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->newHost, $deallocate);
		$this->release($this->newUsername, $deallocate);
	}
}