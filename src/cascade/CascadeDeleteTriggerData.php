<?php
namespace JulianSeymour\PHPWebApplicationFramework\cascade;

use function JulianSeymour\PHPWebApplicationFramework\f;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\UniversalDataClassResolver;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignMetadataBundle;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;

class CascadeDeleteTriggerData extends DataStructure
{

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void
	{
		parent::declareColumns($columns, $ds);
		$instigator = new ForeignMetadataBundle("instigator", $ds);
		$instigator->setRelationshipType(RELATIONSHIP_TYPE_ONE_TO_ONE);
		$instigator->setForeignDataStructureClassResolver(UniversalDataClassResolver::class);
		$instigator->constrain();
		static::pushTemporaryColumnsStatic($columns, $instigator);
	}

	public static function getPrettyClassName(?string $lang = null)
	{
		ErrorMessage::unimplemented(f());
	}

	public static function getTableNameStatic(): string
	{
		return "cascade_delete_triggers";
	}

	public static function getDataType(): string
	{
		return DATATYPE_CASCADE_DELETE;
	}

	public static function getPrettyClassNames(?string $lang = null)
	{
		ErrorMessage::unimplemented(f());
	}

	public static function getPhylumName(): string
	{
		return "cascadeDeleteTriggers";
	}

	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void
	{
		parent::reconfigureColumns($columns, $ds);
		$keep = [
			$ds->getIdentifierName(),
			"instigatorKey",
			"instigatorDataType",
			"instigatorSubtype"
		];
		foreach ($columns as $name => $column) {
			if (! in_array($name, $keep, true)) {
				$column->volatilize();
			}
		}
		$columns['instigatorSubtype']->setNullable(true);
	}

	public function hasInstigatorKey(): bool
	{
		return $this->hasColumnValue("instigatorKey");
	}

	public function setInstigatorKey(string $value): string
	{
		return $this->setColumnValue("instigatorKey", $value);
	}

	public function getInstigatorKey(): string
	{
		return $this->getColumnValue("instigatorKey");
	}

	public function ejectInstigatorKey(): ?string
	{
		return $this->ejectColumnValue("instigatorKey");
	}

	public function hasInstigatorData(): bool
	{
		return $this->hasForeignDataStructure("instigatorKey");
	}

	public function setInstigatorData(DataStructure $struct): DataStructure
	{
		return $this->setForeignDataStructure("instigatorKey", $struct);
	}

	public function getInstigatorData(): DataStructure
	{
		return $this->getForeignDataStructure("instigatorKey");
	}

	public function ejectInstgiatorData(): ?DataStructure
	{
		return $this->ejectForeignDataStructure("instigatorKey");
	}

	public function hasInstigatorDataType(): bool
	{
		return $this->hasColumnValue("instigatorDataType");
	}

	public function setInstigatorDataType(string $value): string
	{
		return $this->setColumnValue("instigatorDataType", $value);
	}

	public function getInstigatorDataType(): string
	{
		return $this->getColumnValue("instigatorDataType");
	}

	public function ejectInstigatorDataType(): ?string
	{
		return $this->ejectColumnValue("instigatorDataType");
	}

	public function hasInstigatorSubtype(): bool
	{
		return $this->hasColumnValue("instigatorSubtype");
	}

	public function setInstigatorSubtype(string $value): string
	{
		return $this->setColumnValue("instigatorSubtype", $value);
	}

	public function getInstigatorSubtype(): string
	{
		return $this->getColumnValue("instigatorSubtype");
	}

	public function ejectInstigatorSubtype(): ?string
	{
		return $this->ejectColumnValue("instigatorSubtype");
	}
}
