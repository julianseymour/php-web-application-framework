<?php

namespace JulianSeymour\PHPWebApplicationFramework\data;

use JulianSeymour\PHPWebApplicationFramework\cascade\DeleteListenerKeyColumnTrait;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignMetadataBundle;
use JulianSeymour\PHPWebApplicationFramework\query\database\StaticDatabaseNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameInterface;

class GlobalIndexData extends DataStructure 
implements StaticDatabaseNameInterface, StaticTableNameInterface{
	
	use DeleteListenerKeyColumnTrait;
	
	public static function getKeyGenerationMode(): int{
		return KEY_GENERATION_MODE_UNIDENTIFIABLE;
	}
	
	public static function declareColumns(array& $columns, ?DataStructure $ds=null) : void{
		$foreign = new ForeignMetadataBundle("indexed", $ds);
		$foreign->setRelationshipType(RELATIONSHIP_TYPE_ONE_TO_ONE);
		$foreign->constrain(false);
		$foreign->setNullable(false);
		$listener = static::declareDeleteListenerKeyColumn();
		array_push($columns, $foreign, $listener);
	}
	
	public static function getDataType(): string{
		return DATATYPE_GLOBAL_INDEX;
	}
	
	public function hasIndexedData():bool{
		return $this->hasForeignDataStructure("indexedKey");
	}
	
	public function getIndexedData() : DataStructure{
		return $this->getForeignDataStructure("indexedKey");
	}
	
	public function setIndexedData(DataStructure $ds) : DataStructure{
		return $this->setForeignDataStructure("indexedKey", $ds);
	}
	
	public static function getDatabaseNameStatic(): string{
		return "data";
	}

	public static function getTableNameStatic(): string{
		return "global_index";
	}

}
