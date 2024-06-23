<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;

trait HumanReadableNameTrait{

	protected $humanReadableName;

	public function hasHumanReadableName(){
		return isset($this->humanReadableName);
	}

	public function setHumanReadableName($hrvn){
		if($this->hasHumanReadableName()){
			$this->release($this->humanReadableName);
		}
		return $this->humanReadableName = $this->claim($hrvn);
	}

	public function getHumanReadableName(){
		$f = __METHOD__;
		if(!isset($this->humanReadableName)){
			if($this instanceof Datum){
				$cn = $this->getName();
				$dsc = $this->getDataStructureClass();
				Debug::error("{$f} human readable name is undefined for column \"{$cn}\" of class \"{$dsc}\"");
			}
			Debug::error("{$f} human readable name is undefined");
		}
		return $this->humanReadableName;
	}
}
