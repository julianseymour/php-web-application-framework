<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\index;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

trait IndexDefiningTrait{

	protected $indexDefinition;

	public function setIndexDefinition($indexDefinition){
		$f = __METHOD__;
		if(!$indexDefinition instanceof IndexDefinition){
			Debug::error("{$f} input parameter must be an index definition");
		}elseif($this->hasIndexDefintion()){
			$this->release($this->indexDefinition);
		}
		return $this->indexDefinition = $this->claim($indexDefinition);
	}

	public function hasIndexDefintion():bool{
		return isset($this->indexDefinition);
	}

	public function getIndexDefinition(){
		$f = __METHOD__;
		if(!$this->hasIndexDefintion()){
			Debug::error("{$f} index definition is undefined");
		}
		return $this->indexDefinition;
	}
}