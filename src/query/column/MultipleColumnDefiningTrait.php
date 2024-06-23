<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\column;

use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\HasColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\AbstractNumericDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\EnumeratedDatumInterface;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\KeyListDatum;
use JulianSeymour\PHPWebApplicationFramework\event\ReleaseChildNodeEvent;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\account\login\FullAuthenticationData;
use JulianSeymour\PHPWebApplicationFramework\datum\SerialNumberDatum;
use JulianSeymour\PHPWebApplicationFramework\account\UsernameData;

/**
 * Trait for anything with multiple column definitions stored in an array.
 *
 * @author j
 */
trait MultipleColumnDefiningTrait{

	use ArrayPropertyTrait;

	public function reportColumns(): void{
		$f = __METHOD__;
		try{
			if(!$this->hasColumns()){
				Debug::print("{$f} no columns to report");
			}
			foreach($this->getColumns() as $name => $column){
				$v = $column->hasValue() ? $column->getValue() : "[undefined]";
				Debug::print("{$f} {$name} : {$v}");
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function withColumns(?array $columns): object{
		$this->setColumns($columns);
		return $this;
	}

	public function hasColumns(): bool{
		return $this->hasArrayProperty("columns");
	}

	/**
	 *
	 * @return Datum[]
	 */
	public function getColumns(): array{
		return $this->getProperty("columns");
	}
	
	public function setColumns(?array $columns):array{
		$f = __METHOD__;
		if($this instanceof DataStructure){
			Debug::error("{$f} DataStructure should override this function");
		}
		return $this->setArrayProperty("columns", $columns);
	}

	public function pushColumn(...$columns): int{
		return $this->pushArrayProperty("columns", ...$columns);
	}

	public function getColumnCount(): int{
		return $this->getArrayPropertyCount("columns");
	}

	public function getColumnNames(): array{
		return $this->getArrayPropertyKeys("columns");
	}

	public function hasColumn(string $column_name): bool{
		return $this->hasArrayPropertyKey("columns", $column_name);
	}

	/**
	 *
	 * @param string $column_name
	 * @return Datum
	 */
	public function getColumn(string $column_name): Datum{
		$f = __METHOD__;
		try{
			if(!$this->hasColumn($column_name)){
				Debug::warning("{$f} column \"{$column_name}\" is undefined for this ".$this->getDebugString());
				$columns = $this->getArrayPropertyKeys("columns");
				Debug::printArray($columns);
				Debug::printStackTrace();
			}
			// Debug::print("{$f} returning normally");
			return $this->getArrayPropertyValue("columns", $column_name);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function protectColumns(bool $value=true, ...$keys){
		$f = __METHOD__;
		if(!$this->hasColumns()){
			Debug::error("{$f} columns have not been allocated yet for this ".$this->getDebugString());
		}elseif(!isset($keys) || count($keys) === 0){
			$keys = $this->getColumnNames();
		}
		foreach($keys as $name){
			$this->getColumn($name)->setDisableDeallocationFlag($value);
		}
		return $value;
	}
	
	/**
	 * add $value to numeric datum at column $field
	 *
	 * @param string $field
	 * @param int|double $value
	 */
	public function addColumnValue(string $field, $value){
		$f = __METHOD__;
		try{
			if(!$this->hasColumn($field)){
				$did = $this->getDebugId();
				$decl = $this->getDeclarationLine();
				return Debug::error("{$f} datum \"{$field}\" does not exist for object with debug Id \"{$did}\", declared {$decl}");
			}
			$datum = $this->getColumn($field);
			$primitive = $datum->getTypeSpecifier();
			if($primitive !== 'i' && $primitive !== 'd'){
				Debug::error("{$f} illegal static binding primitive \"{$primitive}\"");
			}
			return $datum->addValue($value);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function incrementColumnValue(string $column_name){
		return $this->addColumnValue($column_name, 1);
	}

	public function subtractColumnValue(string $field, $value){
		return $this->addColumnValue($field, $value * - 1);
	}

	public function decrementColumnValue(string $column_name){
		return $this->subtractColumnValue($column_name, 1);
	}

	public function setColumnValue(string $field, $value){
		$f = __METHOD__;
		try{
			// Debug::print("{$f} entered; about to call getColumn({$field})");
			$datum = $this->getColumn($field);
			// Debug::print("{$f} returned from getColumn({$field})");
			if(!isset($datum)){
				Debug::warning("{$f} datum \"{$field}\" is undefined");
				$this->debugPrintColumns();
				Debug::error("{$f} exit");
			}
			$value = $datum->setValue($value);
			if(!isset($value) && ! $datum->isNullable()){
				// Debug::warning("{$f} setValue for datum \"{$field}\" returned null");
			}
			// Debug::print("{$f} returning normally");
			return $value;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function hasOriginalColumnValue(string $column_name): bool{
		return $this->getColumn($column_name)->hasValue();
	}

	public function getOrignalColumnValue(string $column_name){
		return $this->getColumn($column_name)->getOriginalValue();
	}

	public function getColumnValueCommand($column_name): GetColumnValueCommand{
		return new GetColumnValueCommand($this, $column_name);
	}

	public function columnToString(string $column_name): string{
		$f = __METHOD__;
		if(!$this->hasColumn($column_name)){
			Debug::error("{$f} datum \"{$column_name}\" does not exist");
		}
		return $this->getColumn($column_name)->__toString();
	}

	public function hasConcreteColumn(string $column_name): bool{
		if(!$this->hasColumn($column_name)){
			return false;
		}
		$datum = $this->getColumn($column_name);
		return !$datum instanceof VirtualDatum && $datum->getPersistenceMode() === PERSISTENCE_MODE_DATABASE;
	}

	public function debugPrintColumns(?string $msg = null, bool $exit = true): void{
		if(isset($msg)){
			Debug::warning($msg);
		}
		foreach($this->getColumns() as $c){
			if($c instanceof VirtualDatum){
				continue;
			}
			$column_name = $c->getName();
			$value = $c->getHumanReadableValue();
			Debug::print("{$column_name}: \"{$value}\"");
		}
		if($exit){
			Debug::printStackTrace();
		}
	}

	public function getColumnValue(string $column_name){
		$f = __METHOD__;
		$print = false;
		$datum = $this->getColumn($column_name);
		if($datum == null){
			Debug::error("{$f} datum is undefined");
		}elseif($print){
			$value = $datum->getValue();
			Debug::print("{$f} returning \"{$value}\"");
		}
		return $datum->getValue();
	}

	public function hasColumnValue(string $column_name): bool{
		$f = __METHOD__;
		$print = false;
		if(!$this->hasColumn($column_name)){
			if($print){
				Debug::print("{$f} this object lacks a datum \"{$column_name}\"");
			}
			return false;
		}
		$datum = $this->getColumn($column_name);
		if($print){
			if($datum->hasValue()){
				Debug::print("{$f} yes, this object has a value at column \"{$column_name}\"");
			}else{
				Debug::print("{$f} no, this object does not have a value at column \"{$column_name}\"");
			}
		}
		return $datum->hasValue();
	}

	public function sumColumnValues(...$column_names){
		$f = __METHOD__;
		if(empty($column_names)){
			Debug::error("{$f} column names array is empty");
		}
		$sum = 0;
		foreach($column_names as $cn){
			$column = $this->getColumn($cn);
			if(!$column instanceof AbstractNumericDatum){
				Debug::error("{$f} column \"{$cn}\" is not numeric");
			}elseif(!$column->hasValue() && ! $column->hasDefaultValue()){
				Debug::error("{$f} column \"{$cn}\" lacks a value");
			}
			$sum += $column->getValue();
		}
		return $sum;
	}

	public function hasColumnValueCommand($column_name): HasColumnValueCommand{
		return new HasColumnValueCommand($this, $column_name);
	}

	public function getFilteredColumnNames(...$filters): array{
		return array_keys($this->getFilteredColumns(...$filters));
	}

	public function unsetFilteredColumns(...$filters): int{
		foreach($this->getFilteredColumns(...$filters) as $column){
			$column->unsetValue();
		}
		return SUCCESS;
	}

	/**
	 * Unset the values at the provided indices.
	 * If the column is not nullable, it is skipped.
	 *
	 * @param string[] $column_names
	 * @return int
	 */
	public function unsetColumnValues(...$column_names): int{
		$f = __METHOD__;
		$print = false;
		$force = false;
		if(!empty($column_names)){
			if($print){
				Debug::print("{$f} column names are defined");
			}
			$first = array_keys($column_names)[0];
			if(is_array($column_names[$first])){
				$column_names = $column_names[$first];
			}
			$force = true;
		}else{
			if($print){
				Debug::print("{$f} column names undefined, assuming you want to destroy everything");
			}
			$column_names = $this->getColumnNames();
		}
		if($print){
			Debug::print("{$f} about to unset the following columns:");
			Debug::printArray($column_names);
			// Debug::printStackTrace();
		}
		foreach($column_names as $column_name){
			$column = $this->getColumn($column_name);
			if($column instanceof VirtualDatum){
				if($print){
					Debug::print("{$f} column \"{$column_name}\" is a virtual datum");
				}
				continue;
			}elseif($print){
				Debug::print("{$f} unsetting column \"{$column_name}\"");
			}
			$column->unsetValue($force);
			if(!$column instanceof BooleanDatum && ! $column instanceof EnumeratedDatumInterface){
				if($this->hasColumnValue($column_name)){
					Debug::error("{$f} Datum->unset doesn't work -- column \"{$column_name}\" still has a value");
				}
			}
		}
		return SUCCESS;
	}

	public function getFilteredColumnCount(...$filters): int{
		return count($this->getFilteredColumns(...$filters));
	}

	public function regenerateColumns($column_names): int{
		$f = __METHOD__;
		$print = false;
		if(empty($column_names)){
			Debug::error("{$f} indices array is empty");
		}
		foreach($column_names as $column_name){
			if(!$this->hasColumn($column_name)){
				Debug::error("{$f} this object does not have a datum at column \"{$column_name}\"");
			}elseif($print){
				Debug::print("{$f} about to regenerate datum at column \"{$column_name}\"");
			}
			$datum = $this->getColumn($column_name);
			$datum->regenerate();
		}
		if($print){
			Debug::print("{$f} returning normally");
		}
		return SUCCESS;
	}

	/**
	 * set datum values indexed in array
	 *
	 * @param array $arr
	 * @return int
	 */
	public function processArray(array $arr): int{
		$f = __METHOD__;
		$print = false;
		foreach($arr as $key => $value){
			if(!$this->hasColumn($key)){
				Debug::error("{$f} invalid column \"{$key}\"");
			}elseif($print){
				Debug::print("{$f} about to set column \"{$key}\" to \"{$value}\"");
			}
			$this->setColumnValue($key, $value);
		}
		return SUCCESS;
	}

	public function hasDatabaseColumn(string $cn): bool{
		return $this->hasColumn($cn) && $this->getColumn($cn)->getPersistenceMode() === PERSISTENCE_MODE_DATABASE;
	}

	public function ejectColumnValue(string $field){
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} ejecting value from column \"{$field}\"");
		}
		return $this->getColumn($field)->ejectValue();
	}

	public function getOriginalColumnValue(string $column_name){
		return $this->getColumn($column_name)->getOriginalValue();
	}

	public function getFilteredColumns(...$filters): array{
		$columns = $this->getColumns();
		$ret = [];
		foreach($columns as $column_name => $column){
			if($column->applyFilter(...$filters)){
				$ret[$column_name] = $column;
			}
		}
		return $ret;
	}

	public function hasKeyListDatum($phylum){
		$f = __METHOD__;
		$print = false;
		if($print){
			if(!$this->hasColumn($phylum)){
				Debug::print("{$f} no datum at index \"{$phylum}\"");
			}elseif(!$this->getColumn($phylum) instanceof KeyListDatum){
				Debug::print("{$f} datum at index \"{$phylum}\" is not a child key list");
			}
		}
		return $this->hasColumn($phylum) && $this->getColumn($phylum) instanceof KeyListDatum;
	}
	
	public function releaseColumns(bool $deallocate, ...$names){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if(!$this->hasColumns()){
			Debug::error("{$f} no columns");
		}
		if(!isset($names) || count($names) === 0){
			$names = $this->getColumnNames();
		}
		if($print){
			Debug::print("{$f} about to release the following columns:");
			Debug::printArray($names);
		}
		foreach($names as $name){
			if($print){
				Debug::print("{$f} about to release column \"{$name}\" with debug string ".$this->getColumn($name)->getDebugString()."...");
			}
			$this->releaseColumn($name, $deallocate);
		}
	}
	
	public function releaseColumn(string $name, bool $deallocate=false){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if(!$this->hasColumn($name)){
			Debug::error("{$f} column {$name} doesn't exist for this ".$this->getDebugString());
		}
		$datum = $this->getColumn($name);
		if($this->hasAnyEventListener(EVENT_RELEASE_CHILD)){
			$this->dispatchEvent(new ReleaseChildNodeEvent($name, $datum, $deallocate));
		}
		if($print){
			Debug::print("{$f} about to release columnd {$name}");
		}
		$this->releaseArrayPropertyKey('columns', $name, $deallocate);
	}
}
