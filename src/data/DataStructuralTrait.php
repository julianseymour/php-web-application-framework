<?php

namespace JulianSeymour\PHPWebApplicationFramework\data;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;

trait DataStructuralTrait{

	/**
	 * the DataStructure that hosts this datum as one of its columns
	 *
	 * @var DataStructure
	 */
	protected $dataStructure;

	/**
	 *
	 * @param DataStructure $obj
	 * @return DataStructure
	 */
	public function setDataStructure($obj){
		if($obj == null) {
			unset($this->dataStructure);
			return null;
		}
		return $this->dataStructure = $obj;
	}

	/**
	 *
	 * @return DataStructure
	 */
	public function getDataStructure(){
		$f = __METHOD__;
		if(!$this->hasDataStructure()) {
			if($this instanceof Datum) {
				$column_name = $this->getName();
				Debug::error("{$f} data structure is undefined for column \"{$column_name}\"");
			}
			Debug::error("{$f} data structure is undefined");
		}
		return $this->dataStructure;
	}

	public function hasDataStructure():bool{
		return isset($this->dataStructure) && is_object($this->dataStructure);
	}
}
