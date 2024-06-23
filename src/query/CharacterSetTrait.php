<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait CharacterSetTrait{

	protected $characterSet;

	public function setCharacterSet($set){
		if($this->hasCharacterSet()){
			$this->release($this->characterSet);
		}
		return $this->characterSet = $this->claim($set);
	}

	public function hasCharacterSet():bool{
		return isset($this->characterSet);
	}

	public function getCharacterSet(){
		$f = __METHOD__;
		if(!$this->hasCharacterSet()){
			Debug::error("{$f} character set is undefined");
		}
		return $this->characterSet;
	}
}
