<?php

namespace JulianSeymour\PHPWebApplicationFramework\auth\password;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait PasswordTrait{
	
	protected $password;
	
	public function hasPassword():bool{
		return !empty($this->password);
	}
	
	public function getPassword(){
		$f = __METHOD__;
		if(!$this->hasPassword()){
			Debug::error("{$f} password is undefined");
		}
		return $this->password;
	}
	
	public function setPassword($password){ // XXX validate password
		$f = __METHOD__;
		if(!is_string($password)){
			Debug::error("{$f} password must be a string");
		}elseif($this->hasPassword()){
			$this->release($this->password);
		}
		return $this->password = $this->claim($password);
	}
}