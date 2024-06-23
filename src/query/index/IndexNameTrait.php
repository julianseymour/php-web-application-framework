<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\index;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait IndexNameTrait{

	protected $indexName;

	public function setIndexName($indexName){
		if($this->hasIndexName()){
			$this->release($this->indexName);
		}
		return $this->indexName = $this->claim($indexName);
	}

	public function hasIndexName():bool{
		return isset($this->indexName);
	}

	public function getIndexName(){
		$f = __METHOD__;
		if(!$this->hasIndexName()){
			Debug::error("{$f} index name is undefined");
		}
		return $this->indexName;
	}
}
