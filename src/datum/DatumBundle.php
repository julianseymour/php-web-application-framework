<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;

/**
 * An object that generates multiple datums with the function geneerateComponents
 *
 * @author j
 *        
 */
abstract class DatumBundle extends AbstractDatum
{

	use ArrayPropertyTrait;
	use NamedTrait;

	public abstract function generateComponents(?DataStructure $ds = null): array;

	public function __construct(?string $name = null, ?DataStructure $ds = null)
	{
		parent::__construct();
		if (! empty($name)) {
			$this->setName($name);
		}
	}

	/*
	 * public function setVolatileColumnNames(?array $column_names):?array{
	 * return $this->setArrayProperty("volatileColumnNames", $column_names);
	 * }
	 *
	 * public function hasVolatileColumnNames():bool{
	 * return $this->hasArrayProperty("volatileColumnNames");
	 * }
	 *
	 * public function getVolatileColumnNames():array{
	 * return $this->getProperty("volatileColumnNames");
	 * }
	 *
	 * public function setNullableColumnNames(?array $column_names):?array{
	 * return $this->setArrayProperty("nullableColumnNames", $column_names);
	 * }
	 *
	 * public function hasNullableColumnNames():bool{
	 * return $this->hasArrayProperty("nullableColumnNames");
	 * }
	 *
	 * public function getNullableColumnNames():array{
	 * return $this->getProperty("nullableColumnNames");
	 * }
	 *
	 * /*public function getComponents(?DataStructure $ds):?array{
	 * $f = __METHOD__; //DatumBundle::getShortClass()."(".static::getShortClass().")->getComponents()";
	 * $print = false;
	 * if($this->hasComponents()){
	 * return $this->getProperty("components");
	 * }
	 * $components = $this->generateComponents($ds);
	 * if($this->hasNullableColumnNames() || $this->hasVolatileColumnNames()){
	 * $nullable = $this->hasNullableColumnNames() ? $this->getNullableColumnNames() : null;
	 * $volatile = $this->hasVolatileColumnNames() ? $this->getVolatileColumnNames() : null;
	 * foreach($components as $component){
	 * $name = $component->getColumnName();
	 * if($nullable !== null){
	 * if(false !== array_search($name, $nullable)){
	 * $component->setNullable(true);
	 * }
	 * }
	 * if($volatile !== null){
	 * if(false !== array_search($name, $volatile)){
	 * $component->volatilize();
	 * }
	 * }
	 * }
	 * }
	 * return $this->setComponents($components);
	 * }
	 */
	public function getArrayKey($i)
	{
		return $this->getName();
	}

	public function setComponents(?array $components): ?array
	{
		return $this->setArrayProperty("components", $components);
	}

	public function hasComponents(): bool
	{
		return $this->hasArrayProperty("components");
	}

	public function getComponent($name): Datum
	{
		return $this->getArrayPropertyValue("components", $name);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->name);
	}
}
