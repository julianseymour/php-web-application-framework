<?php

namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait MultipleNameChangesTrait{

	protected $nameChanges;

	public function rename($oldname, $newname){
		if(!$this->hasNameChanges()){
			$this->nameChanges = [];
		}
		return $this->nameChanges[$oldname] = $this->claim($newname);
	}

	public function hasNameChange($key):bool{
		if(!isset($this->nameChanges) || !is_array($this->nameChanges) || empty($this->nameChanges)){
			return false;
		}
		return array_key_exists($key, $this->nameChanges);
	}
	
	public function hasNameChanges(...$keys){
		if(!isset($this->nameChanges) || !is_array($this->nameChanges) || empty($this->nameChanges)){
			return false;
		}elseif(!isset($keys)){
			return true;
		}
		foreach($keys as $key){
			if(!$this->hasNameChange($key)){
				return false;
			}
		}
		return true;
	}

	public function setNameChanges(?array $nameChanges):?array{
		$f = __METHOD__;
		foreach($nameChanges as $oldname => $newname){
			if(!is_string($oldname) || ! is_string($newname)){
				Debug::error("{$f} both old and new names must be strings");
			}
		}
		if($this->hasNameChanges()){
			$this->release($this->nameChanges);
		}
		return $this->nameChanges = $this->claim($nameChanges);
	}

	public function getNameChanges(){
		$f = __METHOD__;
		if(!$this->hasNameChanges()){
			Debug::error("{$f} name changes are undefined");
		}
		return $this->nameChanges;
	}
}
