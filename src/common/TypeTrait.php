<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait TypeTrait{

	protected $type;

	public function setType($type){
		if($this->hasType()){
			$this->release($this->type);
		}
		return $this->type = $this->claim($type);
	}

	public function hasType():bool{
		return isset($this->type);
	}

	public function getType(){
		$f = __METHOD__;
		if(!$this->hasType()){
			Debug::error("{$f} type is undefined");
		}
		return $this->type;
	}

	public function withType($type): object{
		$this->setType($type);
		return $this;
	}
}
