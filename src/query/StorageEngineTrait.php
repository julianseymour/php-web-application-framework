<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait StorageEngineTrait{

	protected $storageEngineName;

	public function engine($name){
		$this->setStorageEngine($name);
		return $this;
	}

	public function setStorageEngine($name){
		$f = __METHOD__;
		if(!is_string($name)){
			Debug::error("{$f} storage engine name must be a string");
		}elseif($this->hasStorageEngine()){
			$this->release($this->storageEngineName);
		}
		return $this->storageEngineName = $this->claim($name);
	}

	public function hasStorageEngine():bool{
		return isset($this->storageEngineName);
	}

	public function getStorageEngine(){
		$f = __METHOD__;
		if(!$this->hasStorageEngine()){
			Debug::error("{$f} storage engine name is undefined");
		}
		return $this->storageEngineName;
	}
}