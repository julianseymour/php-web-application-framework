<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\index;

use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;

trait KeyPartsTrait{

	use ArrayPropertyTrait;

	public function setKeyParts($keyparts){
		return $this->setArrayProperty("keyParts", $keyparts);
	}

	public function pushKeyParts(...$keyparts){
		return $this->pushArrayProperty("keyParts", ...$keyparts);
	}

	public function hasKeyParts(){
		return $this->hasArrayProperty("keyParts");
	}

	public function getKeyParts(){
		return $this->getProperty("keyParts");
	}

	public function mergeKeyParts($keyparts){
		return $this->mergeArrayProperty("keyParts", $keyparts);
	}

	public function withKeyParts($keyparts){
		$this->setKeyParts($keyparts);
		return $this;
	}
}
