<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait CollatedTrait{

	protected $collationName;

	public function setCollationName($cn){
		if($this->hasCollationName()){
			$this->release($this->collationName);
		}
		return $this->collationName = $this->claim($cn);
	}

	public function hasCollationName():bool{
		return isset($this->collationName);
	}

	public function getCollationName(){
		$f = __METHOD__;
		if(!$this->hasCollationName()){
			Debug::error("{$f} collation name is undefined");
		}
		return $this->collationName;
	}
}