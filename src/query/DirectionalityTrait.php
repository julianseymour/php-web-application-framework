<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait DirectionalityTrait{

	protected $directionality;

	public function setDirectionality($directionality){
		$f = __METHOD__;
		if(!is_string($directionality)){
			Debug::error("{$f} directionality is must be a string");
		}
		$directionality = strtolower($directionality);
		switch($directionality){
			case DIRECTION_ASCENDING:
			case DIRECTION_DESCENDING:
				break;
			default:
				Debug::error("{$f} invalid directionality \"{$directionality}\"");
		}
		if($this->hasDirectionality()){
			$this->release($this->directionality);
		}
		return $this->directionality = $this->claim($directionality);
	}

	public function hasDirectionality():bool{
		return isset($this->directionality);
	}

	public function getDirectionality(){
		$f = __METHOD__;
		if(!$this->hasDirectionality()){
			Debug::error("{$f} directionality is undefined");
		}
		return $this->directionality;
	}

	public function asc(){
		$this->setDirectionality(DIRECTION_ASCENDING);
		return $this;
	}

	public function desc(){
		$this->setDirectionality(DIRECTION_DESCENDING);
		return $this;
	}
}
