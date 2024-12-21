<?php

namespace JulianSeymour\PHPWebApplicationFramework\data;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\debug;
use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\ends_with;
use function JulianSeymour\PHPWebApplicationFramework\get_class_filename;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\mutual_reference;
use function JulianSeymour\PHPWebApplicationFramework\registry;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\admin\AdminOnlyAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\PermissiveInterface;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\PermissiveTrait;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\StaticPermissionGatewayInterface;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheableInterface;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheableTrait;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\data\ConstructorCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ConcreteSubtypeColumnInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ElementBindableTrait;
use JulianSeymour\PHPWebApplicationFramework\common\IteratorTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StaticSubtypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\UpdateFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\schemes\SharedEncryptionSchemeInterface;
use JulianSeymour\PHPWebApplicationFramework\datum\AbstractDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\DatumBundle;
use JulianSeymour\PHPWebApplicationFramework\datum\TimestampDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatumInterface;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\KeyListDatum;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadedFlagTrait;
use JulianSeymour\PHPWebApplicationFramework\db\load\Loadout;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\AfterDeleteEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterEditEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterGenerateKeyEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterInitializeEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterInsertEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterLoadEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterSaveEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterUpdateEvent;
use JulianSeymour\PHPWebApplicationFramework\event\ApoptoseEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeDeleteEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeEditEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeGenerateKeyEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeInitializeEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeInsertEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeLoadEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeSaveEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeUpdateEvent;
use JulianSeymour\PHPWebApplicationFramework\event\DeallocateEvent;
use JulianSeymour\PHPWebApplicationFramework\event\LoadFailureEvent;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\form\FormProcessor;
use JulianSeymour\PHPWebApplicationFramework\json\EchoJsonInterface;
use JulianSeymour\PHPWebApplicationFramework\json\EchoJsonTrait;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\json\JsonDatum;
use JulianSeymour\PHPWebApplicationFramework\query\AssignmentExpression;
use JulianSeymour\PHPWebApplicationFramework\query\DeleteStatement;
use JulianSeymour\PHPWebApplicationFramework\query\OrderByClause;
use JulianSeymour\PHPWebApplicationFramework\query\UpdateStatement;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\Constraint;
use JulianSeymour\PHPWebApplicationFramework\query\index\IndexDefinition;
use JulianSeymour\PHPWebApplicationFramework\query\insert\InsertStatement;
use JulianSeymour\PHPWebApplicationFramework\query\select\RiggedSelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\table\FullTableName;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartTrait;
use JulianSeymour\PHPWebApplicationFramework\security\throttle\GenericThrottleMeter;
use JulianSeymour\PHPWebApplicationFramework\validate\ValidationClosureTrait;
use Exception;
use mysqli;

/**
 * Data stored in and queried from the database (usually), session or cookie superglobals, or RAM.
 * Its most important properties are its columns (using MultipleColumnDefiningTrait) and foreign data structure relationships, whose ORM-type properties are defined by a foreign key column, and whose references are stored in $foreignDataStructures
 *
 * @author j
 */
abstract class DataStructure extends Basic 
implements AllocationModeInterface, 
CacheableInterface, 
EchoJsonInterface, 
JavaScriptCounterpartInterface, 
ObjectRelationalMappingInterface,
PermissiveInterface,
ReplicableInterface,
StaticPropertyTypeInterface, 
StaticPermissionGatewayInterface,
TableDefinitionInterface{

	use AllocationModeTrait;
	use CacheableTrait;
	use EchoJsonTrait;
	use ElementBindableTrait;
	use IteratorTrait;
	use JavaScriptCounterpartTrait;
	use LoadedFlagTrait;
	use ObjectRelationalMappingTrait;
	use PermissiveTrait;
	use ReplicableTrait;
	use StaticPropertyTypeTrait;
	use TableDefinitionTrait;
	use UpdateFlagBearingTrait;
	use ValidationClosureTrait;

	/**
	 * Persistence mode assigned to columns generated by this object without persistence modes assigned
	 * @var int
	 */
	protected $defaulPersistenceMode;
	
	/**
	 *
	 * @var string|NULL
	 */
	protected $identifierName;

	/**
	 * This is only checked inside Datum->setValue()
	 *
	 * @var int
	 */
	private $receptivity;

	/**
	 *
	 * @param int $function
	 * @return string
	 */
	public abstract static function getDataType(): string;

	public function __construct(?int $mode = ALLOCATION_MODE_EAGER){
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			parent::__construct();
			if($mode === null){
				$mode = ALLOCATION_MODE_EAGER;
			}
			if($mode === ALLOCATION_MODE_EAGER){
				if($print){
					Debug::print("{$f} eager allocation mode, about to allocate columns");
				}
				$this->allocateColumns();
				if($print){
					Debug::print("{$f} returned from allocating columns");
				}
			}else{
				if($print){
					Debug::print("{$f} allocation mode is something other than eager");
				}
				$this->setAllocationMode($mode);
			}
			$this->setReceptivity(DATA_MODE_DEFAULT);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"autoRegister", //if true, this object will be registered as soon as its key is generated
			"arrayMembershipConfigured",
			"blockInsertion", //set this to true to stop insertion from happening at the last minute
			//"cascadeDelete", // checked in afterDeleteHook
			"dealloc", //if true, this object will be deallocated when a related data structure is deallocated. This is a memory saving trick, be careful with it
			DIRECTIVE_DELETE, // if true, this object is flagged for deletion if it is being managed by a related data structure in the process of an update operation; if it has yet to be inserted, this flag prevents its insertion from happening in the first place
			DIRECTIVE_DELETE_FOREIGN, // if true, this object is flagged to delete foreign data structures as part of its update operation
			"deleteOld", // if true, this object is flagged to delete OLD foreign data structures, which are stored in a separate array from the regular foreign data structures
			"derived",
			"disableLog", //prevents this object from being logged by the debugger
			"expanded",
			"expandForeign", // if true, this object has already expanded its foreign data structures (i.e. the function expandForeignDataStructures was called)
			DIRECTIVE_INSERT, // if true, this object is flagged for insertion
			DIRECTIVE_PREINSERT_FOREIGN, // if true, this object is flagged to insert foreign data structures to which it has constrained foreign key reference(s)
			DIRECTIVE_POSTINSERT_FOREIGN, // if true, this object is flagged to insert foreign data structure(s) that have constrained foreign data structures to this
			"inserting", //object is in the process of being inserted into the database. Used to prevent infinite loops in mutuallly referential objects being inserted in the same request/response cycle
			"inserted", // if true, object was inserted during this request. Needed because the insert flag must be turned off as soon as possible to prevent multiple inserts, but KeyListDatum->updateIntersectionTables needs to know this
			"invalidateCache", // if true. this object will attempt to invalidate all caches with its table name in afterEditHook
			"lazy", // if true, this object is being lazy loaded
			"loaded", // true -> this object was successfully loaded from the database
			"onDuplicateKeyUpdate", // if true, then this object will automatically generate "on duplicate key update" clauses as part of its update query statements
			"operand", // if true, this object is the operand of a database operation
			"processedForm", // if true, this object has processed a form
			"reloaded", // if true, this object has been reloaded from the database; this prevents infinitely recurvise reloading of mutually referenced foreign data structures and is unset at the end of reload()
			"replica", // if true, this object is a replica
			"searchResult", // if true, this object is flagged as a search result
			"temporary", // if true, this object marks its create table statements as temporary
			"trimmed", // if true, this object has trimmed columns without values or default values in trimUnusedColumns
			DIRECTIVE_UPDATE, // if true, this object has been marked for update
			DIRECTIVE_PREUPDATE_FOREIGN, // if true, this object will update foreign data structures that have to be updated before it, for whatever reason
			DIRECTIVE_POSTUPDATE_FOREIGN, // if true, this object will update foreign data structures that must be updated after it
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			//"cascadeDelete",
			"loaded",
			"onDuplicateKeyUpdate",
			"searchResult",
			"temporary",
			"trimmed"
		]);
	}
	
	public static function declarePropertyTypes(?StaticPropertyTypeInterface $object = null): array{
		return [
			"constraints" => Constraint::class,
			"columns" => Datum::class, // nonstatic declaration was commented out in constructor -- maybe for a reason
			"indexDefinitions" => IndexDefinition::class
		];
	}
	
	/**
	 * declare columns that make up the content of this object's row.
	 * You will want to redeclare this function in derived classes
	 *
	 * @param DataStructure $ds
	 *        	: optional DataStructure parameter
	 * @return Datum[]
	 */
	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$f = __METHOD__;
		try{
			$datatype = new VirtualDatum("dataType");
			$status = new VirtualDatum("status");
			$pretty = new VirtualDatum("prettyClassName");
			$search_result = new VirtualDatum("searchResult");
			$elementClass = new VirtualDatum("elementClass");
			$columns = [
				$datatype,
				$status,
				$pretty,
				$search_result,
				$elementClass
			];
			if(
				is_a(static::class, StaticSubtypeInterface::class, true)
				&& !is_a(static::class, ConcreteSubtypeColumnInterface::class, true)
			){
				$subtype = new VirtualDatum("subtype");
				array_push($columns, $subtype);
			}
			if(is_a(static::class, SoftDeletableInterface::class, true)){
				$soft = new TimestampDatum("softDeletionTimestamp");
				$soft->setDefaultValue(null);
				$soft->setUserWritableFlag(true);
				array_push($columns, $soft);
			}
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	/**
	 * XXX TODO I want to make this abstract but I'm too lazy to redefine it in derived classes
	 * @return int
	 */
	public static function getKeyGenerationMode(): int{
		return KEY_GENERATION_MODE_PSEUDOKEY;
	}
	
	public static function getDefaultPersistenceModeStatic(): int{
		return PERSISTENCE_MODE_DATABASE;
	}
	
	public static function getPrettyClassName():string{
		return static::getShortClass();
	}

	public static function getPrettyClassNames():string{
		$sc = static::getShortClass();
		if(ends_with($sc, "s")){
			return "{$sc}es";
		}
		return "{$sc}s";
	}

	public function getUserRoles(mysqli $mysqli, UserData $user): ?array{
		return $user->getStaticRoles();
	}

	public function setOperandFlag(bool $value = true): bool{
		return $this->setFlag("operand", $value);
	}

	public function getOperandFlag(){
		return $this->getFlag("operand");
	}
	
	public static function throttleOnInsert(): bool{
		return true;
	}

	public function setSearchResultFlag($value){
		return $this->setFlag("searchResult", $value);
	}

	public function getSearchResultFlag(){
		return $this->getFlag("searchResult");
	}

	public static function getDuplicateEntryRecourse(): int{
		$f = __METHOD__;
		Debug::warning("{$f} duplicate entries are not allowed");
		return RECOURSE_ABORT; // EXIT;
	}

	public static function constructorCommand(...$params): ConstructorCommand{
		$arr = [];
		if(isset($params)){
			foreach($params as $p){
				array_push($arr, $p);
			}
		}
		return new ConstructorCommand(static::class, ...$arr);
	}

	public function getRepository():Repository{
		return app()->getRepository($this->getDatabaseName(), $this->getTableName());
	}
	
	/**
	 * load something from the database with unique identifier
	 *
	 * @param string|int $key
	 *        	identifier
	 * @param string $suffix
	 *        	table name appendix
	 * @return int
	 */
	public function loadFailureHook(): int{
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} override this function if it's acceptible for objects of this class to have a not found status");
		}
		if($this->hasAnyEventListener(EVENT_LOAD_FAILED)){
			$this->dispatchEvent(new LoadFailureEvent());
		}
		return $this->setObjectStatus(ERROR_NOT_FOUND);
	}

	public static function getThrottleMeterClass(): string{
		return GenericThrottleMeter::class;
	}

	/**
	 * this is used to tell this data's Datum objects which phase of their lifecycle it is so they know whether to generate key/nonces (for example) or just set the value
	 *
	 * @param int $r
	 * @return int
	 */
	public function setReceptivity(?int $r): ?int{
		if($this->hasReceptivity()){
			$this->release($this->receptivity);
		}
		return $this->receptivity = $this->claim($r);
	}

	public function getReceptivity(): ?int{
		return $this->receptivity;
	}

	public function hasReceptivity():bool{
		return isset($this->receptivity);
	}

	public function getProcessedFormFlag():bool{
		return $this->getFlag("processedForm");
	}
	
	public function setBlockInsertionFlag(bool $value = true): bool{
		return $this->setFlag("blockInsertion", $value);
	}

	public function getBlockInsertionFlag(): bool{
		return $this->getFlag("blockInsertion");
	}

	public function blockInsertion(bool $value = true): DataStructure{
		$this->setFlag("blockInsertion", $value);
		return $this;
	}

	public function setTrimmedFlag(bool $value = true): bool{
		return $this->setFlag("trimmed", $value);
	}

	public function getTrimmedFlag(): bool{
		return $this->getFlag("trimmed");
	}

	/**
	 * Deallocates columns without values or default values
	 * This is a desperate attempt to save memory but it seems to make things worse
	 *
	 * @return int : the number of dropped columns
	 */
	public function trimUnusedColumns(bool $foreign=false, int $recursion_depth=0):int{
		$f = __METHOD__;
		try{
			$print = false;
			$trim = app()->getUseCase()->getTrimmableColumnNames($this);
			if($trim === null){
				$trimmed = 0;
			}else{
				$trimmed = count($trim);
			}
			if($trimmed > 0){
				if($print){
					Debug::print("{$f} about to drop the following columns:");
					Debug::printArray($trim);
				}
				foreach($trim as $column_name){
					$this->releaseColumn($column_name, true);
					$this->unsetArrayPropertyValue("columns", $column_name);
				}
			}elseif($print){
				Debug::print("{$f} there are no columns to trim");
			}
			$this->setTrimmedFlag(true);
			if($foreign && $recursion_depth > 0){
				if($this->hasForeignDataStructures()){
					$this->trimForeignDataStructures($recursion_depth);
				}
			}
			return $trimmed;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	/**
	 * returns true if the two objects have the same class and column values, false otherwise
	 *
	 * @param DataStructure $obj1
	 * @param DataStructure $obj2
	 * @return boolean
	 */
	public static function equals(DataStructure $obj1, DataStructure $obj2): bool{
		$f = __METHOD__;
		$print = false;
		if($obj1->getClass() !== $obj2->getClass()){
			if($print){
				Debug::print("{$f} class differs, returning false");
			}
			return false;
		}
		$columns1 = $obj1->getFilteredColumns(COLUMN_FILTER_COMPARABLE);
		$columns2 = $obj2->getFilteredColumns(COLUMN_FILTER_COMPARABLE);
		if(count($columns1) !== count($columns2)){
			if($print){
				Debug::print("{$f} column count differs");
			}
			return false;
		}
		foreach($columns1 as $column_name => $column){
			if($column instanceof VirtualDatum){
				continue;
			}elseif($column->getIgnoreInequivalenceFlag()){
				continue;
			}elseif(!$obj2->hasColumn($column_name)){
				if($print){
					Debug::print("{$f} second object lacks a datum at column \"{$column_name}\"");
				}
				return false;
			}
			$value1 = $column->getValue();
			$value2 = $obj2->getColumnValue($column_name);
			if($value1 !== $value2){
				if($print){
					Debug::print("{$f} values differ ({$value1} vs {$value2}) for datum \"{$column_name}\"");
				}
				return false;
			}
		}
		if($print){
			Debug::print("{$f} returning true");
		}
		return true;
	}
	
	public static function loadMultiple(mysqli $mysqli, SelectStatement $select, string $typedef = null, ...$params): ?array{
		return static::getRepositoryStatic()->loadMultiple($mysqli, static::class, $select, $typedef, ...$params);
	}

	public function beforeLoadHook(mysqli $mysqli): int{
		if($this->hasAnyEventListener(EVENT_BEFORE_LOAD)){
			$this->dispatchEvent(new BeforeLoadEvent());
		}
		return SUCCESS;
	}

	public function afterLoadHook(mysqli $mysqli): int{
		if($this->hasAnyEventListener(EVENT_AFTER_LOAD)){
			$this->dispatchEvent(new AfterLoadEvent());
		}
		return SUCCESS;
	}

	/**
	 * processes the results of a query on an intersection table
	 *
	 * @param array $arr
	 * @return int
	 */
	public function processIntersectionTableQueryResultArray(array $arr): int{
		$f = __METHOD__;
		$print = false;
		if($this->getReceptivity() === DATA_MODE_RECEPTIVE){
			Debug::error("{$f} this shouldn't be getting called on receptive objects");
		}
		if($print){
			Debug::printStackTraceNoExit("{$f} entered");
		}
		if(!array_key_exists("relationship", $arr)){
			Debug::warning("{$f} array does not have a foreign key name");
			Debug::printArray($arr);
			Debug::printStackTrace();
		}elseif($print){
			Debug::print("{$f} entered with the following array");
			Debug::printArray($arr);
		}
		$fkn = $arr['relationship'];
		if(!$this->hasColumn($fkn)){
			Debug::error("{$f} this object does not have a datum at column \"{$fkn}\"");
		}
		$column = $this->getColumn($fkn);
		if($column instanceof ForeignKeyDatum){
			$column->setValueFromQueryResult($arr["foreignKey"]);
		}elseif($column instanceof KeyListDatum){
			if($print){
				Debug::print("{$f} column is a key list");
			}
			$fk = $arr['foreignKey'];
			$column->pushValueFromQueryResult($fk);
			if(registry()->has($fk)){
				if($print){
					Debug::print("{$f} registry has an object with key \"{$fk}\"");
				}
				$fds = registry()->get($fk);
				if($fds->getLoadedFlag()){
					$this->setForeignDataStructureListMember($fkn, $fds);
				}elseif($print){
					Debug::print("{$f} ... but it wasn't loaded yet");
				}
			}elseif($print){
				Debug::print("{$f} the registry does not know anything about an object with key \"{$fk}\"");
			}
		}else{
			$cc = $column->getClass();
			Debug::error("{$f} illegal column class \"{$cc}\"");
		}
		if(cache()->enabled()){
			$column->setDirtyCacheFlag(true);
		}
		return SUCCESS;
	}

	/**
	 * XXX replace this pile
	 *
	 * @return NULL|string[]
	 */
	public function getLoadableIntersectionTableNames(?array &$array=null): ?array{
		$f = __METHOD__;
		try{
			$print = false;
			$columns = $this->getFilteredColumns(COLUMN_FILTER_POTENTIAL);
			if(empty($columns)){
				if($print){
					Debug::print("{$f} no polymorphic key datums with values");
				}
				return null;
			}
			$map = [];
			$dsc = $this->getClass();
			$type1 = $this->getTableName();
			foreach($columns as $column_name => $column){
				if($column->hasForeignDataStructureClass()){
					$fdsc = $column->getForeignDataStructureClass();
					if(!method_exists($fdsc, 'getTableNameStatic')){
						Debug::error("{$f} table name cannot be determined statically for foreign data structure class \"{$fdsc}\"");
					}
					$table2 = $fdsc::getTableNameStatic();
				}elseif($column->hasForeignDataStructureClassResolver()){
					$resolver = $column->getForeignDataStructureClassResolver();
					if(
						$column instanceof ForeignKeyDatumInterface && (
							$column->hasForeignDataType() || (
								$column->hasForeignDataSubtypeName() && 
								$column->hasForeignDataSubtype()
							)
						)
					){
						if($print){
							$key = $this->hasIdentifierValue() ? $this->getIdentifierValue() : "undefined";
							Debug::print("{$f} about to call {$resolver}::resolveClass() for column \"{$column_name}\" of {$dsc} with key \"{$key}\"");
						}
						$fdsc = $resolver::resolveClass($column);
						if(!method_exists($fdsc, 'getTableNameStatic')){
							Debug::error("{$f} table name cannot be determined statically for foreign data structure class \"{$fdsc}\"");
						}
						$table2 = $fdsc::getTableNameStatic();
					}else{
						if($print){
							Debug::print("{$f} column \"{$column_name}\" has its foreign data structre class resolver, but does not have a foreign datatype or subtype");
						}
						$intersections = $resolver::getAllPossibleIntersectionData($column);
						foreach($intersections as $intersection){
							$ftn = new FullTableName($intersection->getDatabaseName(), $intersection->getTableName());
							$sql = $ftn->toSQL();
							$map[$sql] = $sql;
							if(array_key_exists($sql, $array)){
								deallocate($ftn);
							}else{
								$array[$sql] = $ftn;
							}
						}
						deallocate($intersections);
						continue;
					}
				}else{
					Debug::print("{$f} column \"{$column_name}\" lacks a foreign data structure class or resolver");
				}
				$ftn = new FullTableName("intersections", "{$type1}_{$table2}");
				$sql = $ftn->toSQL();
				$map[$sql] = $sql;
				if(array_key_exists($sql, $array)){
					deallocate($ftn);
				}else{
					$array[$sql] = $ftn;
				}
			}
			if(empty($map)){
				if($print){
					Debug::print("{$f} returning null");
				}
				return null;
			}
			return $map;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	/**
	 * load any foreign keys that are stored in intersection tables
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function loadIntersectionTableKeys(mysqli $mysqli): int{
		$f = __METHOD__;
		try{
			$print = false;
			if(!$this->isUninitialized()){
				Debug::error("{$f} this shouldn't be getting called on loaded objects");
			}elseif($this->getReceptivity() === DATA_MODE_RECEPTIVE){
				Debug::error("{$f} this shouldn't be getting called on receptive objects");
			}
			$hostKeyNames = [];
			$this->getLoadableIntersectionTableNames($hostKeyNames);
			if(empty($hostKeyNames)){
				if($print){
					Debug::print("{$f} there are no intersection tables to load");
				}
				return SUCCESS;
			}
			$key = $this->getIdentifierValue();
			foreach($hostKeyNames as $intersectionTableName){
				$select = new SelectStatement();
				$select->from(
					$intersectionTableName->getDatabaseName(), 
					$intersectionTableName->getTableName()
				)->where(new WhereCondition("hostKey", OPERATOR_EQUALS));
				$result = $select->prepareBindExecuteGetResult($mysqli, 's', $key);
				deallocate($select);
				if($print){
					$ss = $select->toSQL();
				}
				$count = $result->num_rows;
				if($count == 0){
					if($print){
						Debug::warning("{$f} query statement \"{$ss}\" returned 0 results");
					}
					continue;
				}
				$results = $result->fetch_all(MYSQLI_ASSOC);
				$result->free_result();
				foreach($results as $result){
					$status = $this->processIntersectionTableQueryResultArray($result);
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} processIntersectionTableQueryResultArray on object with key \"{$key}\" returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}elseif($print){
						Debug::print("{$f} successfully processed intersection table query results for object with key \"{$key}\"");
					}
				}
			}
			deallocate($hostKeyNames);
			if(CACHE_ENABLED && $this->isRegistrable() && $this->hasIdentifierValue() && $this->hasTimeToLive()){
				$key = $this->getIdentifierValue();
				if(cache()->hasAPCu($key)){
					$cached = cache()->getAPCu($key);
					$columns = $this->getFilteredColumns(COLUMN_FILTER_DIRTY_CACHE);
					if(!empty($columns)){
						foreach($columns as $column_name => $column){
							$cached[$column_name] = $column->getDatabaseEncodedValue();
							$column->setDirtyCacheFlag(false);
						}
					}elseif($print){
						Debug::print("{$f} there are no dirty cache flagged columns");
					}
					cache()->setAPCu($key, $cached, $this->getTimeToLive());
				}elseif($print){
					Debug::print("{$f} there is no cached value with key \"{$key}\"");
				}
			}elseif($print){
				Debug::print("{$f} cache is not enabled");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	/**
	 * similar to processArray except it calls Datum->setValueFromQueryResult instead of $this->setColumnValue; it also sets the loaded flag and fires the before- and afterLoadHooks
	 *
	 * @param array $arr
	 * @return int
	 */
	public function processQueryResultArray(mysqli $mysqli, array $arr): int{
		$f = __METHOD__;
		try{
			$print = false;
			if($print){
				Debug::print("{$f} entered; about to process the following values:");
				Debug::printArray($arr);
			}
			$status = $this->beforeLoadHook($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} before load hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$columns = $this->getColumns();
			foreach($columns as $vn => $t){
				if(array_key_exists($vn, $arr)){
					$pm = $t->getPersistenceMode();
					switch($pm){
						case PERSISTENCE_MODE_ALIAS:
						case PERSISTENCE_MODE_DATABASE:
						case PERSISTENCE_MODE_EMBEDDED:
						case PERSISTENCE_MODE_ENCRYPTED:
							break;
						case PERSISTENCE_MODE_COOKIE:
						case PERSISTENCE_MODE_SESSION:
							//case PERSISTENCE_MODE_VOLATILE:
							Debug::warning("{$f} column \"{$vn}\" has invalid persistence mode ".Debug::getPersistenceModeString($pm));
							continue 2;
						default:
							Debug::warning("{$f} column \"{$vn}\" has unusual persistence mode ".Debug::getPersistenceModeString($pm));
					}
					if($print){
						Debug::print("{$f} about to call setValueFromQueryResult for column \"{$vn}\"");
					}
					$t->setValueFromQueryResult($arr[$vn]);
				}elseif($print){
					Debug::print("{$f} column \"{$vn}\" was not loaded from the database, and is not mandatory");
				}
			}
			// load queried columns
			if(method_exists($this, "setLoadedFlag")){
				$this->setLoadedFlag(true);
			}
			$status = $this->afterLoadHook($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} after load hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} returning normally");
			}
			return SUCCESS; //do NOT set object status here
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getAliasedColumnSelectStatement(string $column_name, $key=null):SelectStatement{
		return RiggedSelectStatement::getAliasedColumnSelectStatement($this, $column_name, $key);
	} // need to test this with more cases

	/**
	 * load this object from the database to satisfy WhereCondition $where for parameters $params.
	 *
	 * @param mysqli $mysqli
	 * @param WhereCondition|WhereCondition[] $where
	 * @param mixed|mixed[] $params
	 * @param OrderByClause $order_by
	 * @param int $limit : optional limit parameter
	 */
	public final function load(mysqli $mysqli, $where, $params, $order_by = null, $limit = null): int{
		return $this->getRepository()->load($mysqli, $this, $where, $params, $order_by, $limit);
	}

	/**
	 * Unset the values stored by an object of this class with no object context.
	 * This is useful for objects that store their values in superglobal arrays or cookies
	 *
	 * @param string[] ...$column_names
	 * @return number
	 */
	public static function unsetColumnValuesStatic(...$column_names): int{
		$f = __METHOD__;
		$storage = static::getDefaultPersistenceModeStatic();
		switch($storage){
			case PERSISTENCE_MODE_SESSION:
			case PERSISTENCE_MODE_COOKIE:
				break;
			default:
				Debug::error("{$f} this function can only be called on classes stored in superglobals");
		}
		$obj = new static();
		$ret = $obj->unsetColumnValues(...$column_names);
		deallocate($obj);
		return $ret;
	}

	public function isRegistrable(): bool{
		$idn = $this->getIdentifierName();
		return $idn !== null && $this->hasColumn($idn) && $this->getKeyGenerationMode() !== KEY_GENERATION_MODE_UNIDENTIFIABLE && $this->getKeyGenerationMode() !== KEY_GENERATION_MODE_NATURAL && $this->hasIdentifierValue();
	}

	public static function isRegistrableStatic(): bool{
		$idn = static::getIdentifierNameStatic();
		return $idn !== null && static::hasColumnStatic($idn) && static::getKeyGenerationMode() !== KEY_GENERATION_MODE_NATURAL;
	}

	public function isUninitialized(): bool{
		$f = __METHOD__;
		$print = false;
		if($print){
			if($this->getObjectStatus() === STATUS_PRELAZYLOAD){
				Debug::print("{$f} lazy load in progress");
			}elseif(parent::isUninitialized()){
				Debug::print("{$f} parent function returned true");
			}else{
				$err = ErrorMessage::getResultMessage($this->getObjectStatus());
				Debug::print("{$f} nope, status is \"{$err}\"");
			}
		}
		return $this->hasObjectStatus() && $this->getObjectStatus() === STATUS_PRELAZYLOAD || parent::isUninitialized();
	}

	/**
	 * override this to create additional functionality that gets called before deletion
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function beforeDeleteHook(mysqli $mysqli): int{
		$f = __METHOD__;
		$print = false;
		try{
			// before delete event
			if($this->hasAnyEventListener(EVENT_BEFORE_DELETE)){
				$status = $this->dispatchEvent(new BeforeDeleteEvent());
			}
			$status = $this->flagForeignDataStructuresForRecursiveDeletion($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} flagForeignDataStructuresForRecursiveDeletion returned error status \"{$err}\" for this ".$this->getDebugString());
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function hasDefaultPersistenceMode():bool{
		return isset($this->defaultPersistenceMode);
	}
	
	public function setDefaultPersistenceMode(?int $mode):?int{
		if($mode == null){
			unset($this->defUltPersistenceMode);
			return null;
		}
		return $this->defaultPersistenceMode = $mode;
	}
	
	public function getDefaultPersistenceMode(): int{
		if($this->hasDefaultPersistenceMode()){
			return $this->defaultPersistenceMode;
		}
		return static::getDefaultPersistenceModeStatic();
	}

	public static function getPermissionStatic(string $name, $data){
		return new AdminOnlyAccountTypePermission($name);
	}

	public function getDeleteStatement():DeleteStatement{
		$f = __METHOD__;
		if($this->getKeyGenerationMode() === KEY_GENERATION_MODE_UNIDENTIFIABLE){
			Debug::error("{$f} this ".$this->getDebugString()." is unidentifiable");
		}elseif(!$this->hasIdentifierName() && $this->getIdentifierNameStatic() === null){
			Debug::error("{$f} no identifier name for this ".$this->getDebugString());
		}elseif(!$this->hasColumn($this->getIdentifierName())){
			Debug::error("{$f} no identifier column for this ".$this->getDebugString());
		}
		$delete = new DeleteStatement();
		$delete->from($this->getDatabaseName(), $this->getTableName())->where(
			new WhereCondition($this->getIdentifierName(), OPERATOR_EQUALS)
		)->limit(1);
		return $delete;
	}
	
	/**
	 * delete this object from the database
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function delete(mysqli $mysqli): int{
		return $this->getRepository()->delete($mysqli, $this);
	}

	/**
	 * override this to define additional functionality that occurs after deletion
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function afterDeleteHook(mysqli $mysqli): int{
		$this->afterEditHook($mysqli, directive());
		if($this->hasAnyEventListener(EVENT_AFTER_DELETE)){
			$this->dispatchEvent(new AfterDeleteEvent());
		}
		return SUCCESS;
	}

	/**
	 * this is called by beforeSave and beforeDelete
	 *
	 * @param mysqli $mysqli
	 * @param string $directive
	 * @return int
	 */
	protected function beforeEditHook(mysqli $mysqli, string $directive): int{
		if($this->hasAnyEventListener(EVENT_BEFORE_EDIT)){
			$this->dispatchEvent(new BeforeEditEvent($directive));
		}
		return SUCCESS;
	}

	public function validate(): int{
		if($this->hasValidationClosure()){
			$closure = $this->getValidationClosure();
			return $closure($this);
		}
		return SUCCESS;
	}

	/**
	 * this is called by beforeUpdate and beforeInsert
	 *
	 * @param mysqli $mysqli
	 * @param string $directive
	 * @return int
	 */
	protected function beforeSaveHook(mysqli $mysqli, string $directive): int{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			$status = $this->beforeEditHook($mysqli, $directive);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} beforeEditHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} beforeEditHook successful");
			}
			$status = $this->validate();
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} validate returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} validation successful");
			}
			// deal with mutually referential one to one relationships for objects being inserted simultaneously
			$status = $this->fulfillMutuallyReferentialForeignKeys();
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} fulfillMutuallyReferentialForeignKeys returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			// moved this from right above validate()
			if($this->hasAnyEventListener(EVENT_BEFORE_SAVE)){
				$this->dispatchEvent(new BeforeSaveEvent($directive));
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	/**
	 * override this to define additional functionality that occurs after insertion
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function beforeInsertHook(mysqli $mysqli): int{
		$f = __METHOD__;
		try{
			$print = false;
			if($print){
				Debug::print("{$f} entered for this ".$this->getDebugString());
			}
			$status = $this->generateUndefinedForeignKeys();
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} generate undefined foreign keys returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} generateUndefinedKeys executed successfully");
			}
			$status = $this->loadForeignDataStructures($mysqli, false, 0, true);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} loadForeignDataStructures returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} successfully loaded foreign data structures");
			}
			if(!$this->getFlag("expandForeign")){
				$status = Loadout::expandForeignDataStructures($this, $mysqli);
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} expandForeignDataStructures returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print){
					Debug::print("{$f} successfully expanded foreign data structures");
				}
			}elseif($print){
				Debug::print("{$f} already expanded foreign data structures");
			}
			// beforeSaveHook gets called inside beforeInsert and beforeUpdate
			$status = $this->beforeSaveHook($mysqli, DIRECTIVE_INSERT);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} beforeSaveHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} beforeSaveHook executed successfully");
			}
			if($this->hasAnyEventListener(EVENT_BEFORE_INSERT)){
				$this->dispatchEvent(new BeforeInsertEvent()); // moved from top
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	/**
	 * returns all embedded data structures, i.e.
	 * those that are stored in separate tables
	 *
	 * @return NULL|EmbeddedData[]
	 */
	public function getEmbeddedDataStructures():?array{
		$f = __METHOD__;
		try{
			$print = false;
			$columns = $this->getFilteredColumns(COLUMN_FILTER_EMBEDDED);
			if(empty($columns)){
				if($print){
					Debug::print("{$f} there are no embedded columns");
				}
				return null;
			}
			$groups = [];
			foreach($columns as $column_name => $column){
				if($print){
					Debug::print("{$f} column \"{$column_name}\" is embedded");
				}
				$groupname = $column->getEmbeddedName();
				if(array_key_exists($groupname, $groups)){
					if($print){
						Debug::print("{$f} EmbeddedData for group \"{$groupname}\" already exists");
					}
					$replica = $column->replicate();
					$replica->setPersistenceMode(PERSISTENCE_MODE_DATABASE);
					$replica->setDataStructure($groups[$groupname]);
					$groups[$groupname]->pushColumn($replica);
					if($column->getUpdateFlag()){
						if($print){
							Debug::print("{$f} yes, embedded column \"{$column_name}\" is flagged for update");
						}
						$groups[$groupname]->setUpdateFlag(true);
					}elseif($print){
						Debug::print("{$f} no, embedded column \"{$column_name}\" is NOT flagged for update");
					}
					continue;
				}
				$embedme = new EmbeddedData();
				$embedme->setName($groupname);
				$embedme->setSubsumingObject($this);
				$replica = $column->replicate();
				$replica->setPersistenceMode(PERSISTENCE_MODE_DATABASE);
				$replica->setDataStructure($embedme);
				$embedme->pushColumn($replica);
				if($column->getUpdateFlag()){
					$embedme->setUpdateFlag(true);
					if($print){
						Debug::print("{$f} yes, embedded column \"{$column_name}\" is flagged for update");
					}
				}elseif($print){
					Debug::print("{$f} no, embedded column \"{$column_name}\" is NOT flagged for update");
				}
				$groups[$groupname] = $embedme;
				if($print){
					Debug::print("{$f} created EmbeddedData for group \"{$groupname}\" and pushed column \"{$column_name}\"");
				}
			}
			$print = false;
			if($print){
				foreach($groups as $name => $e){
					Debug::print("{$f} about to update the following columns for embed group \"{$name}\":");
					foreach($groups as $column_name => $column){
						Debug::print("{$f} {$column_name}");
					}
					Debug::print("{$f} done printing updatable columns for embed group \"{$name}\"");
					if($e->getColumnCount() < 2){
						Debug::error("{$f} embedded data structure \"{$name}\" has < 2 columns");
					}else{
						Debug::print("{$f} embedded group \"{$name}\" has a sufficient number of columns. The problem is likely in your create table statement.");
					}
				}
			}
			return $groups;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	/**
	 * insert this object into the database
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function insert(mysqli $mysqli): int{
		return $this->getRepository()->insert($mysqli, $this);
	}

	public function setInvalidateCacheFlag(bool $value = true): bool{
		return $this->setFlag("invalidateCache", $value);
	}

	public function getInvalidateCacheFlag(): bool{
		return $this->getFlag("invalidateCache");
	}

	protected function afterEditHook(mysqli $mysqli, string $directive): int{
		if(cache()->enabled() && $this->getInvalidateCacheFlag()){
			$ftn = new FullTableName($this->getDatabaseName(), $this->getTableName());
			$sql = $ftn->toSQL();
			$sha = sha1($sql);
			if(cache()->has("table_{$sha}")){
				cache()->delete($sha);
			}
		}
		if($this->hasAnyEventListener(EVENT_AFTER_EDIT)){
			$this->dispatchEvent(new AfterEditEvent($directive));
		}
		return SUCCESS;
	}

	protected function afterSaveHook(mysqli $mysqli, string $directive): int{
		$f = __METHOD__;
		$print = false;
		$status = $this->afterEditHook($mysqli, $directive);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} afterEditHook returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}elseif($print){
			Debug::print("{$f} afterEditHook returned successfully");
		}
		if($this->hasAnyEventListener(EVENT_AFTER_SAVE)){
			$this->dispatchEvent(new AfterSaveEvent($directive));
		}
		return SUCCESS;
	}

	public function afterInsertHook(mysqli $mysqli): int{
		$f = __METHOD__;
		$print = false;
		$status = $this->afterSaveHook($mysqli, DIRECTIVE_INSERT);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} afterSaveHook returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		if($this->hasAnyEventListener(EVENT_AFTER_INSERT)){
			$this->dispatchEvent(new AfterInsertEvent());
		}
		$status = $this->getObjectStatus();
		if(is_int($status) && $status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} dispatching afterInsert changed status to \"{$err}\"");
			return $status;
		}elseif($print){
			Debug::print("{$f} successfully dispatched afterInsert event");
		}
		return SUCCESS;
	}

	/**
	 * prevent the current IP address from flooding the database with garbage
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function throttle(mysqli $mysqli): int{
		$tmc = static::getThrottleMeterClass();
		$meter = new $tmc();
		$limit = 10; // 5; //60;
		$meter->setLimitPerMinute($limit);
		$timestamp = time();
		$quota_operator = OPERATOR_LESSTHANEQUALS;
		$select = $this->select()->where("insertIpAddress")->withTypeSpecifier('s')->withParameters([
			$_SERVER['REMOTE_ADDR']
		]);
		if($meter->meter($mysqli, $timestamp, $quota_operator, $select)){
			return SUCCESS;
		}else{
			return FAILURE;
		}
	}

	/**
	 * returns SUCCESS if the database does not already contain a row determined to be a duplicate of this object,
	 * ERROR_DUPLICATE_ENTRY otherwise.
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function preventDuplicateEntry(mysqli $mysqli): int{
		return $this->getRepository()->preventDuplicateEntry($mysqli, $this);
	}

	public function setIdentifierValue($value){
		return $this->setColumnValue($this->getIdentifierName(), $value);
	}

	public static function getRepositoryStatic():Repository{
		return app()->getRepository(static::getDatabaseNameStatic(), static::getTableNameStatic());
	}
	
	public static function getObjectFromKey(mysqli $mysqli, $key, ?int $mode = null): ?DataStructure{
		return static::getRepositoryStatic()->getObjectFromVariable($mysqli, static::class, static::getIdentifierNameStatic(), $key, $mode);
	}

	/**
	 * return an object of this class that satisifes the conedition $varname=$value
	 *
	 * @param mysqli $mysqli
	 * @param string $varname
	 * @param mixed $value
	 * @return DataStructure|NULL
	 */
	public static function getObjectFromVariable(mysqli $mysqli, string $varname, $value, ?int $mode = null): ?DataStructure{
		return static::getRepositoryStatic()->getObjectFromVariable($mysqli, static::class, $varname, $value, $mode);
	}

	public function loadFromKey(mysqli $mysqli, $key): int{
		$f = $this->getShortClass()."(".static::getShortClass().")->loadFromKey()";
		$print = false;
		$status = $this->load($mysqli, $this->getIdentifierName(), $key);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} loading object with key \"{$key}\" returned error status \"{$err}\"");
		}elseif($print){
			Debug::print("{$f} loaded object with key \"{$key}\" successfully");
		}
		return $status;
	}

	/**
	 *
	 * @return InsertStatement
	 */
	public function getInsertStatement(){
		$expressions = [];
		foreach($this->getFilteredColumnNames(COLUMN_FILTER_INSERT) as $column_name){
			$expressions[]= new AssignmentExpression($column_name);
		}
		$insert = new InsertStatement();
		$insert->into($this->getDatabaseName(), $this->getTableName())->set($expressions);
		if($this->getOnDuplicateKeyUpdateFlag()){
			$expressions = [];
			foreach($this->getFilteredColumnNames(COLUMN_FILTER_INSERT, "!".COLUMN_FILTER_ID) as $column_name){
				$expressions[]= new AssignmentExpression($column_name);
			}
			$insert->setDuplicateColumnExpressions($expressions);
		}
		return $insert;
	}

	/**
	 * Generate an array defining the columns with membership in the array generated by $this->toArray().
	 * This function is just for generating the array and does not configure array membership.
	 *
	 * @param string $config_id
	 * @return boolean[]
	 */
	public function getArrayMembershipConfiguration($config_id): ?array{
		return null;
	}

	public function setArrayMembershipConfiguredFlag(bool $value = true): bool{
		return $this->setFlag("arrayMembershipConfigured", $value);
	}

	public function getArrayMembershipConfiguredFlag(): bool{
		return $this->getFlag("arrayMembershipConfigured");
	}

	/**
	 * Configure membership per column for the array returned by $this->toArray().
	 *
	 * @param string|array $config_id
	 *        	: array for specifying per-column membership, or a string to pass to getArrayMembershipConfiguration to generate one
	 * @return int
	 */
	public function configureArrayMembership($config_id){
		$f = __METHOD__;
		$print = false;
		$this->setArrayMembershipConfiguredFlag(true);
		if(is_string($config_id) || is_int($config_id)){
			$keyvalues = $this->getArrayMembershipConfiguration($config_id);
		}elseif(is_array($config_id)){
			$keyvalues = $config_id;
		}else{
			Debug::error("{$f} config ID must be a string, integer or array");
		}
		if($print){
			Debug::print("{$f} got the following array membership configuration:");
			Debug::printArray($keyvalues);
		}
		foreach($keyvalues as $column_name => $value){
			if(!$this->hasColumn($column_name)){
				$sc = $this->getShortClass();
				Debug::error("{$f} datum at column \"{$column_name}\" does not exist for class {$sc}");
			}
			$this->getColumn($column_name)->configureArrayMembership($value);
		}
		return SUCCESS;
	}

	protected function addColumnEventListeners(Datum $column){
		$f = __METHOD__;
		$print = false;
		if($print){
			Debug::print("{$f} about to set up mutual reference closure for column ".$column->getName()." of ".$this->getDebugString());
		}
		if(BACKWARDS_REFERENCES_ENABLED){
			$name = $column->getName();
			$closure1 = function(DataStructure $parent, bool $deallocate=false) 
			use ($name, $f, $print){
				if($parent->hasColumn($name)){
					$print = $parent->getColumn($name)->getDebugFlag();
					if($print){
						if($deallocate){
							Debug::print("{$f} hard deallocating column {$name}");
						}
					}
					$parent->releaseColumn($name, $deallocate);
				}
			};
			$closure2 = function(Datum $column, bool $deallocate=false)
			use ($f, $print){
				if($column->hasDataStructure()){
					$column->releaseDataStructure(false);
				}
			};
			mutual_reference($this, $column, $closure1, $closure2, EVENT_RELEASE_CHILD, EVENT_RELEASE_PARENT, [
				"key" => $column->getName()
			]);
			$closure3 = function(DeallocateEvent $event, DataStructure $target) use ($column){
				$target->removeEventListener($event);
				if($column->hasDataStructure()){
					$column->releaseDataStructure(false);
				}
			};
			$this->addEventListener(EVENT_DEALLOCATE, $closure3);
		}
	}
	
	public function setColumns(?array $columns):array{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if($print){
			Debug::print("{$f} entered, about to set data structures and add event listeners");
		}
		$ret = $this->setArrayProperty("columns", $columns); //this must be called first because the event listeners attached in addColumnEventListeners require that the DataStructure and Datum must already have claims on one another
		foreach($columns as $column_name => $column){
			if(is_string($column)){
				Debug::error("{$f} column at index \"{$column_name}\" is the string \"{$column}\"");
			}
			$column->setDataStructure($this);
			$this->addColumnEventListeners($column);
		}
		if($print){
			Debug::print("{$f} done adding event listeners");
		}
		return $ret;
	}
	
	public function pushColumn(...$columns):int{
		$f = __METHOD__;
		if(!isset($columns) || count($columns) === 0){
			Debug::error("{$f} received no input parameters");
		}
		$ret = $this->pushArrayProperty("columns", ...$columns);
		foreach($columns as $column){
			$this->addColumnEventListeners($column);
		}
		return $ret;
	}
	
	/**
	 * This gets called in repackColumns, allowing you to change the encryption scheme of a datum declared in a parent class
	 * @param Datum $column
	 */
	public static function reconfigureColumnEncryption(Datum $column):void{}
	
	/**
	 * indexes datums by column name and generates components of DatumBundles and encrypted datums
	 *
	 * @param DataStructure $ds
	 * @param array $columns
	 */
	public static final function repackColumns(array $columns, ?DataStructure $ds = null): array{
		$f = __METHOD__;
		try{
			$print = false;
			$return = [];
			$ps = static::getDefaultPersistenceModeStatic();
			foreach($columns as $column){
				$vn = $column->getName();
				if($print){
					Debug::print("{$f} colunn {$vn}");
				}
				if(empty($column)){
					Debug::error("{$f} datum is undefined");
				}elseif(!$column instanceof AbstractDatum){
					$gottype = is_object($column) ? $column->getClass() : gettype($column);
					Debug::error("{$f} datum is a {$gottype}");
				}elseif($column instanceof DatumBundle){
					$components = $column->generateComponents($ds);
					foreach($components as $component){
						$component->setDeclaredFlag(true);
						if(is_array($component)){
							Debug::error("{$f} component is an array");
						}elseif(!$component->hasPersistenceMode()){
							$component->setPersistenceMode($ps);
						}
						if($ds !== null){
							$component->setDataStructure($ds);
						}
						$return[$component->getName()] = $component;
					}
					deallocate($column);
					continue;
				}
				$column->setDeclaredFlag(true);
				if($ds !== null){
					$column->setDataStructure($ds);
				}
				$return[$vn] = $column;
				static::reconfigureColumnEncryption($column);
				if(!$column->hasEncryptionScheme()){
					if(!$column->hasPersistenceMode()){
						$column->setPersistenceMode($ps);
					}elseif($print){
						$ps2 = $column->getPersistenceMode();
						Debug::print("{$f} datum \"{$vn}\" already has its storage mode set to {$ps2}");
					}
					continue;
				}
				$scheme_class = $column->getEncryptionScheme();
				if(is_int($scheme_class)){
					Debug::error("{$f} encryption scheme \"{$scheme_class}\" is an integer");
				}elseif(!class_exists($scheme_class)){
					Debug::error("{$f} encryption scheme class \"{$scheme_class}\" does not exist");
				}
				//XXX TODO this is an inappropriate place to be dealing with this
				$scheme_obj = new $scheme_class();
				if($scheme_obj instanceof SharedEncryptionSchemeInterface){
					if(!array_key_exists("replacementKeyRequested", $columns)){
						if(!$ds instanceof DataStructure){
							Debug::error("{$f} this part breaks down if you don't provide a data structure");
						}elseif($print){
							Debug::print("{$f} replacementKeyRequested has not already been pushed");
						}
						$requested = new BooleanDatum("replacementKeyRequested");
						$requested->setDefaultValue(false);
						$requested->setDataStructure($ds);
						$return["replacementKeyRequested"] = $requested;
					}
				}
				deallocate($scheme_obj);
				$scheme = new $scheme_class($column);
				$components = $scheme->generateComponents($ds);
				deallocate($scheme);
				$mode = $column->hasPersistenceMode() ? $column->getPersistenceMode() : $ps;
				foreach($components as $component_name => $component){
					if($print){
						Debug::print("{$f} about to set original variable name \"{$vn}\" for component at index \"{$component_name}\"");
					}
					$component->setOriginalDatumIndex($vn);
					if($component->getPersistenceMode() !== PERSISTENCE_MODE_ENCRYPTED){
						$component->setPersistenceMode($mode);
					}
					if($column->isNullable()){
						$component->setNullable(true);
						$component->setDefaultValue(null);
					}
					$component->setDataStructure($ds);
					$component->setDeclaredFlag(true);
					$return[$component->getName()] = $component;
				}
				$column->setPersistenceMode(PERSISTENCE_MODE_ENCRYPTED);
			}
			return $return;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	protected function generateColumns():?array{
		$columns = [];
		// populates the array with this data structure's columns
		static::declareColumns($columns, $this);
		return $columns;
	}
	
	public function allocateColumns(): void{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		// generate columns. The first two are void functions with reference parameters for performance reasons
		$columns = $this->generateColumns();
		$embedded = mods()->getModuleSpecificColumns($this);
		if(!empty($embedded)){
			array_push($columns, ...$embedded);
		}
		// generates components of datum bundles
		$repacked = static::repackColumns($columns, $this);
		//unset($columns);
		if(isset($repacked) && is_array($repacked) && !empty($repacked)){
			// this function is for derived classes to change columns declared in super classes. This gets called after repackColumns so it can modify bundle components
			static::reconfigureColumns($repacked, $this);
			// reorders columns if applicable
			$reordered = $this->getReorderedColumnIndices();
			if(!empty($reordered)){
				$repacked = $this->reorderColumns($repacked, $reordered);
			}
			foreach($repacked as $name => $column){
				if($column instanceof ForeignKeyDatumInterface && ! $column->hasRelationshipType()){
					Debug::error("{$f} foreign key column \"{$name}\" does not define its relationship type");
				}
			}
			if($print){
				Debug::print("{$f} assigning the following columns:");
				Debug::printArray($repacked);
			}
			$this->setColumns($repacked);
			if($print){
				Debug::print("{$f} returned from assigning columns");
			}
			if(app()->hasUseCase()){
				app()->getUseCase()->reconfigureDataStructure($this);
			}
		}else{
			if(isset($columns) && is_array($columns) && !empty($columns)){
				Debug::error("{$f} non-empty columns went in, but repackColumns returned nothing");
			}elseif($print){
				Debug::print("{$f} repackColumns returned nothing");
			}
		}
	}
	
	/**
	 * returns the value of VirtualDatums
	 *
	 * @param string $column_name
	 * @return mixed
	 */
	public function getVirtualColumnValue(string $column_name){
		$f = __METHOD__;
		switch($column_name){
			case "dataType":
				return $this->getDataType();
			case "elementClass":
				return get_short_class($this->getElementClass());
			case "prettyClassName":
				return $this->getPrettyClassName();
			case "searchResult":
				return $this->getSearchResultFlag();
			case "status":
				return $this->getObjectStatus();
			default:
				Debug::error("{$f} override this in derived classes -- column name is \"{$column_name}\"");
		}
	}

	/**
	 * return true if the specified VirtualDatum's value can be returned, false otherwise
	 *
	 * @param string $column_name
	 * @return boolean
	 */
	public function hasVirtualColumnValue(string $column_name): bool{
		$f = __METHOD__;
		switch($column_name){
			case "status":
				return $this->hasObjectStatus();
			case "dataType":
			case "prettyClassName":
				return true;
			case "elementClass":
				return $this->hasElementClass();
			case "searchResult":
				return $this->getSearchResultFlag();
			default:
				$sc = static::getShortClass();
				Debug::error("{$f} override this in derived classes -- class is \"{$sc}\", column name is \"{$column_name}\"");
		}
	}

	/**
	 * returns an array approximation of this object based on the configuration passed to configureArrayMembership
	 *
	 * @return array
	 */
	public function toArray($config_id = null): array{
		$f = __METHOD__;
		$print = false;
		if($config_id !== null){
			if($print){
				Debug::print("{$f} about to call configureArrayMembership with the following parameter:");
				Debug::print($config_id);
			}
			$this->configureArrayMembership($config_id);
		}elseif($print){
			Debug::print("{$f} config ID is null");
		}
		$columns = $this->getFilteredColumns(COLUMN_FILTER_ARRAY_MEMBER);
		if(count($columns) == 0){
			Debug::error("{$f} column count is 0");
		}
		$arr = [];
		foreach($columns as $column_name => $column){
			if(!$column->getArrayMembershipFlag()){
				if($print){
					Debug::error("{$f} datum \"{$column_name}\" does not have its array membership flag set");
				}
				continue;
			}elseif($column instanceof JsonDatum){
				$value = $column->getValue();
			}else{
				if($print){
					Debug::print("{$f} about to contribute value of datum \"{$column_name}\" to the array");
				}
				$value = $column->getHumanReadableValue();
				while($value instanceof ValueReturningCommandInterface){
					if($print){
						Debug::print("{$f} datum {$column_name}'s value is a value-returning command; about to evaluate");
					}
					$value = $value->evaluate();
				}
			}
			$arr[$column_name] = $value;
		}
		if($print){
			$count = count($arr);
			Debug::print("{$f} returning an array with $count members");
			Debug::printArray($arr);
		}
		return $arr;
	}

	public function echoInnerJson(bool $destroy = false): void{
		$f = __METHOD__;
		Debug::error("{$f} disabled because of redundant brackets");
		Json::echo($this->toArray(), $destroy, false);
	}

	public function echoJson(bool $destroy = false): void{
		Json::echo($this->toArray(), $destroy, false);
	}

	protected function beforeGenerateKeyHook(): int{
		if($this->hasAnyEventListener(EVENT_BEFORE_GENERATE_KEY)){
			$this->dispatchEvent(new BeforeGenerateKeyEvent());
		}
		return SUCCESS;
	}

	protected function afterGenerateKeyHook($key): int{
		if($this->hasAnyEventListener(EVENT_AFTER_GENERATE_KEY)){
			$this->dispatchEvent(new AfterGenerateKeyEvent($key));
		}
		return SUCCESS;
	}

	protected function beforeGenerateInitialValuesHook(): int{
		if($this->hasAnyEventListener(EVENT_BEFORE_INITIALIZE)){
			$this->dispatchEvent(new BeforeInitializeEvent());
		}
		return SUCCESS;
	}

	/**
	 * generate initial column values prior to insertion
	 *
	 * @return int
	 */
	public function generateInitialValues(): int{
		$f = __METHOD__;
		try{
			$print = false;
			// before generate initial values hook
			$status = $this->beforeGenerateInitialValuesHook();
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} before generate key hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			// generate this object's identifier
			$mode = $this->getKeyGenerationMode();
			$idn = $this->getIdentifierName();
			if($this->hasIdentifierName() && !$this->hasIdentifierValue()){
				$status = $this->generateKey();
				if($status !== SUCCESS){
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} generateKey returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print){
					Debug::print("{$f} successfully generated key");
				}
			}
			// iterate through the other columns and set their values
			foreach($this->getFilteredColumns("!".COLUMN_FILTER_VIRTUAL, "!".COLUMN_FILTER_ALIAS, "!".COLUMN_FILTER_ID) as $name => $column){
				if($column->hasValue()){
					if($print){
						Debug::print("{$f} column \"{$name}\" has already generated its value \"" . $column->getValue() . "\"");
					}
					continue;
				}elseif($idn !== null && $name === $idn){
					if($print){
						Debug::print("{$f} skipping identifier column \"{$idn}\" generation");
					}
					continue;
				}elseif($print){
					Debug::print("{$f} about to generate initial value for column \"{$name}\"");
				}
				if($column->getPersistenceMode() !== PERSISTENCE_MODE_COOKIE || ! headers_sent()){
					$status = $column->generate();
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} column \"{$name}\" returned error status \"{$err}\" when generating initial value");
						return $this->setObjectStatus($status);
					}elseif($print){
						Debug::print("{$f} generated initial value for column \"{$name}\"");
					}
				}elseif($print){
					Debug::print("{$f} this column is stored in cookies, and headers were already sent");
				}
			}
			// after generate initial values hook
			$status = $this->afterGenerateInitialValuesHook();
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} afterGenerateInitialValuesHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} returning normally");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	protected function afterGenerateInitialValuesHook(): int{
		if($this->hasAnyEventListener(EVENT_AFTER_INITIALIZE)){
			$this->dispatchEvent(new AfterInitializeEvent());
		}
		return SUCCESS;
	}

	public function getAutoRegisterFlag():bool{
		return $this->getFlag("autoRegister");
	}
	
	public function setAutoRegisterFlag(bool $value=true):bool{
		return $this->setFlag("autoRegister", $value);
	}
	
	public function autoRegister(bool $value=true):DataStructure{
		$this->setAutoRegisterFlag($value);
		return $this;
	}
	
	/**
	 * generate a unique identifier for this object.
	 *
	 * @return int : status code
	 */
	public function generateKey():int{
		$f = __METHOD__;
		try{
			$print = false;
			if($print){
				Debug::printStackTraceNoExit("{$f} entered for this ".$this->getDebugString());
			}
			$status = $this->beforeGenerateKeyHook();
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} before generate key hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$mode = $this->getKeyGenerationMode();
			switch($mode){
				case KEY_GENERATION_MODE_NATURAL:
					if(!$this->hasIdentifierValue()){
						$idn = $this->getIdentifierName();
						Debug::printPost("{$f} natural key generation mode -- identifier {$idn} is undefined for this ".$this->getDebugString());
					}
					break; // return SUCCESS;
				case KEY_GENERATION_MODE_LITERAL:
					Debug::error("{$f} don't call this for objects with literal key generation mode");
				default:
			}
			$key = null;
			$idn = $this->getIdentifierName();
			if($idn === null){
				Debug::error("{$f} this object has no identifier whatsoever");
			}elseif(!$this->hasColumn($idn)){
				if($print){
					Debug::print("{$f} this object does not have a column \"{$idn}\"");
				}
			}elseif(!$this->hasIdentifierValue()){
				if($print){
					Debug::print("{$f} key has not been generated");
				}
				if($mode !== KEY_GENERATION_MODE_NATURAL){
					if($this->hasColumnValue('uniqueKey')){
						$key = $this->getIdentifierValue();
						Debug::error("{$f} key was already generated -- returning \"{$key}\"");
						return SUCCESS;
					}elseif($print){
						$column = $this->getColumn('uniqueKey');
						$column_class = $column->getClass();
						Debug::print("{$f} key generation mode is \"{$mode}\"; about to generate key with datum of class \"{$column_class}\"");
					}
					$status = $this->getColumn('uniqueKey')->generate();
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} generating key returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}
					$key = $this->getColumn('uniqueKey')->getValue();
					if($print){
						Debug::print("{$f} generated key \"{$key}\"");
					}
					// $this->setIdentifierValue($key);
					if($this->getAutoRegisterFlag()){
						if(registry()->hasObjectRegisteredToKey($key)){
							if($this->getKeyGenerationMode() !== KEY_GENERATION_MODE_HASH){
								Debug::error("{$f} impermissable key collision");
							}
							if(!$this->getOnDuplicateKeyUpdateFlag()){
								if($print){
									$collision = registry()->getRegisteredObjectFromKey($key);
									$cc = $collision->getClass();
									$did1 = $this->getDebugId();
									$decl1 = $this->getDeclarationLine();
									$did2 = $collision->getDebugId();
									$decl2 = $collision->getDeclarationLine();
									Debug::print("{$f} there is already a {$cc} mapped to key \"{$key}\". This object has debug ID {$did1} and was declated {$decl1}; collision has debug ID {$did2} and was declared {$decl2}");
								}
								return $this->setObjectStatus(ERROR_KEY_COLLISION);
							}
						}
						if(registry()->has($key)){
							registry()->update($key, $this);
						}else{
							registry()->registerObjectToKey($key, $this);
						}
					}
				}elseif($print){
					Debug::print("{$f} natural key generation mode");
				}
			}else{//if($print){
				Debug::error("{$f} object's key was already generated for this ".$this->getDebugString());
			}
			$status = $this->afterGenerateKeyHook($key);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} after generate key hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setInsertFlag(bool $value = true): bool{
		$f = __METHOD__;
		$print = false;
		if($value && $this->getDeleteFlag()){
			Debug::error("{$f} cannot simultaneously flag an object for both insertion and deletion. The object may have tripped its apoptosis signal");
			return $this->setDeleteFlag(false);
		}elseif($print){
			$did = $this->getDebugId();
			Debug::print("{$f} entered. Debug Id is \"{$did}\"");
		}
		return $this->setFlag(DIRECTIVE_INSERT, $value);
	}

	public function getInsertFlag(): bool{
		return $this->getFlag(DIRECTIVE_INSERT);
	}

	public function setInsertedFlag(bool $value = true): bool{
		return $this->setFlag("inserted", $value);
	}

	public function getInsertedFlag(): bool{
		return $this->getFlag("inserted");
	}

	public function setDeleteFlag(bool $value = true): bool{
		$f = __METHOD__;
		$print = false;
		if($print){
			$did = $this->getDebugId();
			Debug::printStackTraceNoExit("{$f} entered. Debug ID is \"{$did}\"");
		}
		return $this->setFlag(DIRECTIVE_DELETE, $value);
	}

	public function getDeleteFlag(): bool{
		return $this->getFlag(DIRECTIVE_DELETE);
	}

	protected function apoptoseHook($caller): int{
		if($this->hasAnyEventListener(EVENT_APOPTOSE)){
			$this->dispatchEvent(new ApoptoseEvent($caller));
		}
		return SUCCESS;
	}

	/**
	 * marks this object for deletion
	 */
	public final function apoptose($caller): int{
		$f = __METHOD__;
		try{
			$print = false;
			$this->setDeleteFlag(true);
			$status = $this->apoptoseHook($caller);
			switch($status){
				case SUCCESS:
					if($print){
						Debug::print("{$f} apoptoseHook returned success");
					}
					break;
				default:
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} apoptoseHook returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getOnDuplicateKeyUpdateFlag():bool{
		return $this->getFlag("onDuplicateKeyUpdate");
	}

	public function setOnDuplicateKeyUpdateFlag(bool $value=true):bool{
		return $this->setFlag("onDuplicateKeyUpdate", $value);
	}

	public function getIdentifierValue(){
		$f = __METHOD__;
		try{
			$print = false;
			if($this->getKeyGenerationMode() === KEY_GENERATION_MODE_LITERAL){
				return $this->getIdentifierName();
			}
			$vn = $this->getIdentifierName();
			if($vn == null){
				if($print){
					Debug::print("{$f} identifier name is null");
				}
				return null;
			}elseif(!$this->hasColumn($vn)){
				Debug::error("{$f} undefined column \"{$vn}\" for this ".$this->getDebugString());
			}elseif($print){
				Debug::print("{$f} identifier name is \"{$vn}\"");
			}
			$key = $this->getColumnValue($vn);
			if(!is_int($key) && ! is_string($key)){
				$gottype = gettype($key);
				$decl = $this->getDeclarationLine();
				Debug::error("{$f} key is a {$gottype}. Declared {$decl}");
			}
			if($print){
				Debug::print("{$f} returning \"{$key}\"");
			}
			return $key;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getUpdateViewName(): string{
		return $this->getTableName();
	}

	public function getReloadedFlag():bool{
		return $this->getFlag("reloaded");
	}

	public function setReloadedFlag(bool $value = true):bool{
		return $this->setFlag("reloaded", $value);
	}

	/**
	 * reload this object from the database
	 *
	 * @param mysqli $mysqli
	 * @param boolean $foreign
	 *        	: if true, reload foreign data structures as well
	 * @return int
	 */
	public function reload(mysqli $mysqli, bool $foreign = true): int{
		return $this->getRepository()->reload($mysqli, $this, $foreign);
	}

	public function getIdentifierValueCommand():GetColumnValueCommand{
		$givc = new GetColumnValueCommand();
		$givc->setDataStructure($this);
		$givc->setColumnName($this->getIdentifierName());
		return $givc;
	}

	public function isDeleted():bool{
		return $this->hasObjectStatus() && $this->getObjectStatus() === STATUS_DELETED;
	}

	public function getUpdateDatabaseName(): string{
		return $this->getDatabaseName();
	}

	public function getUpdateStatement($write_indices):UpdateStatement{
		$update = new UpdateStatement(
			$this->getUpdateDatabaseName(), 
			$this->getUpdateViewName()
		);
		$update->set($write_indices)->where(
			new WhereCondition($this->getIdentifierName(), OPERATOR_EQUALS)
		);
		return $update;
	}

	public function beforeUpdateHook(mysqli $mysqli): int{
		$f = __METHOD__;
		$print = false;
		if($this->hasAnyEventListener(EVENT_BEFORE_UPDATE)){
			$this->dispatchEvent(new BeforeUpdateEvent());
		}
		$status = $this->generateUndefinedForeignKeys();
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::error("{$f} generate undefined foreign keys returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}elseif($print){
			Debug::print("{$f} generateUndefinedForeignKeys returned successfully");
		}
		$status = $this->loadForeignDataStructures($mysqli, false, 0, true);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} loadUpdatedForeignDataStructures returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}elseif($print){
			Debug::print("{$f} loadForeignDataStructures returned successfully");
		}
		$status = $this->beforeSaveHook($mysqli, DIRECTIVE_UPDATE);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} beforeSaveHook returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}elseif($print){
			Debug::print("{$f} beforeSaveHook returned successfully");
		}
		return SUCCESS;
	}

	public function hasIdentifierValue(): bool{
		$f = __METHOD__;
		$print = $this->getDataType() === DATATYPE_MESSAGE;
		$mode = $this->getKeyGenerationMode();
		if($mode === KEY_GENERATION_MODE_UNIDENTIFIABLE){
			if($print){
				Debug::print("{$f} this ".$this->getDebugString()." is unidentifiable");
			}
			return false;
		}elseif(!$this->hasIdentifierName()){
			if($print){
				Debug::print("{$f} identifier name is unavailable for this ".$this->getDebugString());
			}
			return false;
		}
		$idn = $this->getIdentifierName();
		if($idn === null){
			if($print){
				Debug::print("{$f} identifier name returned null for this ".$this->getDebugString());
			}
			return false;
		}
		return $this->hasColumnValue($idn);
	}

	public function ejectIdentifierValue(){
		return $this->ejectColumnValue($this->getIdentifierName());
	}

	/**
	 * update this object's row in the database
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public final function update(mysqli $mysqli): int{
		$repository = $this->getRepository();
		return $repository->update($mysqli, $this);
	}

	public function afterUpdateHook(mysqli $mysqli): int{
		$f = __METHOD__;
		$status = $this->afterSaveHook($mysqli, DIRECTIVE_UPDATE);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} afterSaveHook returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}elseif($this->hasAnyEventListener(EVENT_AFTER_UPDATE)){
			$this->dispatchEvent(new AfterUpdateEvent());
		}
		return SUCCESS;
	}

	/**
	 * copy values from another object to this one
	 *
	 * @param DataStructure $that
	 * @return int
	 */
	public function copy($that):int{
		$f = __METHOD__;
		try{
			$print = false;
			$ret = parent::copy($that);
			if($that->hasColumns()){
				if(!$this->hasColumns()){
					$this->allocateColumns();
				}
				foreach($that->getColumns() as $column_name => $column){
					$this->getColumn($column_name)->copy($column);
				}
			}
			if($that->hasForeignDataStructures()){
				if($print){
					Debug::print("{$f} copying foreign data structures");
				}
				$this->copyForeignDataStructures($that);
			}elseif($print){
				Debug::print("{$f} no foreign data structures to copy");
			}
			return $ret;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	/**
	 * create a replica of this object without accessing the database
	 *
	 * @return NULL|DataStructure
	 */
	public final function replicate(...$params): ?ReplicableInterface{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			$status = $this->beforeReplicateHook();
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} before replica hook returned error status \"{$err}\"");
				$this->setObjectStatus($status);
				return null;
			}
			$mode = $this->hasAllocationMode() ? $this->getAllocationMode() : ALLOCATION_MODE_EAGER;
			if($print){
				Debug::print("{$f} allocation mode is \"{$mode}\"");
			}
			$replica = new static($mode);
			$replica->setReplicaFlag(true);
			$replica->setReceptivity(DATA_MODE_PASSIVE);
			if($print){
				$count = $replica->getColumnCount();
				if($count === 0){
					$count2 = $this->getColumnCount();
					if($count2 === 0){
						Debug::error("{$f} column count is zero for both original and replica");
					}
					Debug::error("{$f} replica column count is 0, but this object has {$count2} columns");
				}elseif($print){
					Debug::print("{$f} replica column count is {$count}");
				}
			}
			$replica->copy($this);
			$replica->setReceptivity($this->getReceptivity());
			$status = $this->afterReplicateHook($replica);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} after replica hook returned error status \"{$err}\"");
				$this->setObjectStatus($status);
				return null;
			}
			return $replica;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function whereIntersectionalHostKey($foreignClass, string $relationship, string $operator = OPERATOR_EQUALS): WhereCondition{
		return WhereCondition::intersectional(static::class, $foreignClass, "hostKey", $relationship, $operator);
	}

	public static function whereIntersectionalForeignKey($foreignClass, string $relationship, string $operator = OPERATOR_EQUALS): WhereCondition{
		return WhereCondition::intersectional(static::class, $foreignClass, "foreignKey", $relationship, $operator);
	}

	public static function generateLazyAliasExpression($foreignClass, ?string $foreignKeyName = null, ?SelectStatement $subquery = null): SelectStatement{
		return SelectStatement::generateLazyAliasExpression(static::class, $foreignClass, $foreignKeyName, $subquery);
	}

	public function logDatabaseOperation(string $directive): int{
		$f = __METHOD__;
		$print = false;
		if($this->getFlag("disableLog")){
			return 0;
		}
		$class = static::getShortClass();
		if(!$this->hasIdentifierName()){
			if($print){
				Debug::print("{$f} identifier name is undefined");
			}
			$key = "[unidentifiable]";
		}elseif($this->hasIdentifierValue()){
			if($print){
				Debug::print("{$f} identifier value is defined");
			}
			$key = $this->getIdentifierValue();
		}else{
			Debug::error("{$f} should not be logging database operations on objects without identifiers. Object is a ".$this->getDebugString());
		}
		$did = $this->getDebugId();
		$decl = $this->getDeclarationLine();
		return debug()->digest("{$directive} {$class} with key {$key} (debug ID {$did}, declared {$decl})");
	}

	public function setInsertingFlag(bool $value = true): bool{
		return $this->setFlag("inserting", $value);
	}

	public function getInsertingFlag(): bool{
		return $this->getFlag("inserting");
	}

	public static function getJavaScriptClassPath(): ?string{
		$fn = get_class_filename(DataStructure::class);
		return substr($fn, 0, strlen($fn) - 3) . "js";
	}

	public function getDeallocateFlag():bool{
		return $this->getFlag("dealloc");
	}
	
	public function setDeallocateFlag(bool $value=true):bool{
		return $this->setFlag("dealloc", $value);
	}
	
	public function processForm(AjaxForm $form, ?array $arr, ?array $files = null): int{
		$processor = new FormProcessor();
		$ret = $processor->processForm($this, $form, $arr, $files);
		$this->disableDeallocation();
		deallocate($processor);
		$this->enableDeallocation();
		return $ret;
	}
	
	public function dispose(bool $deallocate=false):void{
		$f = __METHOD__;
		$print = false;
		$ds = $this->getDebugString();
		if($print){
			Debug::print("{$f} entered for this {$ds}");
			if($deallocate){
				Debug::print("{$f} we are hard deallocating this ".$ds);
			}else{
				Debug::print("{$f} no recursive disposal for this {$ds}");
			}
		}
		if($this->getAllocatedFlag()){
			$this->releaseAllForeignDataStructures($deallocate); //must be called unconditionally, and prior to the parent function
		}
		if($this->hasColumns()){
			$this->releaseColumns($deallocate);
		}elseif($print){
			Debug::print("{$f} no columns to release");
		}
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		parent::dispose($deallocate);
		$this->release($this->databaseName, $deallocate);
		$this->release($this->tableName, $deallocate);
		$this->release($this->elementClass, $deallocate);
		$this->release($this->iterator, $deallocate);
		$this->release($this->oldDataStructures, $deallocate);
		$this->release($this->permissionGateway, $deallocate);
		if($this->hasPermissions()){
			$this->releasePermissions($deallocate);
		}
		$this->release($this->singlePermissionGateways, $deallocate);
		$this->release($this->propertyTypes, $deallocate);
		$this->release($this->receptivity, $deallocate);
	}
}
