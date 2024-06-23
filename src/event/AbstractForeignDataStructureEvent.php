<?php

namespace JulianSeymour\PHPWebApplicationFramework\event;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;

abstract class AbstractForeignDataStructureEvent extends Event{
	
	public function __construct(?string $type=null, ?string $columnName=null, ?DataStructure $struct=null, ?array $properties = null){
		if($properties === null){
			$properties = [];
		}
		if($columnName !== null){
			$properties['columnName'] = $columnName;
		}
		if($struct !== null){
			$properties['data'] = $struct;
			if($struct instanceof DataStructure && $struct->hasIdentifierValue()){
				$properties['foreignKey'] = $struct->getIdentifierValue(); 
			}
		}
		parent::__construct($type, $properties);
	}
	
	public function hasColumnName():bool{
		return $this->hasProperty('columnName');
	}
	
	public function getColumnName(): ?string{
		$f = __METHOD__;
		if(!$this->hasColumnName()){
			Debug::error("{$f} column name is undefined");
		}
		return $this->getProperty("columnName");
	}
	
	public function hasForeignDataStructure():bool{
		return $this->hasProperty('data');
	}
	
	public function getForeignDataStructure(){
		$f = __METHOD__;
		iF(!$this->hasForeignDataStructure()){
			Debug::error("{$f} foreign data structure is undefined");
		}
		return $this->getProperty("data");
	}
	
	public function hasForeignKey():bool{
		return $this->hasProperty("foreignKey");
	}
	
	public function getForeignKey(){
		$f = __METHOD__;
		if(!$this->hasForeignKey()){
			Debug::error("{$f} foreign key is undefined");
		}
		return $this->getProperty("foreignKey");
	}
}
