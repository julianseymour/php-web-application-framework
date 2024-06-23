<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\table\alter;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\ConstrainableTrait;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\Constraint;
use JulianSeymour\PHPWebApplicationFramework\query\partition\PartitionDefinition;
use JulianSeymour\PHPWebApplicationFramework\query\table\FullTableNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\charset\CharacterSetOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\charset\ConvertToCharacterSetOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\column\AddColumnOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\column\ChangeColumnOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\column\DropColumnDefaultOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\column\DropColumnOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\column\ModifyColumnOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\column\OrderByOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\column\RenameColumnOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\column\SetColumnDefault;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\column\SetColumnVisibilityOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\constraint\AddConstraintOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\constraint\AlterConstraintOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\constraint\DropConstraintOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\constraint\DropForeignKeyOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\index\AddIndexOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\index\AlterIndexOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\index\DisableKeysOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\index\DropIndexOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\index\DropPrimaryKeyOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\index\EnableKeysOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\index\RenameIndexOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition\AddPartitionOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition\AnalyzePartitionOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition\CheckPartitionOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition\CoalescePartitionOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition\DiscardPartitionTablespacePartition;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition\DropPartitionOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition\ExchangePartitionOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition\ImportPartitionTablespaceOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition\OptimizePartitionOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition\RebuildPartitionOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition\RemovePartitioningOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition\ReorganizePartitionOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition\RepairPartitionOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\partition\TruncatePartitionOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\tablespace\DiscardTablespaceOption;
use JulianSeymour\PHPWebApplicationFramework\query\table\alter\tablespace\ImportTablespaceOption;
use Exception;

class AlterTableStatement extends QueryStatement implements StaticPropertyTypeInterface{

	use ConstrainableTrait;
	use FullTableNameTrait;
	use StaticPropertyTypeTrait;
	
	public function __construct(...$dbtable){
		parent::__construct();
		$this->unpackTableName($dbtable);
	}
	
	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array{
		return [
			"alterOptions" => AlterOption::class,
			"constraints" => Constraint::class,
			"partitionOptions", AlterOption::class
		];
	}
	
	public function setAlterOptions($options):?array{
		return $this->setArrayProperty("alterOptions", $options);
	}

	public function pushAlterOptions(...$options):int{
		return $this->pushArrayProperty("alterOptions", ...$options);
	}

	public function hasAlterOptions():bool{
		return $this->hasArrayProperty("alterOptions");
	}

	public function getAlterOptions(){
		return $this->getProperty("alterOptions");
	}

	public function mergeAlterOptions($options):array{
		return $this->mergeArrayProperty("alterOptions", $options);
	}

	public function addColumn(Datum $columnDefinition, $position = null, $afterColumnName = null): AlterTableStatement{
		return $this->withAlterOption(new AddColumnOption($columnDefinition, $position, $afterColumnName));
	}

	public function addColumns(...$columnDefinitions):AlterTableStatement{
		$f = __METHOD__;
		if(!isset($columnDefinitions)){
			Debug::error("{$f} received null parameter");
		}
		return $this->withAlterOption(AddColumnOption::addColumns(...$columnDefinitions));
	}

	public function changeColumn($oldColumnName, $newColumnDefinition):AlterTableStatement{
		return $this->withAlterOption(new ChangeColumnOption($oldColumnName, $newColumnDefinition));
	}

	public function dropColumn($columnName):AlterTableStatement{
		return $this->withAlterOption(new DropColumnOption($columnName));
	}

	public function dropColumns(...$columnNames):AlterTableStatement{
		$f = __METHOD__;
		if(!isset($columnNames)){
			Debug::error("{$f} received null parameter");
		}
		foreach($columnNames as $columnName){
			$this->pushAlterOption(new DropColumnOption($columnName));
		}
		return $this;
	}

	public function withAlterOption($option):AlterTableStatement{
		$this->pushAlterOption($option);
		return $this;
	}

	public function addIndex($indexDefinition):AlterTableStatement{
		return $this->withAlterOption(new AddIndexOption($indexDefinition));
	}

	public function alterIndex($indexName, $visibility):AlterTableStatement{
		return $this->withAlterOption(new AlterIndexOption($indexName, $visibility));
	}

	public function dropIndex($indexName):AlterTableStatement{
		return $this->withAlterOption(new DropIndexOption($indexName));
	}

	public function renameIndex($oldName, $newName):AlterTableStatement{
		return $this->withAlterOption(new RenameIndexOption($oldName, $newName));
	}

	public function dropColumnDefault($columnName):AlterTableStatement{
		return $this->withAlterOption(new DropColumnDefaultOption($columnName));
	}

	public function modifyColumn($columnDefinition):AlterTableStatement{
		return $this->withAlterOption(new ModifyColumnOption($columnDefinition));
	}

	public function renameColumn($oldName, $newName):AlterTableStatement{
		return $this->withAlterOption(new RenameColumnOption($oldName, $newName));
	}

	public function setColumnDefault($columnName, $default):AlterTableStatement{
		return $this->withAlterOption(new SetColumnDefault($columnName, $default));
	}

	public function setColumnVisibility($columnName, $visibility):AlterTableStatement{
		return $this->withAlterOption(new SetColumnVisibilityOption($columnName, $visibility));
	}

	public function characterSet($charset, $collationName = null):AlterTableStatement{
		return $this->withAlterOption(new CharacterSetOption($charset, $collationName));
	}

	public function convertToCharacterSet($charset, $collationName = null):AlterTableStatement{
		return $this->withAlterOption(new ConvertToCharacterSetOption($charset, $collationName));
	}

	public function addConstraint($constraint):AlterTableStatement{
		return $this->withAlterOption(new AddConstraintOption($constraint));
	}

	public function alterConstraint($symbol, $enforcement):AlterTableStatement{
		return $this->withAlterOption(new AlterConstraintOption($symbol, $enforcement));
	}

	public function dropConstraint($symbol):AlterTableStatement{
		return $this->withAlterOption(new DropConstraintOption($symbol));
	}

	public function dropForeignKey($symbol):AlterTableStatement{
		return $this->withAlterOption(new DropForeignKeyOption($symbol));
	}

	public function rename($newTableName):AlterTableStatement{
		return $this->withAlterOption(new RenameTableOption($newTableName));
	}

	public function dropPrimaryKey():AlterTableStatement{
		return $this->withAlterOption(new DropPrimaryKeyOption());
	}

	public function disableKeys():AlterTableStatement{
		return $this->withAlterOption(new DisableKeysOption());
	}

	public function enableKeys():AlterTableStatement{
		return $this->withAlterOption(new EnableKeysOption());
	}

	public function discardTablespace():AlterTableStatement{
		return $this->withAlterOption(new DiscardTablespaceOption());
	}

	public function importTablespace():AlterTableStatement{
		return $this->withAlterOption(new ImportTablespaceOption());
	}

	public function algorithm($algorithm = ALGORITHM_DEFAULT){
		return $this->withAlterOption(new AlgorithmOption($algorithm));
	}

	public function force():AlterTableStatement{
		return $this->withAlterOption(new ForceOption());
	}

	public function lock($lock = LOCK_OPTION_DEFAULT):AlterTableStatement{
		return $this->withAlterOption(new LockOption($lock));
	}

	public function reorderBy(...$columnNames):AlterTableStatement{
		return $this->withAlterOption(new OrderByOption(...$columnNames));
	}

	public function withValidation():AlterTableStatement{
		return $this->withAlterOption(new SetValidationOption(true));
	}

	public function withoutValidation():AlterTableStatement{
		return $this->withAlterOption(new SetValidationOption(false));
	}

	public static function getStatementTypeString(): string{
		return "alter table";
	}

	protected function getStatementCommandString(): string{
		ErrorMessage::unimplemented(__METHOD__);
	}

	public function setPartitionOptions($options):?array{
		return $this->setArrayProperty("partitionOptions", $options);
	}

	public function pushPartitionOptions(...$options):int{
		return $this->pushArrayProperty("partitionOptions", ...$options);
	}

	public function hasPartitionOptions():bool{
		return $this->hasArrayProperty("partitionOptions");
	}

	public function getPartitionOptions(){
		return $this->getProperty("partitionOptions");
	}

	public function mergePartitionOptions($options):array{
		return $this->mergeArrayProperty("partitionOptions", $options);
	}

	public function withPartitionOption($option):AlterTableStatement{
		$this->pushPartitionOption($option);
		return $this;
	}

	public function addPartition(PartitionDefinition $partitionDefinition):AlterTableStatement{
		return $this->withPartitionOption(new AddPartitionOption($partitionDefinition));
	}

	public function analyzePartition($partitionNames = null):AlterTableStatement{
		return $this->withPartitionOption(new AnalyzePartitionOption($partitionNames));
	}

	public function checkPartition($partitionNames = null):AlterTableStatement{
		return $this->withPartitionOption(new CheckPartitionOption($partitionNames));
	}

	public function coalescePartition($number):AlterTableStatement{
		return $this->withPartitionOption(new CoalescePartitionOption($number));
	}

	public function discardPartitionTablespace($partitionNames = null):AlterTableStatement{
		return $this->withPartitionOption(new DiscardPartitionTablespacePartition($partitionNames));
	}

	public function dropPartition($partitionNames):AlterTableStatement{
		return $this->withPartitionOption(new DropPartitionOption($partitionNames));
	}

	public function exchangePartition($partitionName, $dbName, $tableName, $validate = null):AlterTableStatement{
		return $this->withPartitionOption(new ExchangePartitionOption($partitionName, $dbName, $tableName, $validate));
	}

	public function importPartitionTablespace($partitionNames = null):AlterTableStatement{
		return $this->withPartitionOption(new ImportPartitionTablespaceOption($partitionNames));
	}

	public function optimizePartition($partitionNames = null):AlterTableStatement{
		return $this->withPartitionOption(new OptimizePartitionOption($partitionNames));
	}

	public function rebuildPartition($partitionNames = null):AlterTableStatement{
		return $this->withPartitionOption(new RebuildPartitionOption($partitionNames));
	}

	public function removePartitioning():AlterTableStatement{
		return $this->withPartitionOption(new RemovePartitioningOption());
	}

	public function reorganizePartition($partitionNames, $partitionDefinitions):AlterTableStatement{
		return $this->withPartitionOption(new ReorganizePartitionOption($partitionNames, $partitionDefinitions));
	}

	public function repairPartitionOption($partitionNames = null):AlterTableStatement{
		return $this->withPartitionOption(new RepairPartitionOption($partitionNames));
	}

	public function truncatePartition($partitionNames = null):AlterTableStatement{
		return $this->withPartitionOption(new TruncatePartitionOption($partitionNames));
	}

	public function getQueryStatementString():string{
		$f = __METHOD__;
		try{
			if(!$this->hasAlterOptions()){
				Debug::error("{$f} no options defined");
			}
			$string = "alter table ";
			if($this->hasDatabaseName()){
				$string .= back_quote($this->getDatabaseName()) . ".";
			}
			$string .= back_quote($this->getTableName()) . " " . implode(',', $this->getAlterOptions());
			if($this->hasPartitionOptions()){
				$string .= implode(',', $this->getPartitionOptions());
			}
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->databaseName, $deallocate);
		$this->release($this->tableName, $deallocate);
	}
}
