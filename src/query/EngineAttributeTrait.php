<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait EngineAttributeTrait{

	protected $engineAttributeString;

	public function setEngineAttribute($attr){
		if($this->hasEngineAttribute()){
			$this->release($this->engineAttributeString);
		}
		return $this->engineAttributeString = $this->claim($attr);
	}

	public function hasEngineAttribute():bool{
		return isset($this->engineAttributeString);
	}

	public function getEngineAttribute(){
		$f = __METHOD__;
		if(!$this->hasEngineAttribute()){
			Debug::error("{$f} engine attribute is undefined");
		}
		return $this->engineAttributeString;
	}

	public function engineAttribute($attr)
	{
		$this->setEngineAttribute($attr);
		return $this;
	}
}