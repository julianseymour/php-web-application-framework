<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait CallbackTrait{

	protected $callback;

	public function setCallback($callback){
		if($this->hasCallback()){
			$this->release($this->callback);
		}
		return $this->callback = $this->claim($callback);
	}

	public function getCallback(){
		$f = __METHOD__;
		if(!$this->hasCallback()){
			Debug::error("{$f} callback is undefined");
		}
		return $this->callback;
	}

	public function hasCallback():bool{
		return isset($this->callback);
	}
}
