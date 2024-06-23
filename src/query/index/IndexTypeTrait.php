<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\index;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use function JulianSeymour\PHPWebApplicationFramework\release;

trait IndexTypeTrait{

	protected $indexType;

	public function setIndexType($type){
		$f = __METHOD__;
		if(!is_string($type)){
			Debug::error("{$f} index type must be a string");
		}
		$type = strtolower($type);
		switch($type){
			case INDEX_TYPE_BTREE:
			case INDEX_TYPE_FULLTEXT:
			case INDEX_TYPE_HASH:
			case INDEX_TYPE_SPATIAL:
				break;
			default:
				Debug::error("{$f} invalid index type \"{$type}\"");
		}
		if($this->hasIndexType()){
			$this->release($this->indexType);
		}
		return $this->indexType = $this->claim($type);
	}

	public function getIndexType(){
		$f = __METHOD__;
		if(!$this->hasIndexType()){
			Debug::error("{$f} index type is undefined");
		}
		return $this->indexType;
	}

	public function hasIndexType():bool{
		return isset($this->indexType);
	}

	public function withIndexType($type){
		$this->setIndexType($type);
		return $this;
	}
}