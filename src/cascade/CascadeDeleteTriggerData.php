<?php

namespace JulianSeymour\PHPWebApplicationFramework\cascade;

use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignMetadataBundle;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\StaticTableNameTrait;
use JulianSeymour\PHPWebApplicationFramework\datum\PseudokeyDatum;

/**
 * This class is used to automatically delete data structures when another data structure that they are dependent on is deleted, but not directly referenced. 
 * Instead, the cascade delete data gets deleted as part of the delete() function, and that deletion cascades to all listeners.
 * @author j
 *
 */
class CascadeDeleteTriggerData extends DataStructure implements StaticTableNameInterface{

	use StaticTableNameTrait;
	
	public static function getDatabaseNameStatic():string{
		return "cascading";
	}
	
	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		//parent::declareColumns($columns, $ds);
		$uniqueKey = new PseudokeyDatum("uniqueKey");
		$uniqueKey->setUniqueFlag(true);
		$uniqueKey->setIndexFlag(true);
		$instigator = new ForeignMetadataBundle("instigator", $ds);
		$instigator->setRelationshipType(RELATIONSHIP_TYPE_ONE_TO_ONE);
		$instigator->setForeignDataStructureClassResolver(CascadeDeletableClassResolver::class);
		$instigator->constrain();
		array_push($columns, $uniqueKey, $instigator);
	}

	public static function getPrettyClassName():string{
		ErrorMessage::unimplemented(f());
	}

	public static function getTableNameStatic(): string{
		return "delete_triggers";
	}

	public static function getDataType(): string{
		return DATATYPE_CASCADE_DELETE;
	}

	public static function getPrettyClassNames():string{
		ErrorMessage::unimplemented(f());
	}

	public static function getPhylumName(): string{
		return "cascadeDeleteTriggers";
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::reconfigureColumns($columns, $ds);
		$keep = [
			$ds->getIdentifierName(),
			"instigatorKey",
			"instigatorDataType",
			"instigatorSubtype"
		];
		foreach($columns as $name => $column){
			if(!in_array($name, $keep, true)){
				$column->volatilize();
			}
		}
		$columns['instigatorSubtype']->setNullable(true);
	}

	public function hasInstigatorKey(): bool{
		return $this->hasColumnValue("instigatorKey");
	}

	public function setInstigatorKey(string $value): string{
		return $this->setColumnValue("instigatorKey", $value);
	}

	public function getInstigatorKey(): string{
		return $this->getColumnValue("instigatorKey");
	}

	public function ejectInstigatorKey(): ?string{
		return $this->ejectColumnValue("instigatorKey");
	}

	public function hasInstigatorData(): bool{
		return $this->hasForeignDataStructure("instigatorKey");
	}

	public function setInstigatorData(DataStructure $struct): DataStructure{
		return $this->setForeignDataStructure("instigatorKey", $struct);
	}

	public function getInstigatorData(): DataStructure{
		return $this->getForeignDataStructure("instigatorKey");
	}

	public function ejectInstgiatorData(): ?DataStructure{
		return $this->ejectForeignDataStructure("instigatorKey");
	}

	public function hasInstigatorDataType(): bool{
		return $this->hasColumnValue("instigatorDataType");
	}

	public function setInstigatorDataType(string $value): string{
		return $this->setColumnValue("instigatorDataType", $value);
	}

	public function getInstigatorDataType(): string{
		return $this->getColumnValue("instigatorDataType");
	}

	public function ejectInstigatorDataType(): ?string{
		return $this->ejectColumnValue("instigatorDataType");
	}

	public function hasInstigatorSubtype(): bool{
		return $this->hasColumnValue("instigatorSubtype");
	}

	public function setInstigatorSubtype(string $value): string{
		return $this->setColumnValue("instigatorSubtype", $value);
	}

	public function getInstigatorSubtype(): string{
		return $this->getColumnValue("instigatorSubtype");
	}

	public function ejectInstigatorSubtype(): ?string{
		return $this->ejectColumnValue("instigatorSubtype");
	}
}
