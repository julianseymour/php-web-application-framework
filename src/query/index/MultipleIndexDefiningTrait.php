<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\index;

use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;

/**
 * XXX TODO this is used only by DataStructure
 * @author j
 *
 */
trait MultipleIndexDefiningTrait{

	use ArrayPropertyTrait;

	public function setIndexDefinitions($indexDefinitions){
		return $this->setArrayProperty("indexDefinitions", $indexDefinitions);
	}

	public function hasIndexDefinitions():bool{
		return $this->hasArrayProperty("indexDefinitions");
	}

	public function pushIndexDefinitions(...$indexDefinitions):int{
		return $this->pushArrayProperty("indexDefinitions", ...$indexDefinitions);
	}

	public function mergeIndexDefinitions($indexDefinitions){
		return $this->mergeArrayProperty("indexDefinitions", $indexDefinitions);
	}

	public function getIndexDefinitions(){
		return $this->getProperty("indexDefinitions");
	}

	public function withIndexDefinitions($indexDefinitions){
		$this->setIndexDefinitions($indexDefinitions);
		return $this;
	}
}
