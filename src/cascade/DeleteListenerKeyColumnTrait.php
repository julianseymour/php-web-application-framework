<?php

namespace JulianSeymour\PHPWebApplicationFramework\cascade;

use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnDefiningTrait;

/**
 * A trait for DataStructure classes that listen for the deletion of a foreign data structure to which this object does not have a direct reference.
 * @author j
 *
 */
trait DeleteListenerKeyColumnTrait{

	use MultipleColumnDefiningTrait;

	public function setDeleteListenerKey(string $value): string
	{
		return $this->setColumnValue("deleteListenerKey", $value);
	}

	public function hasDeleteListenerKey(): bool
	{
		return $this->hasColumnValue("deleteListenerKey");
	}

	public function getDeleteListenerKey(): string
	{
		return $this->getColumnValue("deleteListenerKey");
	}

	public function ejectDeleteListenerKey(): ?string
	{
		return $this->ejectColumnValue("deleteListenerKey");
	}

	public function setDeleteListenerData(CascadeDeleteTriggerData $struct): CascadeDeleteTriggerData
	{
		return $this->setForeignDataStructure("deleteListenerKey", $struct);
	}

	public function hasDeleteListenerData(): bool
	{
		return $this->hasForeignDataStructure("deleteListenerKey");
	}

	public function getDeleteListenerData(): CascadeDeleteTriggerData
	{
		return $this->getForeignDataStructure("deleteListenerKey");
	}

	public function ejectDeleteListenerData(): ?CascadeDeleteTriggerData
	{
		return $this->ejectForeignDataStructure("deleteListenerKey");
	}

	protected static function declareDeleteListenerKeyColumn(?string $name = null): ForeignKeyDatum
	{
		if ($name === null) {
			$name = "deleteListenerKey";
		}
		$delete_key = new ForeignKeyDatum($name, RELATIONSHIP_TYPE_MANY_TO_ONE);
		$delete_key->setForeignDataStructureClass(CascadeDeleteTriggerData::class);
		$delete_key->onUpdate(REFERENCE_OPTION_CASCADE);
		$delete_key->onDelete(REFERENCE_OPTION_CASCADE);
		$delete_key->constrain();
		return $delete_key;
	}
}
