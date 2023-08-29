<?php

namespace JulianSeymour\PHPWebApplicationFramework\error;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait ErrorMessageTrait{
	
	protected $errorMessage;
	
	public function hasErrorMessage():bool{
		return isset($this->errorMessage);
	}
	
	public function getErrorMessage():string{
		$f = __METHOD__;
		if(!$this->hasErrorMessage()){
			Debug::error("{$f} error message is undefined");
		}
		return $this->errorMessage;
	}
	
	public function setErrorMessage(?string $msg):?string{
		if($msg == null){
			unset($this->errorMessage);
			return null;
		}
		return $this->errorMessage = $msg;
	}
	
	public function ejectErrorMessage():?string{
		$ret = $this->getErrorMessage();
		unset($this->errorMessage);
		return $ret;
	}
}
