<?php

namespace JulianSeymour\PHPWebApplicationFramework\data;

use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\is_abstract;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetIdentifierNameCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\AfterCreateTableEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeCreateTableEvent;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use JulianSeymour\PHPWebApplicationFramework\query\TemporaryFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\query\TypeSpecificInterface;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnDefiningTrait;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\ConstrainableTrait;
use JulianSeymour\PHPWebApplicationFramework\query\database\DatabaseNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\index\MultipleIndexDefiningTrait;
use JulianSeymour\PHPWebApplicationFramework\query\select\RiggedSelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\table\CreateTableStatement;
use JulianSeymour\PHPWebApplicationFramework\query\table\TableNameTrait;
use Exception;
use mysqli;

trait TableDefinitionTrait{
	
	use ConstrainableTrait;
	use DatabaseNameTrait;
	use MultipleColumnDefiningTrait;
	use MultipleIndexDefiningTrait;
	use TableNameTrait;
	use TemporaryFlagBearingTrait;
	
	/**
	 * reorder columns in the order returned by getReorderedColumnIndices())
	 * If this object has a column that is not defined by getReorderedColumnIndices,
	 * they will go at the end in their initial order
	 *
	 * @param array $columns
	 * @return Datum[]
	 */
	public static final function reorderColumns(array $columns, ?array $order=null):?array{
		$f = __METHOD__;
		$reordered = [];
		foreach($order as $column_name){
			if(!array_key_exists($column_name, $columns)){
				Debug::error("{$f} column \"{$column_name}\" does not exist");
				continue;
			}
			$reordered[$column_name] = $columns[$column_name];
			unset($columns[$column_name]);
		}
		foreach(array_keys($columns) as $column_name){
			$reordered[$column_name] = $columns[$column_name];
			unset($columns[$column_name]);
		}
		$columns = null;
		return $reordered;
	}
	
	public function getTableName(): string{
		$f = __METHOD__;
		try{
			$print = false;
			if($this->hasTableName()){
				if($print){
					Debug::print("{$f} table name was directly assigned");
				}
				return $this->tableName;
			}elseif(!method_exists($this, 'getTableNameStatic')){
				Debug::error("{$f} table name for class ".$this->getShortClass()." cannot be determined statically");
			}
			if($print){
				Debug::print("{$f} table name was not already assigned");
			}
			$table = static::getTableNameStatic();
			if($print){
				Debug::print("{$f} returning \"{$table}\"");
			}
			return $this->setTableName($table);
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function getDatabaseName(): string{
		$f = __METHOD__;
		$print = false;
		if($this->hasDatabaseName()){
			if($print){
				Debug::print("{$f} database name was already assigned");
			}
			return $this->databaseName;
		}elseif(!method_exists($this, 'getDatabaseNameStatic')){
			Debug::error("{$f} database name cannot be determined statically");
		}
		return $this->setDatabaseName(static::getDatabaseNameStatic());
	}
	
	public function getCreateTableStatement(): CreateTableStatement{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		$create = CreateTableStatement::fromTableDefinition($this);
		if($print){
			Debug::print("{$f} returning the following: ".$create->toSQL());
		}
		return $create;
	}
	
	public static function getCreateTableStatementStatic(): CreateTableStatement{
		$f = __METHOD__;
		$dummy = new static();
		$columns1 = $dummy->getFilteredColumns(DIRECTIVE_CREATE_TABLE);
		$columns2 = [];
		foreach($columns1 as $column){
			$column->setDataStructureClass(static::class);
			$columns2[$column->getName()] = $column;
		}
		if(!method_exists(static::class, 'getTableNameStatic')){
			Debug::error("{$f} table name cannot be determined statically for class \"".static::getShortClass()."\"");
		}
		$create = new CreateTableStatement(
			static::getDatabaseNameStatic(),
			static::getTableNameStatic()
			);
		$create->withColumns(array_values($columns2));
		deallocate($dummy);
		return $create;
	}
	
	/**
	 * Creates a table in the database for this class.
	 * This function does not start a database transaction because create table automatically commits
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function createTable(mysqli $mysqli): int{
		$f = __METHOD__;
		try{
			$print = false;
			$status = SUCCESS;
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} permission returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			// pre-table creation hook
			$status = $this->beforeCreateTableHook($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} before create table hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			// create table
			$create = $this->getCreateTableStatement();
			if($print){
				Debug::print("{$f} create table statement is " . $create->toSQL());
			}
			$status = $create->executeGetStatus($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} executing table creation query returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			// post-create table hook
			$status = $this->afterCreateTableHook($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} after create table hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public static function createTableStatic(mysqli $mysqli):int{
		$data = new static();
		return $data->createTable($mysqli);
	}
	
	public function beforeCreateTableHook(mysqli $mysqli): int{ //XXX I can't imagine a situation where this would be useful
		if($this->hasAnyEventListener(EVENT_BEFORE_CREATE_TABLE)){
			$this->dispatchEvent(new BeforeCreateTableEvent());
		}
		return SUCCESS;
	}
	
	public function afterCreateTableHook(mysqli $mysqli): int{
		if($this->hasAnyEventListener(EVENT_AFTER_CREATE_TABLE)){
			$this->dispatchEvent(new AfterCreateTableEvent());
		}
		return SUCCESS;
	}
	
	public static function tableExistsStatic(mysqli $mysqli): bool{
		$f = __METHOD__;
		if(!method_exists(static::class, 'getTableNameStatic')){
			Debug::error("{$f} table name cannot be determined statically for class \"".static::getShortClass()."\"");
		}
		return QueryBuilder::tableExists($mysqli, static::getDatabaseNameStatic(), static::getTableNameStatic());
	}
	
	public function tableExists(mysqli $mysqli):bool{
		$f = __METHOD__;
		if($mysqli->connect_errno){
			Debug::error("{$f} Failed to connect to MySQL: ({$mysqli->connect_errno}){$mysqli->connect_error}");
		}elseif(!$mysqli->ping()){
			Debug::error("{$f} mysqli connection failed ping test: \"" . $mysqli->error . "\"");
		}
		return QueryBuilder::tableExists($mysqli, $this->getDatabaseName(), $this->getTableName());
	}
	
	public static function hasColumnStatic(string $column_name): bool{
		$class = static::class;
		$ds = new $class();
		$ret = $ds->hasColumn($column_name);
		deallocate($ds);
		return $ret;
	}
	
	/**
	 *
	 * @param Datum[] $columns
	 * @return Datum[]
	 */
	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null):void{}
	
	/**
	 *
	 * @param string $column_name
	 * @return Datum
	 */
	public static function getDatumClassStatic(string $column_name):string{
		$f = __METHOD__;
		try{
			$class = static::class;
			$ds = new $class();
			$ret = $ds->getColumn($column_name)->getClass();
			deallocate($ds);
			return $ret;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public static function getTypeSpecifierStatic(...$column_names): string{
		$dummy = new static();
		if(count($column_names) === 1 && is_array($column_names[0])){
			$column_names = $column_names[0];
		}
		$ret = $dummy->getTypeSpecifier(...$column_names);
		deallocate($dummy);
		return $ret;
	}
	
	/**
	 * return the type specifier string for a column, or multiple columns
	 * if you don't provide any column names it will return the type specifier for all columns
	 *
	 * @param string[] ...$column_names
	 * @return string
	 */
	public function getTypeSpecifier(...$column_names): string{
		$f = __METHOD__;
		try{
			$string = "";
			if(!isset($column_names) || empty($column_names)){
				$column_names = $this->getFilteredColumnNames(COLUMN_FILTER_DATABASE);
			}
			if(empty($column_names)){
				return $string;
			}
			foreach($column_names as $column_name){
				if(is_object($column_name)){
					if($column_name instanceof TypeSpecificInterface){
						$string .= $column_name->getTypeSpecifier();
					}else{
						Debug::error("{$f} column name is a ".$column_name->getDebugString());
					}
				}elseif(is_string($column_name)){
					$string .= $this->getColumn($column_name)->getTypeSpecifier();
				}else{
					Debug::error("{$f} column name is neither string nor type specifiying object");
				}
			}
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public function select(...$column_names): SelectStatement{
		return RiggedSelectStatement::fromTableDefinition($this, ...$column_names);
	}
	
	/**
	 * return an unconditional SelectStatement for columns $column_names from this class's database.tablename.
	 * If $column_names is empty, it assume you want to select everything.
	 * if one or more of the columns is embedded it will automatically generate join clauses to load them
	 * However, this function doesn't address columns stored in intersection tables
	 *
	 * @param string[] ...$column_names
	 * @return SelectStatement
	 */
	public static function selectStatic(?TableDefinitionInterface $that=null, ...$column_names): SelectStatement{
		$f = __METHOD__;
		try{
			$print = false && $that !== null && $that->getDebugFlag();
			$deallocate = false;
			if($that === null){
				$that = new static();
				$deallocate = true;
			}else{
				$that->disableDeallocation();
			}
			$select = RiggedSelectStatement::fromTableDefinition($that, ...$column_names);
			if($deallocate){
				deallocate($that);
			}else{
				$that->enableDeallocation();
			}
			if($print){
				$string = $select->toSQL();
				Debug::print("{$f} returning query statement \"{$string}\"");
			}
			return $select;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	public static function getColumnStatic(string $column_name): Datum{
		$f = __METHOD__;
		if(is_abstract(static::class)){
			Debug::error("{$f} cannot instantiate abstract class");
		}
		$dummy = new static();
		$column = $dummy->getColumn($column_name);
		$column->disableDeallocation();
		deallocate($dummy);
		$column->enableDeallocation();
		return $column;
	}
	
	/**
	 * debug function for automatically creating embedded and intersection tables
	 *
	 * @param mysqli $mysqli
	 * @return string
	 */
	public function createAssociatedTables(mysqli $mysqli): int{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			if($mysqli->connect_errno){
				Debug::error("{$f} Failed to connect to MySQL: ({$mysqli->connect_errno}){$mysqli->connect_error}");
			}elseif(!$mysqli->ping()){
				Debug::error("{$f} mysqli connection failed ping test: \"" . $mysqli->error . "\"");
			}
			if($this instanceof IntersectionData){
				Debug::error("{$f} don't call this on intersection data");
			}elseif(!$this->tableExists($mysqli)){
				Debug::warning("{$f} table doesn't exist, what's the point");
				return SUCCESS;
			}
			// tables for embedded columns
			$embedded = $this->getEmbeddedDataStructures();
			if(!empty($embedded)){
				if($print){
					$count = count($embedded);
					Debug::print("{$f} {$count} embedded data structures");
				}
				foreach($embedded as $e){
					$db = $e->getDatabaseName();
					$tableName = $e->getTableName();
					if(! QueryBuilder::tableExists($mysqli, $db, $tableName)){
						if($print){
							Debug::print("{$f} table {$db}.{$tableName} does not yet exist. It has the following columns:");
							$e->debugPrintColumns(null, false);
						}
						$status = $e->createTable($mysqli);
						if($status !== SUCCESS){
							$err = ErrorMessage::getResultMessage($status);
							Debug::error("{$f} creating embedded table \"{$db}.{$tableName}\" returned error status \"{$err}\"");
							return $status;
						}elseif($print){
							Debug::print("{$f} successfully created new embedded table \"{$db}.{$tableName}\"");
						}
					}elseif($print){
						Debug::print("{$f} embedded table \"{$db}.{$tableName}\" already exists");
					}
				}
				deallocate($embedded);
			}elseif($print){
				Debug::print("{$f} no embedded data structures");
			}
			// intersection tables for polymorphic foreign key columns
			$polys = $this->getFilteredColumns(COLUMN_FILTER_INTERSECTION);
			if(!empty($polys)){
				if($print){
					$count = count($polys);
					Debug::print("{$f} {$count} intersection tables");
				}
				foreach($polys as $name => $poly){
					if($print){
						Debug::print("{$f} creating intersection table for column \"{$name}\"");
					}
					$status = $poly->createIntersectionTables($mysqli);
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} creating intersection table for datum \"{$name}\" returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}elseif($print){
						Debug::print("{$f} successfully created intersection table for column \"{$name}\"");
					}
				}
			}elseif($print){
				Debug::print("{$f} no foreign keys stored in intersection tables");
			}
			// event source tables
			$fsc = $this->getFilteredColumns(COLUMN_FILTER_EVENT_SOURCE);
			if(!empty($fsc)){
				foreach($fsc as $name => $column){
					if($print){
						Debug::print("{$f} about to create intersection table for event source of column \"{$name}\"");
					}
					$event_src = new EventSourceData($column);
					if(!QueryBuilder::tableExists($mysqli, EventSourceData::getDatabaseNameStatic(), $event_src->getTableName())){
						$status = $event_src->createTable($mysqli);
						if($status !== SUCCESS){
							$err = ErrorMessage::getResultMessage($status);
							Debug::error("{$f} creating event source table \"{$db}.{$tableName}\" for column \"{$name}\" returned error status \"{$err}\"");
							return $status;
						}elseif($print){
							Debug::print("{$f} successfully created new event source table \"{$db}.{$tableName}\" for column \"{$name}\"");
						}
						$status = $event_src->createAssociatedTables($mysqli);
						if($status !== SUCCESS){
							$err = ErrorMessage::getResultMessage($status);
							Debug::error("{$f} creating associated tables for event source table \"{$db}.{$tableName}\" for column \"{$name}\" returned error status \"{$err}\"");
							return $status;
						}elseif($print){
							Debug::print("{$f} successfully created associated tables for event source table \"{$db}.{$tableName}\" for column \"{$name}\"");
						}
					}elseif($print){
						Debug::print("{$f} event source table \"{$db}.{$tableName}\" for column \"{$name}\" already exists");
					}
				}
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	/**
	 * override this the define which datums and in what order are used to generate hash keys,
	 * and which get checked for uniqueness anyway when using pseudokeys
	 *
	 * @return NULL
	 */
	public static function getCompositeUniqueColumnNames(): ?array{
		return null;
	}
	
	public function setIdentifierName(?string $idn): ?string{
		$f = __METHOD__;
		if($idn === null){
			unset($this->identifierName);
			return null;
		}elseif(!is_string($idn)){
			Debug::error("{$f} identifier name is not a string");
		}
		return $this->identifierName = $idn;
	}
	
	public function hasIdentifierName(): bool{
		return isset($this->identifierName);
	}
	
	public function getIdentifierName(): ?string{
		$f = __METHOD__;
		$print = false;
		if($this->hasIdentifierName()){
			if($print){
				Debug::print("{$f} non-static identifier name is \"{$this->identifierName}\"");
			}
			return $this->identifierName;
		}elseif($print){
			Debug::print("{$f} falling back to static identifier name");
		}
		return static::getIdentifierNameStatic();
	}
	
	public static function getIdentifierNameStatic():?string{
		$f = __METHOD__;
		$mode = static::getKeyGenerationMode();
		switch($mode){
			case KEY_GENERATION_MODE_PSEUDOKEY:
			case KEY_GENERATION_MODE_HASH:
				return 'uniqueKey';
			case KEY_GENERATION_MODE_NATURAL:
				Debug::error("{$f} you must override this function for object with natural keys");
			case KEY_GENERATION_MODE_LITERAL:
				return Debug::error("{$f} you must override this function for objects with literal keys");
			case KEY_GENERATION_MODE_UNIDENTIFIABLE:
				return null;
			default:
				Debug::error("{$f} invalid key generation mode \"{$mode}\"");
		}
	}
	
	public function getIdentifierNameCommand(): GetIdentifierNameCommand{
		return new GetIdentifierNameCommand($this);
	}
	
	public static function getReorderedColumnIndices(): ?array{
		return [];
	}
}
