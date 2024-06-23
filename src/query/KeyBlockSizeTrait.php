<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait KeyBlockSizeTrait{

	protected $keyBlockSizeValue;

	public function setKeyBlockSize($size){
		if($this->hasKeyBlockSize()){
			$this->release($this->keyBlockSizeValue);
		}
		return $this->keyBlockSizeValue = $this->claim($size);
	}

	public function hasKeyBlockSize():bool{
		return isset($this->keyBlockSizeValue);
	}

	public function getKeyBlockSize(){
		$f = __METHOD__;
		if(!$this->hasKeyBlockSize()){
			Debug::error("{$f} key block size is undefined");
		}
		return $this->keyBlockSizeValue;
	}
}
