<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait NewNameTrait{

	protected $newName;

	public function setNewName($n):string{
		$f = __METHOD__;
		if(!is_string($n)){
			Debug::error("{$f} new name must be a string");
		}elseif($this->hasNewName()){
			$this->release($this->newName);
		}
		return $this->newName = $this->claim($n);
	}

	public function hasNewName():bool{
		return !empty($this->newName);
	}

	public function getNewName():string{
		$f = __METHOD__;
		if(!$this->hasNewName()){
			Debug::error("{$f} new table name is undefined");
		}
		return $this->newName;
	}
}
