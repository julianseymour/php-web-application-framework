<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;

class DataTypeDatum extends StringEnumeratedDatum
{

	protected $foreignKeyName;

	public function __construct(string $name = "dataType")
	{
		parent::__construct($name);
	}

	public function setForeignKeyName(?string $name): ?string
	{
		$f = __METHOD__; //DataTypeDatum::getShortClass()."(".static::getShortClass().")->setForeingKeyName()";
		if ($name == null) {
			unset($this->foreignKeyName);
			return null;
		} elseif (! is_string($name)) {
			Debug::error("{$f} foreign key name must be a string");
		}
		return $this->foreignKeyName = $name;
	}

	public function hasForeignKeyName(): bool
	{
		return isset($this->foreignKeyName);
	}

	public function getForeignKeyName(): string
	{
		$f = __METHOD__; //DataTypeDatum::getShortClass()."(".static::getShortClass().")->getForeignKeyName()";
		if (! $this->hasForeignKeyName()) {
			Debug::error("{$f} foreign key name is undefined");
		}
		return $this->foreignKeyName;
	}

	public function withForeignKeyName(?string $name): DataTypeDatum
	{
		$this->setForeignKeyName($name);
		return $this;
	}

	public function hasValidEnumerationMap(): bool
	{
		$f = __METHOD__; //DataTypeDatum::getShortClass()."(".static::getShortClass().")->hasValidEnumerationMap()";
		$print = false;
		if ($this->hasForeignKeyName()) {
			if ($print) {
				Debug::print("{$f} foreign key name is \"{$this->foreignKeyName}\"");
			}
			$foreignKey = $this->getDataStructure()->getColumn($this->getForeignKeyName());
			return $foreignKey->hasForeignDataStructureClass() || $foreignKey->hasForeignDataStructureClassResolver();
		} elseif ($print) {
			Debug::print("{$f} foreign key name is undefined; returning parent function");
		}
		return parent::hasValidEnumerationMap();
	}

	public function getValidEnumerationMap(): array
	{
		$f = __METHOD__; //DataTypeDatum::getShortClass()."(".static::getShortClass().")->getValidEnumerationMap()";
		$print = false;
		if ($this->hasForeignKeyName()) {
			if ($print) {
				Debug::print("{$f} foreign key name is \"{$this->foreignKeyName}\"");
			}
			$foreignKey = $this->getDataStructure()->getColumn($this->getForeignKeyName());
			if ($foreignKey->hasForeignDataStructureClass()) {
				if ($print) {
					Debug::print("{$f} foreign key datum has its foreign data structure class");
				}
				return [
					$foreignKey->getForeignDataStructureClass()::getDataType()
				];
			} elseif ($foreignKey->hasForeignDataStructureClassResolver()) {
				if ($print) {
					Debug::print("{$f} foreign key datum has its class resolver");
				}
				return array_keys($foreignKey->getForeignDataStructureClassResolver()::getIntersections());
			} elseif ($print) {
				Debug::print("{$f} foreign key datum has neither a foreign data structure class nor a class resolver");
			}
		} elseif ($print) {
			Debug::print("{$f} foreign key name is undefined; returning parent function");
		}
		return parent::getValidEnumerationMap();
	}
}
