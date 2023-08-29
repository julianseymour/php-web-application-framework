<?php
namespace JulianSeymour\PHPWebApplicationFramework\data;

use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use mysqli;

class GenericData extends DataStructure{

	public function generateInsertTimestamp(){
		return time();
	}

	public function preventDuplicateEntry(mysqli $mysqli): int{
		return SUCCESS;
	}

	public function setOneToManyAssociativeDataKey($index){
		return $this->dataStructureIndexKey = $index;
	}

	public function getOneToManyAssociativeDataKey(){
		return $this->dataStructureIndexKey;
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
	}

	public static function getDataType(): string{
		return DATATYPE_UNKNOWN;
	}

	public static function getPrettyClassName():string{
		return _("Generic data");
	}

	public static function getPrettyClassNames():string{
		return static::getPrettyClassName();
	}

	public static function getTableNameStatic(): string{
		ErrorMessage::unimplemented(__METHOD__);
	}

	public function setOneToManyAssociativeData($mapper){
		$this->setDatabaseName($mapper->getDatabaseName());
		$this->setTableName($mapper->getTableName());
		return $this->setForeignDataStructure($this->getOneToManyAssociativeDataKey(), $mapper);
	}

	public static function getPhylumName(): string{
		return "data";
	}
	
	public static function getDefaultPersistenceModeStatic():int{
		return PERSISTENCE_MODE_UNDEFINED;
	}
}
