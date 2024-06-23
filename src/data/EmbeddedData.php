<?php

namespace JulianSeymour\PHPWebApplicationFramework\data;

use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\query\database\StaticDatabaseNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\database\StaticDatabaseNameTrait;

class EmbeddedData extends DataStructure implements StaticDatabaseNameInterface{

	use NamedTrait;
	use StaticDatabaseNameTrait;
	
	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$joinKey = new ForeignKeyDatum("joinKey");
		$joinKey->setNullable(false);
		$joinKey->onDelete(REFERENCE_OPTION_CASCADE);
		$joinKey->onUpdate(REFERENCE_OPTION_CASCADE);
		$joinKey->setRelationshipType(RELATIONSHIP_TYPE_MANY_TO_ONE);
		$joinKey->constrain();
		$joinKey->setIndexFlag(true);
		if(!BACKWARDS_REFERENCES_ENABLED){
			$joinKey->setRank(RANK_PARENT);
		}
		array_push($columns, $joinKey);
	}

	public static function getPrettyClassName():string{
		return _("Embedded data");
	}

	public static function getIdentifierNameStatic(): ?string{
		return "joinKey";
	}

	public static function getKeyGenerationMode(): int{
		return KEY_GENERATION_MODE_NATURAL;
	}

	public function isRegistrable(): bool{
		return false;
	}

	public static function isRegistrableStatic(): bool{
		return false;
	}

	public static function getDataType(): string{
		return DATATYPE_EMBEDDED;
	}

	public static function getPrettyClassNames(): string{
		return static::getPrettyClassName();
	}

	public static function getPhylumName(): string{
		return CONST_ERROR;
	}

	public function getTableName(): string{
		$f = __METHOD__;
		$print = false;
		if($print){
			$ret = $this->getSubsumingObject()->getTableName() . "_" . $this->getName();
			Debug::print("{$f} returning \"{$ret}\"");
		}
		return $this->getSubsumingObject()->getTableName() . "_" . $this->getName();
	}

	public function setJoinKey($value): string{
		return $this->setColumnValue("joinKey", $value);
	}

	public function getJoinKey(): string{
		if(!$this->hasJoinKey() && $this->hasSubsumingObject()){
			return $this->setJoinKey($this->getSubsumingObject()
				->getIdentifierValue());
		}
		return $this->getColumnValue("joinKey");
	}

	public function hasJoinKey(): bool{
		return $this->getColumnValue("joinKey");
	}

	public static final function getDatabaseNameStatic(): string{
		return "embedded";
	}

	public function setSubsumingObject(?DataStructure $so): DataStructure{
		$f = __METHOD__;
		return $this->setForeignDataStructure("joinKey", $so);
	}

	public static function getPermissionStatic(string $name, $data){
		switch($name){
			case DIRECTIVE_INSERT:
			case DIRECTIVE_UPDATE:
			case DIRECTIVE_CREATE_TABLE:
				return SUCCESS;
			default:
				return parent::getPermissionStatic($name, $data);
		}
	}

	public final function getEmbeddedDataStructures(): ?array{
		return null;
	}

	protected function beforeSetForeignDataStructureHook(string $column_name, DataStructure $struct): int{
		$f = __METHOD__;
		$print = false;
		if($column_name === "joinKey"){
			if(!$struct instanceof DataStructure){
				Debug::error("{$f} subsuming object must be a DataStructure");
			}elseif($struct instanceof EmbeddedData){
				Debug::error("{$f} you cannot do nested embedded data");
			}
			$this->getColumn("joinKey")->setForeignDataStructureClass($struct->getClass());
			/*
			$this->getColumn("joinKey")->onDelete(REFERENCE_OPTION_CASCADE);
			$this->getColumn("joinKey")->onUpdate(REFERENCE_OPTION_CASCADE);
			$this->getColumn("joinKey")->constrain();
			$this->getColumn("joinKey")->setIndexFlag(true);
			*/
			if($struct->hasIdentifierValue()){
				$this->setJoinKey($struct->getIdentifierValue());
			}
		}
		return parent::beforeSetForeignDataStructureHook($column_name, $struct);
	}

	public function hasSubsumingObject(): bool{
		return $this->hasForeignDataStructure("joinKey");
	}

	public function getSubsumingObject(): DataStructure{
		$f = __METHOD__;
		if(!$this->hasSubsumingObject()){
			Debug::error("{$f} subsuming object is undefined");
		}
		return $this->getForeignDataStructure("joinKey");
	}
}
