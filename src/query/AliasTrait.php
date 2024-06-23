<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait AliasTrait{

	protected $alias;

	public function setAlias($alias){
		$f = __METHOD__;
		if(!is_string($alias)){
			Debug::error("{$f} alias must be a string");
		}elseif(empty($alias)){
			Debug::error("{$f} alias must not be an empty string");
		}elseif($this->hasAlias()){
			$this->release($this->alias);
		}
		return $this->alias = $this->claim($alias);
	}

	public function hasAlias():bool{
		return isset($this->alias) && is_string($this->alias) && !empty($this->alias);
	}

	public function as($alias){
		$this->setAlias($alias);
		return $this;
	}

	public function getAlias(){
		return $this->alias;
	}
}