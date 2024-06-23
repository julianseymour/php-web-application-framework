<?php

namespace JulianSeymour\PHPWebApplicationFramework\form;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait SuccessAndErrorCallbacksTrait{
	
	/**
	 * name of javascript function to call after the user's browser receives a successful response
	 *
	 * @var string
	 */
	protected $successCallback;
	
	/**
	 * name of javascript function to call if something goes wrong
	 *
	 * @var string
	 */
	protected $errorCallback;
	
	public function hasErrorCallback(): bool{
		return isset($this->errorCallback);
	}
	
	public function hasSuccessCallback(): bool{
		return isset($this->successCallback);
	}
	
	public function setSuccessCallback(?string $cb): ?string{
		if($this->hasSuccessCallback()){
			$this->release($this->successCallback);
		}
		return $this->successCallback = $this->claim($cb);
	}
	
	public function setErrorCallback(?string $cb): ?string{
		if($this->hasErrorCallback()){
			$this->release($this->errorCallback);
		}
		return $this->errorCallback = $this->claim($cb);
	}
	
	public function getErrorCallback(){
		$f = __METHOD__;
		if(!$this->hasErrorCallback()){
			Debug::error("{$f} error callback is undefined");
		}
		return $this->errorCallback;
	}
	
	public function getSuccessCallback(){
		$f = __METHOD__;
		if(!$this->hasSuccessCallback()){
			Debug::error("{$f} success callback is undefined");
		}
		return $this->successCallback;
	}
}