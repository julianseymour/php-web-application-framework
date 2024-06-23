<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\column;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait VisibilityTrait{

	protected $visibility;

	public function setVisibility($v){
		$f = __METHOD__;
		if(!is_string($v)){
			Debug::error("{$f} visiblity must be a string");
		}
		$v = strtolower($v);
		switch($v){
			case VISIBILITY_VISIBLE:
			case VISIBLITY_INVISIBLE:
				break;
			default:
				Debug::error("{$f} invalid visibility \"{$v}\"");
		}
		if($this->hasVisibility()){
			$this->release($this->visibility);
		}
		return $this->visibility = $this->claim($v);
	}

	public function hasVisibility():bool{
		return isset($this->visibility);
	}

	public function getVisibility(){
		$f = __METHOD__;
		if(!$this->hasVisibility()){
			Debug::error("{$f} visibility is undefined");
		}
		return $this->visibility;
	}
}
