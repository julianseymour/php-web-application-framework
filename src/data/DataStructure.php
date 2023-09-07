<?php

namespace JulianSeymour\PHPWebApplicationFramework\data;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\array_remove_key;
use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\cache;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\debug;
use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\ends_with;
use function JulianSeymour\PHPWebApplicationFramework\getDateTimeStringFromTimestamp;
use function JulianSeymour\PHPWebApplicationFramework\get_class_filename;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\is_abstract;
use function JulianSeymour\PHPWebApplicationFramework\lazy;
use function JulianSeymour\PHPWebApplicationFramework\mods;
use function JulianSeymour\PHPWebApplicationFramework\registry;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\UserData;
use JulianSeymour\PHPWebApplicationFramework\admin\AdminOnlyAccountTypePermission;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\PermissiveTrait;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\StaticPermissionGatewayInterface;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheableInterface;
use JulianSeymour\PHPWebApplicationFramework\cache\CacheableTrait;
use JulianSeymour\PHPWebApplicationFramework\command\CommandBuilder;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\data\ConstructorCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetColumnValueCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetForeignDataStructureCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetForeignDataStructureCountCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetForeignDataStructureListCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetForeignDataStructureListMemberCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\GetIdentifierNameCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\HasForeignDataStructureCommand;
use JulianSeymour\PHPWebApplicationFramework\command\data\SetForeignDataStructureCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\BinaryExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\OrCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\AllocationModeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ElementBindableTrait;
use JulianSeymour\PHPWebApplicationFramework\common\IteratorTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StaticSubtypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\UpdateFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\ClassResolver;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\schemes\SharedEncryptionSchemeInterface;
use JulianSeymour\PHPWebApplicationFramework\datum\AbstractDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\DatumBundle;
use JulianSeymour\PHPWebApplicationFramework\datum\HashKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\IpAddressDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\PseudokeyDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\SerialNumberDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TimestampDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatumInterface;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\KeyListDatum;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\DatabaseCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicWriteCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\load\LazyLoadHelper;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadedFlagTrait;
use JulianSeymour\PHPWebApplicationFramework\db\load\Loadout;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\AfterCreateTableEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterDeleteEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterDeleteForeignDataStructuresEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterDeriveForeignDataStructuresEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterEditEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterExpandEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterGenerateKeyEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterInsertEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterInsertForeignDataStructuresEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterLoadEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterSaveEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterSetForeignDataStructureEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterUpdateEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterUpdateForeignDataStructuresEvent;
use JulianSeymour\PHPWebApplicationFramework\event\ApoptoseEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeCreateTableEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeDeleteEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeDeleteForeignDataStructuresEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeDeriveForeignDataStructuresEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeEditEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeExpandEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeGenerateKeyEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeInsertEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeInsertForeignDataStructuresEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeLoadEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeSaveEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeSetForeignDataStructureEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeUpdateEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeUpdateForeignDataStructuresEvent;
use JulianSeymour\PHPWebApplicationFramework\event\EventListeningTrait;
use JulianSeymour\PHPWebApplicationFramework\event\LoadFailureEvent;
use JulianSeymour\PHPWebApplicationFramework\file\FileData;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\CheckedInput;
use JulianSeymour\PHPWebApplicationFramework\input\FileInput;
use JulianSeymour\PHPWebApplicationFramework\input\InputInterface;
use JulianSeymour\PHPWebApplicationFramework\json\EchoJsonInterface;
use JulianSeymour\PHPWebApplicationFramework\json\EchoJsonTrait;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\json\JsonDatum;
use JulianSeymour\PHPWebApplicationFramework\query\DeleteStatement;
use JulianSeymour\PHPWebApplicationFramework\query\OrderByClause;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use JulianSeymour\PHPWebApplicationFramework\query\QuestionMark;
use JulianSeymour\PHPWebApplicationFramework\query\TemporaryFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\query\TypeSpecificInterface;
use JulianSeymour\PHPWebApplicationFramework\query\UpdateStatement;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnAlias;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnAliasExpression;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnDefiningTrait;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\ConstrainableTrait;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\Constraint;
use JulianSeymour\PHPWebApplicationFramework\query\database\DatabaseNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\index\IndexDefinition;
use JulianSeymour\PHPWebApplicationFramework\query\index\MultipleIndexDefiningTrait;
use JulianSeymour\PHPWebApplicationFramework\query\insert\InsertStatement;
use JulianSeymour\PHPWebApplicationFramework\query\join\TableFactor;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\table\CreateTableStatement;
use JulianSeymour\PHPWebApplicationFramework\query\table\FullTableName;
use JulianSeymour\PHPWebApplicationFramework\query\table\TableNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptCounterpartTrait;
use JulianSeymour\PHPWebApplicationFramework\security\throttle\GenericThrottleMeter;
use JulianSeymour\PHPWebApplicationFramework\security\throttle\ThrottleMeterData;
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
StaticPropertyTypeInterface, 
StaticPermissionGatewayInterface{

	use AllocationModeTrait;
	use CacheableTrait;
	use ConstrainableTrait;
	use DatabaseNameTrait;
	use EchoJsonTrait;
	use ElementBindableTrait;
	use EventListeningTrait;
	use IteratorTrait;
	use JavaScriptCounterpartTrait;
	use LoadedFlagTrait;
	use MultipleColumnDefiningTrait;
	use MultipleIndexDefiningTrait;
	use PermissiveTrait;
	use ReplicableTrait;
	use StaticPropertyTypeTrait;
	use TableNameTrait;
	use TemporaryFlagBearingTrait;
	use UpdateFlagBearingTrait;
	use ValidationClosureTrait;

	/**
	 * Other DataStructures to which this object has a relationship.
	 * The keys of this array correspond to the ForeignKeyDatums that define the parameters of the relationship
	 *
	 * @var array
	 */
	private $foreignDataStructures;

	/**
	 *
	 * @var string|NULL
	 */
	protected $identifierName;

	/**
	 * like $foreignDataStructures, except these are in the process of getting replaced during an update operation
	 *
	 * @var array
	 */
	private $oldDataStructures;

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
		try {
			$print = false;
			parent::__construct();
			if ($mode === null) {
				$mode = ALLOCATION_MODE_EAGER;
			}
			if ($mode === ALLOCATION_MODE_EAGER) {
				$this->allocateColumns();
			} else {
				if ($print) {
					Debug::print("{$f} allocation mode is something other than eager");
				}
				$this->setAllocationMode($mode);
			}
			$this->setReceptivity(DATA_MODE_DEFAULT);
			if(
				!app()->getFlag("install") && 
				method_exists($this, "getTableNameStatic") && 
				method_exists($this, "getDatabaseNameStatic") &&
				!$this instanceof EmbeddedData && 
				!$this instanceof IntersectionData &&
				!$this instanceof EventSourceData &&
				!$this instanceof DatabaseCredentials &&
				$this->getDefaultPersistenceMode() === PERSISTENCE_MODE_DATABASE
			){
				if(!$this->tableExists(db()->getConnection(PublicReadCredentials::class))){
					Debug::error("{$f} table \"".$this->getTableName()."\" does not exist");
				}
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function allocateColumns(): void{
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		// generate columns. The first two are void functions with reference parameters for performance reasons
		$columns = [];
		// populates the array with this data structure's columns
		static::declareColumns($columns, $this);
		$embedded = mods()->getModuleSpecificColumns($this);
		if (! empty($embedded)) {
			array_push($columns, ...$embedded);
		}
		// generates components of datum bundles
		$repacked = static::repackColumns($columns, $this);
		if (isset($repacked) && is_array($repacked) && ! empty($repacked)) {
			// this function is for derived classes to change columns declared in super classes. This gets called after repackColumns so it can modify bundle components
			static::reconfigureColumns($repacked, $this);
			// reorders columns if applicable
			$reordered = $this->getReorderedColumnIndices();
			if (! empty($reordered)) {
				$repacked = static::reorderColumns($repacked);
			}
			foreach ($repacked as $name => $column) {
				if ($column instanceof ForeignKeyDatumInterface && ! $column->hasRelationshipType()) {
					Debug::error("{$f} foreign key column \"{$name}\" does not define its relationship type");
				}
			}
			if($print){
				Debug::print("{$f} assigning the following columns:");
				Debug::printArray($repacked);
			}
			$this->setColumns($repacked);
			if (app()->hasUseCase()) {
				app()->getUseCase()->reconfigureDataStructure($this);
			}
		} else {
			if (isset($columns) && is_array($columns) && ! empty($columns)) {
				Debug::error("{$f} non-empty columns went in, but repackColumns returned nothing");
			}elseif($print) {
				Debug::print("{$f} repackColumns returned nothing");
			}
		}
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"arrayMembershipConfigured",
			"blockInsertion",
			"cascadeDelete", // checked in afterDeleteHook
			DIRECTIVE_DELETE, // if true, this object is flagged for deletion if it is being managed by a related data structure in the process of an update operation; if it has yet to be inserted, this flag prevents its insertion from happening in the first place
			DIRECTIVE_DELETE_FOREIGN, // if true, this object is flagged to delete foreign data structures as part of its update operation
			"deleteOld", // if true, this object is flagged to delete OLD foreign data structures, which are stored in a separate array from the regular foreign data structures
			"derived",
			"disableLog",
			"expanded",
			"expandForeign", // if true, this object has already expanded its foreign data structures (i.e. the function expandForeignDataStructures was called)
			DIRECTIVE_INSERT, // if true, this object is flagged for insertion
			DIRECTIVE_PREINSERT_FOREIGN, // if true, this object is flagged to insert foreign data structures to which it has constrained foreign key reference(s)
			DIRECTIVE_POSTINSERT_FOREIGN, // if true, this object is flagged to insert foreign data structure(s) that have constrained foreign data structures to this
			"inserting",
			"inserted", // if true, object was inserted during this request. Needed because the insert flag must be turned off as soon as possible to prevent multiple inserts, but KeyListDatum->updateIntersectionTables needs to know this
			"invalidateCache", // if true. this object will attempt to invalidate all caches with its table name in afterEditHook
			"lazy", // if true, this object is beign lazy loaded
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
			DIRECTIVE_POSTUPDATE_FOREIGN // if true, this object will update foreign data structures that must be updated after it
		]);
	}

	public static function declarePropertyTypes(?StaticPropertyTypeInterface $object = null): array{
		return [
			"constraints" => Constraint::class,
			"columns" => Datum::class, // nonstatic declaration was commented out in constructor -- maybe for a reason
			"indexDefinitions" => IndexDefinition::class
		];
	}

	public static function getPrettyClassName():string{
		return static::getShortClass();
	}

	public static function getPrettyClassNames():string{
		$sc = static::getShortClass();
		if (ends_with($sc, "s")) {
			return "{$sc}es";
		}
		return "{$sc}s";
	}

	public function getUserRoles(mysqli $mysqli, UserData $user): ?array{
		return $user->getStaticRoles();
	}

	public function getForeignDataStructures(): ?array{
		if (! isset($this->foreignDataStructures) || ! is_array($this->foreignDataStructures)) {
			return null;
		}
		return $this->foreignDataStructures;
	}

	public function setOperandFlag(bool $value = true): bool{
		return $this->setFlag("operand", $value);
	}

	public function getOperandFlag(){
		return $this->getFlag("operand");
	}

	public function setColumns(?array $columns): ?array{
		$f = __METHOD__;
		foreach ($columns as $column_name => $column) {
			if (is_string($column)) {
				Debug::error("{$f} column at index \"{$column_name}\" is the string \"{$column}\"");
			}
			$column->setDataStructure($this);
		}
		return $this->setArrayProperty("columns", $columns);
	}

	public static function throttleOnInsert(): bool{
		return true;
	}

	public static function getReorderedColumnIndices(): ?array{
		return null;
	}

	public function setSearchResultFlag($value){
		return $this->setFlag("searchResult", $value);
	}

	public function getSearchResultFlag(){
		return $this->getFlag("searchResult");
	}

	/**
	 * reorder columns in the order returned by getReorderedColumnIndices())
	 * If this object has a column that is not defined by getReorderedColumnIndices,
	 * they will go at the end in their initial order
	 *
	 * @param array $columns
	 * @return Datum[]
	 */
	public static final function reorderColumns(?array $columns):?array{
		$f = __METHOD__;
		$order = static::getReorderedColumnIndices();
		if (empty($order)) {
			return $columns;
		}
		$reordered = [];
		foreach ($order as $column_name) {
			if (! array_key_exists($column_name, $columns)) {
				Debug::error("{$f} column \"{$column_name}\" does not exist");
				continue;
			}
			$reordered[$column_name] = $columns[$column_name];
		}
		foreach (array_keys($columns) as $column_name) {
			if (! array_key_exists($column_name, $reordered)) {
				$reordered[$column_name] = $columns[$column_name];
			}
		}
		$columns = null;
		return $reordered;
	}

	public static function getDuplicateEntryRecourse(): int{
		$f = __METHOD__;
		Debug::warning("{$f} duplicate entries are not allowed");
		return RECOURSE_ABORT; // EXIT;
	}

	public function getTableName(): string{
		$f = __METHOD__;
		try {
			$print = false;
			if ($this->hasTableName()) {
				if ($print) {
					Debug::print("{$f} table name was directly assigned");
				}
				return $this->tableName;
			}elseif(!method_exists($this, 'getTableNameStatic')){
				Debug::error("{$f} table name for class ".$this->getShortClass()." cannot be determined statically");
			}
			if ($print) {
				Debug::print("{$f} table name was not already assigned");
			}
			$table = static::getTableNameStatic();
			if ($print) {
				Debug::print("{$f} returning \"{$table}\"");
			}
			return $this->setTableName($table);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getFullTableName():string{
		return $this->getDatabaseName().".".$this->getTableName();
	}

	public static function constructorCommand(...$params): ConstructorCommand{
		$arr = [];
		if (isset($params)) {
			foreach ($params as $p) {
				array_push($arr, $p);
			}
		}
		return new ConstructorCommand(static::class, ...$arr);
	}

	public function getDatabaseName(): string{
		$f = __METHOD__;
		$print = false;
		if ($this->hasDatabaseName()) {
			if ($print) {
				Debug::print("{$f} database name was already assigned");
			}
			return $this->databaseName;
		}elseif(!method_exists($this, 'getDatabaseNameStatic')){
			Debug::error("{$f} database name cannot be determined statically");
		}
		return $this->setDatabaseName(static::getDatabaseNameStatic());
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
		if ($print) {
			Debug::print("{$f} override this function if it's acceptible for objects of this class to have a not found status");
		}

		$this->dispatchEvent(new LoadFailureEvent());

		return $this->setObjectStatus(ERROR_NOT_FOUND);
	}

	public function getPostInsertForeignDataStructuresFlag(){
		return $this->getFlag(DIRECTIVE_POSTINSERT_FOREIGN);
	}

	public function setPostInsertForeignDataStructuresFlag(bool $value = true): bool{
		$f = __METHOD__;
		$print = false;
		if ($print) {
			Debug::printStackTraceNoExit("{$f} entered");
		}
		return $this->setFlag(DIRECTIVE_POSTINSERT_FOREIGN, $value);
	}

	public function getHumanReadableColumnValue(string $column_name){
		return $this->getColumn($column_name)->getHumanReadableValue();
	}

	public static function getThrottleMeterClass(): string{
		return GenericThrottleMeter::class;
	}

	public static function getDefaultPersistenceModeStatic(): int{
		return PERSISTENCE_MODE_DATABASE;
	}

	public static function createThrottleMeterObject(){
		$tmc = static::getThrottleMeterClass();
		return new $tmc();
	}

	public function hasSerialNumber(){
		return $this->hasColumnValue('num');
	}

	/**
	 * this is used to tell this data's Datum objects which phase of their lifecycle it is so they know whether to generate key/nonces (for example) or just set the value
	 *
	 * @param int $r
	 * @return int
	 */
	public function setReceptivity(?int $r): ?int{
		return $this->receptivity = $r;
	}

	public function getReceptivity(): ?int{
		return $this->receptivity;
	}

	/**
	 * delete local reference to a foreign data structure $column_name and return it
	 *
	 * @param string $column_name
	 * @return DataStructure
	 */
	public function ejectForeignDataStructure(string $column_name): ?DataStructure{
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		if($this->hasColumn($column_name)){
			$column = $this->getColumn($column_name);
			$this->ejectColumnValue($column_name);
			if ($column->applyFilter(COLUMN_FILTER_FOREIGN, COLUMN_FILTER_INTERSECTION)) {
				if ($column->hasForeignDataTypeName()) {
					$this->ejectColumnValue($column->getForeignDataTypeName());
				}
				if ($column->hasForeignDataSubtypeName()) {
					$this->ejectColumnValue($column->getForeignDataSubtypeName());
				}
			}elseif($print){
				Debug::print("{$f} datum at column \"{$column_name}\" is not an intersectional foreign key datum");
			}
		}
		if(array_key_exists($column_name, $this->foreignDataStructures)){
			$ret = $this->foreignDataStructures[$column_name];
			$this->foreignDataStructures = array_remove_key($this->foreignDataStructures, $column_name);
		}else{
			$ret = null;
		}
		if(!$this->hasForeignDataStructures()){
			unset($this->foreignDataStructures);
		}
		return $ret;
	}

	public function ejectOldDataStructure(string $column_name): DataStructure{
		$f = __METHOD__;
		if (! $this->hasOldDataStructure($column_name)) {
			Debug::error("{$f} no old data structure to delete");
		}
		$ret = $this->oldDataStructures[$column_name];
		$this->oldDataStructures = array_remove_key($this->oldDataStructures, $column_name);
		if (! $this->hasOldDataStructures()) {
			unset($this->oldDataStructures);
			if ($this->getDeleteOldDataStructuresFlag()) {
				$this->setDeleteOldDataStructuresFlag(false);
			}
		}
		return $ret;
	}

	/**
	 * like the above but for key lists
	 *
	 * @param string $column_name
	 * @param mixed $key
	 * @return DataStructure|NULL
	 */
	public function ejectForeignDataStructureListMember(string $column_name, $key): ?DataStructure{
		$f = __METHOD__;
		$print = false;
		if (! $this->hasForeignDataStructureListMember($column_name, $key)) {
			Debug::error("{$f} no foreign data structure list member at column \"{$column_name}\" with key \"{$key}\"");
		}elseif($print) {
			Debug::print("{$f} ejecting foreign data structure list \"{$column_name}\" member with key \"{$key}\"");
		}
		$column = null;
		if ($this->hasColumn($column_name)) {
			$column = $this->getColumn($column_name);
			if (! $column instanceof KeyListDatum) {
				Debug::error("{$f} datum at column \"{$column_name}\" is not a key list");
			}
		}
		if ($print) {
			$count = $this->getForeignDataStructureCount($column_name);
			Debug::print("{$f} before ejection, this object has {$count} foreign data structures in list \"{$column_name}\"");
		}
		$fds = $this->foreignDataStructures[$column_name][$key];
		$this->foreignDataStructures[$column_name] = array_remove_key($this->foreignDataStructures[$column_name], $key);
		if (empty($this->foreignDataStructures[$column_name])) {
			$this->foreignDataStructures = array_remove_key($this->foreignDataStructures, $column_name);
			if (! $this->hasForeignDataStructures()) {
				unset($this->foreignDataStructures);
			}
		}
		if ($column !== null){
			if($this->hasForeignDataStructureList($column_name)) {
				$structs = $this->foreignDataStructures[$column_name];
				$keys = array_keys($structs);
				$column->setValue($keys);
			}else{
				$column->ejectValue();
			}
		}
		if ($print) {
			$count2 = $this->getForeignDataStructureCount($column_name);
			Debug::print("{$f} after ejection, this object has {$count2} foreign data structures in list \"{$column_name}\"");
			if ($count === $count2) {
				Debug::error("{$f} ejection failed");
			}
		}
		return $fds;
	}

	public function ejectOldDataStructureListMember(string $column_name, $key): DataStructure{
		$f = __METHOD__;
		if (! $this->hasOldDataStructureListMember($column_name, $key)) {
			Debug::error("{$f} no old data structure \"{$column_name}\" with key \"{$key}\"");
		}
		$fds = $this->oldDataStructures[$column_name][$key];
		$this->oldDataStructures[$column_name] = array_remove_key($this->oldDataStructures[$column_name], $key);
		$this->oldDataStructures[$column_name] = array_remove_key($this->oldDataStructures[$column_name], $key);
		if (empty($this->oldDataStructures[$column_name])) {
			$this->oldDataStructures = array_remove_key($this->oldDataStructures, $column_name);
			if (! $this->hasOldDataStructures()) {
				unset($this->oldDataStructures);
				if ($this->getDeleteOldDataStructuresFlag()) {
					$this->setDeleteOldDataStructuresFlag(false);
				}
			}
		}
		return $fds;
	}

	public function getOldDataStructure($column_name): DataStructure{
		$f = __METHOD__;
		if (! $this->hasOldDataStructure($column_name)) {
			Debug::error("{$f} old subordinate data structure of type \"{$column_name}\" is undefined");
		}
		return $this->oldDataStructures[$column_name];
	}

	public function getForeignDataStructure(string $column_name): DataStructure{
		$f = __METHOD__;
		if (! $this->hasForeignDataStructure($column_name)) {
			if ($this->hasIdentifierValue()) {
				$key = $this->getIdentifierValue();
			} else {
				$key = "undefined";
			}
			$decl = $this->getDeclarationLine();
			if ($this->hasColumnValue($column_name)) {
				Debug::error("{$f} foreign data structure \"{$column_name}\" is undefined for object with debug ID \"{$this->debugId}\" and key \"{$key}\", declared {$decl}. However, the column value is defined. Fix this by flagging that column to auto load.");
			} else {
				Debug::error("{$f} foreign data structure \"{$column_name}\" is undefined for object with debug ID \"{$this->debugId}\" and key \"{$key}\", declared {$decl}. Furthermore, the column value is undefined.");
			}
		}
		return $this->foreignDataStructures[$column_name];
	}

	public function getForeignDataStructureCommand(string $column_name): GetForeignDataStructureCommand{
		return new GetForeignDataStructureCommand($this, $column_name);
	}

	public function hasForeignDataStructureCommand(string $column_name): HasForeignDataStructureCommand{
		return new HasForeignDataStructureCommand($this, $column_name);
	}

	public function setForeignDataStrucureCommand(string $column_name, $struct): SetForeignDataStructureCommand{
		return new SetForeignDataStructureCommand($this, $column_name, $struct);
	}

	public function hasOldDataStructure($column_name){
		return is_array($this->oldDataStructures) && array_key_exists($column_name, $this->oldDataStructures) && isset($this->oldDataStructures[$column_name]) && is_object($this->oldDataStructures[$column_name]);
	}

	public function hasProcessedForm(){
		return $this->getFlag("processedForm");
	}

	/**
	 * returns true if this object has a foreign data structure or structures for column $column_name,
	 * false otherwise
	 *
	 * @param string $column_name
	 * @return boolean
	 */
	public function hasForeignDataStructure(string $column_name): bool{
		$f = __METHOD__;
		try {
			$print = false;
			if (is_array($column_name)) {
				Debug::error("{$f} column is an array");
			}
			$column = null;
			if ($this->hasColumn($column_name)) {
				$column = $this->getColumn($column_name);
				if ($column instanceof VirtualDatum) {
					return $this->hasVirtualColumnValue($column_name);
				}elseif($column instanceof KeyListDatum) {
					return $this->hasForeignDataStructureList($column_name);
				}
			}elseif($print){
				Debug::print("{$f} no column \"{$column_name}\"");
			}//
			if(!isset($this->foreignDataStructures) || !is_array($this->foreignDataStructures)){
				if ($print) {
					Debug::print("{$f} foreign data structures array has not been allocated");
				}
				return false;
			}elseif(! array_key_exists($column_name, $this->foreignDataStructures)) {
				if ($print) {
					Debug::print("{$f} data structures array has no defined foreign data structure \"{$column_name}\"");
				}
				return false;
			}elseif(empty($this->foreignDataStructures[$column_name])) {
				if ($print) {
					Debug::print("{$f} data structure at column \"{$column_name}\" is null");
				}
				return false;
			}elseif(!is_object($this->foreignDataStructures[$column_name])){
				if(is_array($this->foreignDataStructures[$column_name])){
					return $this->hasForeignDataStructureList($column_name);
				}elseif($print) {
					Debug::print("{$f} data structure at column \"{$column_name}\" is not an object or array");
				}
				return false;
			}else{
				$status = $this->foreignDataStructures[$column_name]->getObjectStatus();
				if($status === ERROR_NOT_FOUND){
					if(!$this->getSuppressWarningsFlag()){
						$sc = $this->getShortClass();
						$key = $this->hasIdentifierValue() ? $this->getIdentifierValue() : '[undefined]';
						Debug::error("{$f} foreign data structure \"{$column_name}\" is defined, but object status is not found for {$sc} with key {$key}");
					}
					return false;
				}
			}
			return true;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasForeignDataStructures(): bool{
		return is_array($this->foreignDataStructures) && ! empty($this->foreignDataStructures);
	}

	public function getReplacementKeyRequested():bool{
		$f = __METHOD__;
		try {
			if ($this->hasColumn("replacementKeyRequested")) {
				$r = $this->getColumnValue("replacementKeyRequested");
				if ($r) {
					// Debug::print("{$f} yes, {$r} satisfies an if statement");
					return true;
				}
				// Debug::print("{$f} no, {$r} does not satisfy an if statement");
			}
			return false;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * call this to request a replacement decryption key (needed after hard password reset)
	 * to be filfilled by some other samaritan who has access to that key
	 *
	 * @return int
	 */
	public function requestReplacementDecryptionKey():int{
		$f = __METHOD__;
		try {
			$print = false;
			$this->setObjectStatus(ERROR_REPLACEMENT_KEY_REQUESTED);
			if ($this->getReplacementKeyRequested()) {
				if ($print) {
					Debug::print("{$f} replacement key was already requested");
				}
				return $this->getObjectStatus();
			}elseif($print) {
				Debug::print("{$f} replacement key was not already requested");
			}
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			$column = $this->getColumn("replacementKeyRequested");
			$column->setValue(true);
			$column->setUpdateFlag(true);
			$status = $this->update($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} update() returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print) {
				Debug::print("{$f} successfully wrote key replacement request");
			}
			return $this->getObjectStatus();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function fulfillReplacementKeyRequest():int{
		$f = __METHOD__;
		try {
			$this->setColumnValue("replacementKeyRequested", 0);
			$replica = $this->replicate();
			$replica->setReceptivity(DATA_MODE_RECEPTIVE);
			foreach (array_keys($this->getColumns()) as $column_name) {
				$replica->setColumnValue($column_name, $this->getColumnValue($column_name));
				$column = $replica->getColumn($column_name);
				$column->setUpdateFlag(true);
			}
			$mysqli = db()->getConnection(PublicWriteCredentials::class);
			$status = $replica->update($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} updating replica returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setOldDataStructure($column_name, $old_struct){
		if(!isset($this->oldDataStructures) || !is_array($this->oldDataStructures)){
			$this->oldDataStructures = [];
		}
		return $this->oldDataStructures[$column_name] = $old_struct;
	}

	/**
	 * set this object as the foreign data structure (or a member of a list of foreign
	 * data structures) referenced by $struct at the converse relationship index stored in this
	 * object's datum at $column_name
	 *
	 * @param string $column_name
	 *        	: index of the datum that stores the ConverseRelationshipKey that $struct uses to reference this object
	 * @param DataStructure $struct
	 *        	: data structure that stores the reference to this object needing reciprocation
	 */
	private function reciprocateRelationship(string $column_name, DataStructure $struct):void{
		$f = __METHOD__;
		try {
			$print = $this->getDebugFlag();
			if (! $this->hasColumn($column_name)) {
				Debug::error("{$f} no datum at column \"{$column_name}\"");
			}
			$column = $this->getColumn($column_name);
			if (! $column->hasConverseRelationshipKeyName()) {
				Debug::error("{$f} datum at column \"{$column_name}\" does not know the name of its converse relationship key");
			}
			$converse_key = $column->getConverseRelationshipKeyName();
			if ($struct->hasColumn($converse_key)) {
				if ($print) {
					Debug::print("{$f} foreign data structure has a datum at converse relationship column \"{$converse_key}\"");
				}
				$converse_datum = $struct->getColumn($converse_key);
				$mapping = $converse_datum->getRelationshipType();
				if ($print) {
					Debug::print("{$f} datum \"{$converse_key}\" has relationship type {$mapping}");
				}
			}elseif($column->hasRelationshipType()) {
				$mapping = $column->getConverseRelationshipType();
				if ($print) {
					Debug::print("{$f} datum \"{$column_name}\" is mapped by {$mapping}\"");
				}
			} else {
				Debug::error("{$f} data structure does not have a datum for converse relationship \"{$converse_key}\", and datum at column \"{$column_name}\" does not know how many objects map to it");
			}
			switch ($mapping) {
				case RELATIONSHIP_TYPE_ONE_TO_ONE:
					if($struct->hasColumn($converse_key) && !$struct->getColumn($converse_key)->hasConverseRelationshipKeyName()){
						if($print){
							Debug::print("{$f} for whatever reason, 1:1 relationship between {$column_name} and {$converse_key} does not know its converse relationship key name, fixing that now");
						}
						$struct->getColumn($converse_key)->setConverseRelationshipKeyName($column_name);
					}
				case RELATIONSHIP_TYPE_MANY_TO_ONE:
					if ($print) {
						Debug::print("{$f} {$column_name} is a one to one or many to one relationship");
					}
					if (! $struct->hasForeignDataStructure($converse_key) || ($struct->hasColumn($converse_key) && $this->hasIdentifierValue() && $struct->getColumnValue($converse_key) !== $this->getIdentifierValue())) {
						if ($print) {
							if (! $struct->hasForeignDataStructure($converse_key)) {
								Debug::print("{$f} struct does not have a foreign data structure mapped to \"{$converse_key}\"");
							} else {
								Debug::print("{$f} struct has something mapped to \"{$converse_key}\"");
							}
							if ($struct->hasColumn($converse_key)) {
								Debug::print("{$f} struct has a column \"{$converse_key}\"");
								$converse_value = $struct->getColumnValue($converse_key);
								$identifier = $this->hasIdentifierValue() ? $this->getIdentifierValue() : "undefined";
								if ($converse_value !== $identifier) {
									Debug::print("{$f} struct's value \"{$converse_value}\" for column \"{$converse_key}\" differs from this object's identifier \"{$identifier}\"");
								} else {
									Debug::print("{$f} struct's value \"{$converse_value}\" for column \"{$converse_key}\" is identical to this object's identifier");
								}
								Debug::print("{$f} mapping this object to foreign data structure's converse relationship key \"{$converse_key}\"");
							} else {
								Debug::print("{$f} struct does not have a column \"{$converse_key}\"");
							}
						}
						$struct->setForeignDataStructure($converse_key, $this);
					}elseif($print) {
						Debug::print("{$f} foreign data structure has already mapped this object to converse relationship key \"{$converse_key}\"");
					}
					break;
				case RELATIONSHIP_TYPE_ONE_TO_MANY:
				case RELATIONSHIP_TYPE_MANY_TO_MANY:
					if ($print) {
						Debug::print("{$f} {$column_name} is a one to many or many to many relationship");
					}
					$closure = function (?AfterGenerateKeyEvent $event, DataStructure $target) use ($struct, $converse_key, $f, $print, $column_name) {
						if (! $struct->hasForeignDataStructureListMember($converse_key, $target->getIdentifierValue())) {
							if ($print) {
								Debug::print("{$f} mapping this object as a member of foreign data structure's key list at converse relationship column \"{$converse_key}\"");
							}
							$struct->setForeignDataStructureListMember($converse_key, $target);
						}elseif($print) {
							Debug::print("{$f} foreign data structure at column \"{$column_name}\" already has this object as a member of its key list at converse relationship column \"{$converse_key}\"");
						}
					};
					if ($this->hasIdentifierValue()) {
						if ($print) {
							Debug::print("{$f} identifier value is defined, calling closure immediately");
						}
						$closure(null, $this);
					} else {
						if ($print) {
							Debug::print("{$f} identifier value is undefined. Adding AfterGenerateKeyEvent listener");
						}
						$this->addEventListener(EVENT_AFTER_GENERATE_KEY, $closure);
					}
					break;
				default:
					Debug::error("{$f} invalid mapping \"{$mapping}\"");
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setBlockInsertionFlag(bool $value = true): bool{
		return $this->setFlag("blockInsertion", true);
	}

	public function getBlockInsertionFlag(): bool{
		return $this->getFlag("blockInsertion");
	}

	public function blockInsertion(bool $value = true): DataStructure{
		$this->setFlag("blockInsertion", $value);
		return $this;
	}

	public function hasForeignDataStructureListMember(string $column_name, $key): bool{
		return $this->hasForeignDataStructureList($column_name) && array_key_exists($key, $this->foreignDataStructures[$column_name]);
	}

	public function hasOldDataStructureListMember($column_name, $key){
		return $this->hasOldDataStructureList($column_name) && array_key_exists($key, $this->oldDataStructures[$column_name]);
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
					$this->getColumn($column_name)->dispose();
					$this->unsetArrayPropertyValue("columns", $column_name);
				}
			}elseif($print){
				Debug::print("{$f} there are no columns to trim");
			}
			$this->setTrimmedFlag(true);
			if($foreign && $recursion_depth > 0){
				if(
					isset($this->foreignDataStructures)
					&& is_array($this->foreignDataStructures)
					&& !empty($this->foreignDataStructures)
				){
					foreach($this->foreignDataStructures as /*$key =>*/ $value){
						if(is_array($value)){
							foreach($value as $fds){
								if($fds->getTrimmedFlag()){
									continue;
								}
								$fds->trimUnusedColumns($foreign, $recursion_depth-1);
							}
						}else{
							if($value->getTrimmedFlag()){
								continue;
							}
							$value->trimUnusedColumns($foreign, $recursion_depth-1);
						}
					}
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
		if ($obj1->getClass() !== $obj2->getClass()) {
			if ($print) {
				Debug::print("{$f} class differs, returning false");
			}
			return false;
		}
		$columns1 = $obj1->getFilteredColumns(COLUMN_FILTER_COMPARABLE);
		$columns2 = $obj2->getFilteredColumns(COLUMN_FILTER_COMPARABLE);
		if (count($columns1) !== count($columns2)) {
			if ($print) {
				Debug::print("{$f} column count differs");
			}
			return false;
		}
		foreach ($columns1 as $column_name => $column) {
			if ($column instanceof VirtualDatum) {
				continue;
			}elseif($column->getIgnoreInequivalenceFlag()) {
				continue;
			}elseif(! $obj2->hasColumn($column_name)) {
				if ($print) {
					Debug::print("{$f} second object lacks a datum at column \"{$column_name}\"");
				}
				return false;
			}
			$value1 = $column->getValue();
			$value2 = $obj2->getColumnValue($column_name);
			if ($value1 !== $value2) {
				if ($print) {
					Debug::print("{$f} values differ ({$value1} vs {$value2}) for datum \"{$column_name}\"");
				}
				return false;
			}
		}
		if ($print) {
			Debug::print("{$f} returning true");
		}
		return true;
	}

	protected function beforeSetForeignDataStructureHook(string $column_name, DataStructure $struct):int{
		$this->dispatchEvent(new BeforeSetForeignDataStructureEvent($column_name, $struct));
		return SUCCESS;
	}

	protected function afterSetForeignDataStructureHook(string $column_name, DataStructure $struct):int{
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		if($this->hasColumn($column_name)){
			$column = $this->getColumn($column_name);
			if ($column->hasConverseRelationshipKeyName()) {
				if ($print) {
					Debug::print("{$f} datum at column \"{$column_name}\" has an converse relationship key name");
				}
				$this->reciprocateRelationship($column_name, $struct);
				if ($print) {
					$did = $this->getDebugId();
					Debug::print("{$f} returned from reciprocating relationship \"{$column_name}\"; debug ID is \"{$did}\"");
				}
			}elseif($print) {
				Debug::print("{$f} datum at column \"{$column_name}\" does not have an converse relationship key name");
			}
		}elseif($print){
			Debug::print("{$f} no, this object does not have a column \"{$column_name}\"");
		}
		$this->dispatchEvent(new AfterSetForeignDataStructureEvent($column_name, $struct));
		return SUCCESS;
	}

	public function withForeignDataStructure($column_name, $struct): DataStructure{
		$this->setForeignDataStructure($column_name, $struct);
		return $this;
	}

	/**
	 * Sets $struct as a member of $this->foreignDataStructures at index $column_name.
	 * Also automates a recipricating the relationship.
	 * Can be intercepted/extended by redeclaring before and afterSetForeignDataStructureHook()
	 *
	 * @param string $column_name
	 * @param DataStructure $struct
	 * @return DataStructure
	 */
	public /*final*/ function setForeignDataStructure(string $column_name, DataStructure $struct): ?DataStructure{
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				$did = $this->getDebugId();
			}
			if (! isset($struct)) {
				Debug::error("{$f} received a null data structure");
			}elseif(is_array($struct)) {
				Debug::error("{$f} don't call this on arrays");
				return $this->setForeignDataStructureList($column_name, $struct);
			}elseif(! is_object($struct)) {
				$gottype = gettype($struct);
				Debug::error("{$f} struct is a {$gottype}, not an object");
			}elseif(! $struct instanceof DataStructure) {
				$class = $struct->getClass();
				Debug::error("{$f} struct is a \"{$class}\"");
			}elseif($struct->isDeleted()) {
				if ($print) {
					Debug::print("{$f} data structure passed for foreign relationship \"{$column_name}\" is deleted or not found");
				}
				$key = $struct->getIdentifierValue(); // Key();
				if ($print) {
					Debug::error("{$f} data structure with key \"{$key}\" is deleted");
				}
				if ($this->hasForeignDataStructure($column_name)) {
					$this->ejectForeignDataStructure($column_name);
				}
				return null;
			}elseif($struct->hasObjectStatus() && $struct->getObjectStatus() === ERROR_NOT_FOUND) {
				if ($print) {
					Debug::print("{$f} data structure passed for foreign relationship \"{$column_name}\" was not found");
				}
				$key = $struct->getIdentifierValue();
				if ($print) {
					$sc = get_short_class($struct);
					Debug::error("{$f} data structure of class {$sc} with key \"{$key}\" not found");
				}
				if ($this->hasForeignDataStructure($column_name)) {
					$this->ejectForeignDataStructure($column_name);
				}
				return null;
			}elseif($this->hasColumn($column_name)) {
				if ($print) {
					Debug::print("{$f} this object has a column \"{$column_name}\"");
				}
				$column = $this->getColumn($column_name);
				if ($column instanceof KeyListDatum) {
					Debug::error("{$f} don't call this function on KeyListDatum \"{$column_name}\"");
					// return $this->setForeignDataStructureListMember($column_name, $struct);
				}elseif(! $column instanceof ForeignKeyDatum) {
					Debug::error("{$f} don't call this on something other than foreign key datums. Column name was \"{$column_name}\"");
				}
			}elseif($print) {
				Debug::print("{$f} this object does not have a column \"{$column_name}\"");
			}
			if(!isset($this->foreignDataStructures) || !is_array($this->foreignDataStructures)) {
				$this->foreignDataStructures = [];
			}
			$status = $this->beforeSetForeignDataStructureHook($column_name, $struct);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} before set foreign data structure hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$this->foreignDataStructures[$column_name] = $struct;
			if (! $this->hasForeignDataStructure($column_name)) {
				Debug::error("{$f} immediately after setting foreign data structure \"{$column_name}\" it is undefined");
			}elseif($this->hasColumn($column_name)) {
				$column = $this->getColumn($column_name);
				if (! $struct->hasIdentifierValue()) {
					if ($print) {
						Debug::print("{$f} foreign data structure at column \"{$column_name}\" does not have a key");
					}
				}elseif($this->getReceptivity() !== DATA_MODE_SEALED) {
					$key = $struct->getIdentifierValue(); // Key();
					if ($print) {
						Debug::print("{$f} assigning key \"{$key}\" to column \"{$column_name}\"");
					}
					$this->setColumnValue($column_name, $key);
					if($column->applyFilter(COLUMN_FILTER_FOREIGN, COLUMN_FILTER_INTERSECTION)) {
						if ($column->hasForeignDataTypeName()) {
							$this->setColumnValue($column->getForeignDataTypeName(), $struct->getDataType());
						}
						if($struct->hasColumnValue('subtype') || $struct instanceof StaticSubtypeInterface){
							$subtype = $struct->getSubtype();
							$this->setColumnValue($column->getForeignDataSubtypeName(), $subtype);
						}
					}elseif($print) {
						Debug::print("{$f} datum \"{$column_name}\" is not a foreign key datum interface, or is not polymorphic");
					}
				}elseif($print) {
					Debug::print("{$f} this object has been sealed");
				}
				//moved reciprocation into afterSetForeignDataStructureHook
			}elseif($print) {
				Debug::print("{$f} this object does not have a datum at column \"{$column_name}\"");
			}
			$status = $this->afterSetForeignDataStructureHook($column_name, $struct);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} after set foreign data structure hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print) {
				Debug::print("{$f} successfully set foreign data structure \"{$column_name}\" for object with debug ID \"{$did}\"");
			}
			return $struct;
		} catch (Exception $x) {
			x($f, $x);
		}
	}
	
	/**
	 * Select all objects of this class from the database that satisfy $select for parameters $params
	 *
	 * @param mysqli $mysqli
	 * @param SelectStatement $select
	 * @param string $typedef
	 * @param array $params
	 * @return DataStructure[]
	 */
	public static function loadMultiple(mysqli $mysqli, SelectStatement $select, string $typedef = null, ...$params): ?array{
		$f = __METHOD__;
		try {
			$print = false;
			if ($typedef !== null) {
				$select->setTypeSpecifier($typedef);
			}
			if (isset($params)) {
				$select->setParameters($params);
			}
			$result = $select->executeGetResult($mysqli);
			if ($result === null) {
				if ($print) {
					Debug::print("{$f} executeGetResult returned null -- there are no objects to load");
				}
				return null;
			}elseif(! is_object($result)) {
				$gottype = gettype($result);
				Debug::error("{$f} executeQueryGetResult returned {$gottype}");
			}elseif($result->num_rows === 0) {
				return [];
			}
			$results = $result->fetch_all(MYSQLI_ASSOC);
			$class = static::class;
			$arr = [];
			foreach ($results as $result) {
				$obj = new $class();
				$status = $obj->processQueryResultArray($mysqli, $result);
				$id = $obj->getIdentifierValue();
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} processQueryResultArray returned error status \"{$err}\" for object with ID \"{$id}\"");
					return [];
				}
				$arr[$id] = $obj;
			}
			$status = LazyLoadHelper::loadIntersectionTableKeys($mysqli, $arr);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} LazyLoadHelper::loadIntersectionTableKeys returned error status \"{$err}\"");
			}
			return $arr;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function beforeLoadHook(mysqli $mysqli): int{
		$this->dispatchEvent(new BeforeLoadEvent());
		return SUCCESS;
	}

	public function afterLoadHook(mysqli $mysqli): int{
		$this->dispatchEvent(new AfterLoadEvent());
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
		
		if (! array_key_exists("relationship", $arr)) {
			Debug::warning("{$f} array does not have a foreign key name");
			Debug::printArray($arr);
			Debug::printStackTrace();
		}elseif($print) {
			Debug::print("{$f} entered with the following array");
			Debug::printArray($arr);
		}
		$fkn = $arr['relationship'];
		if (! $this->hasColumn($fkn)) {
			Debug::error("{$f} this object does not have a datum at column \"{$fkn}\"");
		}
		$column = $this->getColumn($fkn);
		if ($column instanceof ForeignKeyDatum) {
			$column->setValueFromQueryResult($arr["foreignKey"]);
		}elseif($column instanceof KeyListDatum) {
			if ($print) {
				Debug::print("{$f} column is a key list");
			}
			$fk = $arr['foreignKey'];
			$column->pushValueFromQueryResult($fk);
			if (registry()->has($fk)) {
				if ($print) {
					Debug::print("{$f} registry has an object with key \"{$fk}\"");
				}
				$fds = registry()->get($fk);
				if ($fds->getLoadedFlag()) {
					$this->setForeignDataStructureListMember($fkn, $fds);
				}elseif($print) {
					Debug::print("{$f} ... but it wasn't loaded yet");
				}
			}elseif($print) {
				Debug::print("{$f} the registry does not know anything about an object with key \"{$fk}\"");
			}
		} else {
			$cc = $column->getClass();
			Debug::error("{$f} illegal column class \"{$cc}\"");
		}
		if (cache()->enabled()) {
			$column->setDirtyCacheFlag(true);
		}
		return SUCCESS;
	}

	/**
	 * XXX replace this pile
	 *
	 * @return NULL|string[]
	 */
	public function getLoadableIntersectionTableNames(): ?array{
		$f = __METHOD__;
		try {
			$print = false;
			$columns = $this->getFilteredColumns(COLUMN_FILTER_POTENTIAL);
			if (empty($columns)) {
				if ($print) {
					Debug::print("{$f} no polymorphic key datums with values");
				}
				return null;
			}
			$map = [];
			$dsc = $this->getClass();
			$type1 = $this->getTableName();
			foreach ($columns as $column_name => $column) {
				if ($column->hasForeignDataStructureClass()) {
					$fdsc = $column->getForeignDataStructureClass();
					if(!method_exists($fdsc, 'getTableNameStatic')){
						Debug::error("{$f} table name cannot be determined statically for foreign data structure class \"{$fdsc}\"");
					}
					$table2 = $fdsc::getTableNameStatic();
				}elseif($column->hasForeignDataStructureClassResolver()) {
					$resolver = $column->getForeignDataStructureClassResolver();
					if(
						$column instanceof ForeignKeyDatumInterface && (
							$column->hasForeignDataType() || (
								$column->hasForeignDataSubtypeName() && 
								$column->hasForeignDataSubtype()
							)
						)
					){
						if ($print) {
							$key = $this->hasIdentifierValue() ? $this->getIdentifierValue() : "undefined";
							Debug::print("{$f} about to call {$resolver}::resolveClass() for column \"{$column_name}\" of {$dsc} with key \"{$key}\"");
						}
						$fdsc = $resolver::resolveClass($column);
						if(!method_exists($fdsc, 'getTableNameStatic')){
							Debug::error("{$f} table name cannot be determined statically for foreign data structure class \"{$fdsc}\"");
						}
						$table2 = $fdsc::getTableNameStatic();
					} else {
						if ($print) {
							Debug::print("{$f} column \"{$column_name}\" has its foreign data structre class resolver, but does not have a foreign datatype or subtype");
						}
						$intersections = $resolver::getAllPossibleIntersectionData($column);
						foreach ($intersections as $intersection) {
							$ftn = new FullTableName($intersection->getDatabaseName(), $intersection->getTableName());
							$map[$ftn->toSQL()] = $ftn; // array_push($map, $ftn);
						}
						continue;
					}
				} else {
					Debug::print("{$f} column \"{$column_name}\" lacks a foreign data structure class or resolver");
				}
				$ftn = new FullTableName("intersections", "{$type1}_{$table2}");
				$map[$ftn->toSQL()] = $ftn; // array_push($map, "intersections.{$type1}_{$table2}");
			}
			if (empty($map)) {
				if ($print) {
					Debug::print("{$f} returning null");
				}
				return null;
			}
			return $map;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getCreateTableStatement(): CreateTableStatement{
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		$create = CreateTableStatement::fromTableDefinition($this);
		if($print){
			Debug::print("{$f} returning the following: ".$create->toSQL());
		}
		return $create;
	}

	protected function beforeCreateTableHook(mysqli $mysqli): int
	{
		$this->dispatchEvent(new BeforeCreateTableEvent());
		return SUCCESS;
	}

	protected function afterCreateTableHook(mysqli $mysqli): int{
		$this->dispatchEvent(new AfterCreateTableEvent());
		return SUCCESS;
	}

	public static function getCreateTableStatementStatic(): CreateTableStatement{
		$f = __METHOD__;
		$dummy = new static();
		$columns1 = $dummy->getFilteredColumns(DIRECTIVE_CREATE_TABLE);
		$columns2 = [];
		foreach ($columns1 as $column) {
			$column->setDataStructureClass(static::class);
			$columns2[$column->getName()] = $column;
		}
		if(!method_exists(static::class, 'getTableNameStatic')){
			Debug::error("{$f} table name cannot be determined statically for class \"".static::getShortClass()."\"");
		}
		return QueryBuilder::createTable(static::getDatabaseNameStatic(), static::getTableNameStatic())->withColumns(array_values($columns2));
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
		try {
			$print = false;
			$status = SUCCESS; // $this->permit(user(), DIRECTIVE_CREATE_TABLE);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} permission returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			// pre-table creation hook
			$status = $this->beforeCreateTableHook($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} before create table hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			// create table
			$create = $this->getCreateTableStatement();
			if ($print) {
				Debug::print("{$f} create table statement is " . $create->toSQL());
			}
			$status = $create->executeGetStatus($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} executing table creation query returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			// post-create table hook
			$status = $this->afterCreateTableHook($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} after create table hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function createTableStatic(mysqli $mysqli):int{
		$data = new static();
		return $data->createTable($mysqli);
	}
	
	/**
	 * debug function for automatically creating embedded and intersection tables
	 *
	 * @param mysqli $mysqli
	 * @return string
	 */
	public function createAssociatedTables(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$print = $this->getDebugFlag();
			if ($mysqli->connect_errno) {
				Debug::error("{$f} Failed to connect to MySQL: ({$mysqli->connect_errno}) {$mysqli->connect_error}");
			}elseif(! $mysqli->ping()) {
				Debug::error("{$f} mysqli connection failed ping test: \"" . $mysqli->error . "\"");
			}
			if ($this instanceof IntersectionData) {
				Debug::error("{$f} don't call this on intersection data");
			}elseif(! $this->tableExists($mysqli)) {
				Debug::warning("{$f} table doesn't exist, what's the point");
				return SUCCESS;
			}
			// tables for embedded columns
			$embedded = $this->getEmbeddedDataStructures();
			if (! empty($embedded)) {
				if($print){
					$count = count($embedded);
					Debug::print("{$f} {$count} embedded data structures");
				}
				foreach ($embedded as $e) {
					if($print){
						$e->debug();
					}
					$db = $e->getDatabaseName();
					$tableName = $e->getTableName();
					if (! QueryBuilder::tableExists($mysqli, $db, $tableName)) {
						if ($print) {
							Debug::print("{$f} table {$db}.{$tableName} does not yet exist. It has the following columns:");
							$e->debugPrintColumns(null, false);
						}
						$status = $e->createTable($mysqli);
						if ($status !== SUCCESS) {
							$err = ErrorMessage::getResultMessage($status);
							Debug::error("{$f} creating embedded table \"{$db}.{$tableName}\" returned error status \"{$err}\"");
							return $status;
						}elseif($print) {
							Debug::print("{$f} successfully created new embedded table \"{$db}.{$tableName}\"");
						}
					}elseif($print) {
						Debug::print("{$f} embedded table \"{$db}.{$tableName}\" already exists");
					}
				}
			}elseif($print) {
				Debug::print("{$f} no embedded data structures");
			}
			// intersection tables for polymorphic foreign key columns
			$polys = $this->getFilteredColumns(COLUMN_FILTER_INTERSECTION);
			if (! empty($polys)) {
				if ($print) {
					$count = count($polys);
					Debug::print("{$f} {$count} intersection tables");
				}
				foreach ($polys as $name => $poly) {
					if ($print) {
						Debug::print("{$f} creating intersection table for column \"{$name}\"");
					}
					$status = $poly->createIntersectionTables($mysqli);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} creating intersection table for datum \"{$name}\" returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}elseif($print) {
						Debug::print("{$f} successfully created intersection table for column \"{$name}\"");
					}
				}
			}elseif($print) {
				Debug::print("{$f} no foreign keys stored in intersection tables");
			}
			// event source tables
			$fsc = $this->getFilteredColumns(COLUMN_FILTER_EVENT_SOURCE);
			if (! empty($fsc)) {
				foreach ($fsc as $name => $column) {
					if ($print) {
						Debug::print("{$f} about to create intersection table for event source of column \"{$name}\"");
					}
					$event_src = new EventSourceData($column);
					if (! QueryBuilder::tableExists($mysqli, EventSourceData::getDatabaseNameStatic(), $event_src->getTableName())) {
						$status = $event_src->createTable($mysqli);
						if ($status !== SUCCESS) {
							$err = ErrorMessage::getResultMessage($status);
							Debug::error("{$f} creating event source table \"{$db}.{$tableName}\" for column \"{$name}\" returned error status \"{$err}\"");
							return $status;
						}elseif($print) {
							Debug::print("{$f} successfully created new event source table \"{$db}.{$tableName}\" for column \"{$name}\"");
						}
						$status = $event_src->createAssociatedTables($mysqli);
						if ($status !== SUCCESS) {
							$err = ErrorMessage::getResultMessage($status);
							Debug::error("{$f} creating associated tables for event source table \"{$db}.{$tableName}\" for column \"{$name}\" returned error status \"{$err}\"");
							return $status;
						}elseif($print) {
							Debug::print("{$f} successfully created associated tables for event source table \"{$db}.{$tableName}\" for column \"{$name}\"");
						}
					}elseif($print) {
						Debug::print("{$f} event source table \"{$db}.{$tableName}\" for column \"{$name}\" already exists");
					}
				}
			}
			return SUCCESS;
		} catch (Exception $x) {
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
		try {
			$print = false;
			
			if(!$this->isUninitialized()){
				Debug::error("{$f} this shouldn't be getting called on loaded objects");
			}elseif($this->getReceptivity() === DATA_MODE_RECEPTIVE){
				Debug::error("{$f} this shouldn't be getting called on receptive objects");
			}
			
			$hostKeyNames = $this->getLoadableIntersectionTableNames();
			if (empty($hostKeyNames)) {
				if ($print) {
					Debug::print("{$f} there are no intersection tables to load");
				}
				return SUCCESS;
			}
			$key = $this->getIdentifierValue();
			foreach ($hostKeyNames as $intersectionTableName) {
				$select = new SelectStatement();
				$select->from(
					$intersectionTableName->getDatabaseName(), 
					$intersectionTableName->getTableName()
				)->where(new WhereCondition("hostKey", OPERATOR_EQUALS));
				$result = $select->prepareBindExecuteGetResult($mysqli, 's', $key);
				$count = $result->num_rows;
				if ($count == 0) {
					if ($print) {
						Debug::warning("{$f} query statement \"{$select}\" returned 0 results");
					}
					continue;
				}
				$results = $result->fetch_all(MYSQLI_ASSOC);
				$result->free_result();
				foreach ($results as $result) {
					$status = $this->processIntersectionTableQueryResultArray($result);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} processIntersectionTableQueryResultArray on object with key \"{$key}\" returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}elseif($print) {
						Debug::print("{$f} successfully processed intersection table query results for object with key \"{$key}\"");
					}
				}
			}
			if (CACHE_ENABLED && $this->isRegistrable() && $this->hasIdentifierValue() && $this->hasTimeToLive()) {
				$key = $this->getIdentifierValue();
				if (cache()->hasAPCu($key)) {
					$cached = cache()->getAPCu($key);
					$columns = $this->getFilteredColumns(COLUMN_FILTER_DIRTY_CACHE);
					if (! empty($columns)) {
						foreach ($columns as $column_name => $column) {
							$cached[$column_name] = $column->getDatabaseEncodedValue();
							$column->setDirtyCacheFlag(false);
						}
					}elseif($print) {
						Debug::print("{$f} there are no dirty cache flagged columns");
					}
					cache()->setAPCu($key, $cached, $this->getTimeToLive());
				}elseif($print) {
					Debug::print("{$f} there is no cached value with key \"{$key}\"");
				}
			}elseif($print) {
				Debug::print("{$f} cache is not enabled");
			}
			return SUCCESS;
		} catch (Exception $x) {
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
		try {
			$print = $this->getDebugFlag();
			if ($print) {
				Debug::print("{$f} entered; about to process the following values:");
				Debug::printArray($arr);
			}
			$status = $this->beforeLoadHook($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} before load hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$columns = $this->getColumns();
			foreach ($columns as $vn => $t) {
				if (array_key_exists($vn, $arr)) {
					$pm = $t->getPersistenceMode();
					switch ($pm) {
						case PERSISTENCE_MODE_ALIAS:
						case PERSISTENCE_MODE_DATABASE:
						case PERSISTENCE_MODE_EMBEDDED:
						case PERSISTENCE_MODE_ENCRYPTED:
							break;
						case PERSISTENCE_MODE_COOKIE:
						case PERSISTENCE_MODE_SESSION:
							// case PERSISTENCE_MODE_VOLATILE:
							Debug::error("{$f} column \"{$vn}\" has invalid persistence mode ".Debug::getPersistenceModeString($pm));
							cache()->clearAPCu(); // XXX TODO delete this
						default:
							Debug::warning("{$f} column \"{$vn}\" has unusual persistence mode ".Debug::getPersistenceModeString($pm));
					}
					if ($print) {
						Debug::print("{$f} about to call setValueFromQueryResult for column \"{$vn}\"");
					}
					$t->setValueFromQueryResult($arr[$vn]);
				}elseif($print) {
					Debug::print("{$f} column \"{$vn}\" was not loaded from the database, and is not mandatory");
				}
			}
			// load queried columns
			if (method_exists($this, "setLoadedFlag")) {
				$this->setLoadedFlag(true);
			}
			$status = $this->afterLoadHook($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} after load hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}

			if ($print) {
				Debug::print("{$f} returning normally");
			}
			return SUCCESS; //do NOT set object status here
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getAliasedColumnSelectStatement(string $column_name, $key=null):SelectStatement{
		$f = __METHOD__;
		try{
			$aliased_column = $this->getColumn($column_name);
			if($key === null){
				$key = $aliased_column->getValue();
			}
			$subqueryClass = $aliased_column->getSubqueryClass();
			if(!method_exists($subqueryClass, 'getTableNameStatic')){
				Debug::error("{$f} table name cannot be determined statically for subquery class \"{$subqueryClass}\"");
			}
			$converse_keyname = $aliased_column->getConverseRelationshipKeyName();
			return $this->select()->where(
				new WhereCondition(
					$this->getIdentifierName(),
					OPERATOR_EQUALS,
					null,
					$subqueryClass::generateLazyAliasExpression(
						static::class,
						$converse_keyname,
						QueryBuilder::select($subqueryClass::getIdentifierNameStatic())->from(
							$subqueryClass::getDatabaseNameStatic(),
							$subqueryClass::getTableNameStatic()
						)->where(
							new WhereCondition($column_name, OPERATOR_EQUALS)
						)->escape(ESCAPE_TYPE_PARENTHESIS)
					)
				)
			)->withTypeSpecifier($aliased_column->getTypeSpecifier()."s")->withParams([
				$key,
				$converse_keyname
			]);
		}catch(Exception $x){
			x($f, $x);
		}
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
		$f = __METHOD__;
		try {
			$print = $this->getDebugFlag();
			if($print){
				Debug::print("{$f} data structure class is ".$this->getShortClass());
			}
			$typedef = "";
			if (! is_array($params)) {
				$params = [
					$params
				];
			}
			if (is_string($where)) {
				if ($this->hasColumn($where) && $this->getColumn($where)->getPersistenceMode() === PERSISTENCE_MODE_ALIAS) {
					if ($print) {
						Debug::error("{$f} where condition is an aliased column name");
					}
					// $select = $this->getAliasedColumnSelectStatement($where, $params[0]);
				} else {
					if ($print) {
						Debug::print("{$f} where condition is the string \"{$where}\"");
					}
					$select = $this->select()->where(new WhereCondition($where));
				}
			} else if($where instanceof WhereCondition){
				if ($print) {
					Debug::print("{$f} where condition is not a string");
				}
				$select = $this->select()->where($where);
			}else{
				Debug::error("{$f} where condition is neither a string nor a WhereCondition");
			}
			// generate parameter list
			if (! empty($params)) {
				if (! $select->hasTypeSpecifier()) {
					$conditions = $select->getSuperflatWhereConditionArray();
					foreach ($conditions as $i => $condition) {
						if ($condition->hasUnbindableOperator()) {
							if ($print) {
								Debug::print("{$f} parameter at column {$i} is null");
							}
							continue;
						}
						$column_name = $condition->getColumnName();
						if ($print) {
							Debug::print("{$f} about to get type specifier for column \"{$column_name}\"");
						}
						if ($this->hasColumn($column_name)) {
							$typedef .= $this->getColumn($column_name)->getTypeSpecifier();
						} else { // condition is accomplice to an aliased column
							$ts = $condition->getTypeSpecifier();
							if (empty($ts)) {
								Debug::error("{$f} type specifier is empty string");
							}
							$typedef .= $condition->getTypeSpecifier();
						}
					}
					if (! empty($params) || ! empty($typedef)) {
						$length = strlen($typedef);
						$count = isset($params) ? count($params) : 0;
						if ($length !== $count) {
							$where = $select->getWhereCondition();
							if($where->hasSelectStatement()){
								$where->setParameterCount($count);
							}
							Debug::error("{$f} type specifier \"{$typedef}\" length {$length} does not match parameter count {$count} in query statement \"{$select}\"");
						}
						$select->setParameters($params);
						$select->setTypeSpecifier($typedef);
					}
				}elseif(! $select->hasParameters()) {
					$select->setParameters($params);
				}
			}
			// order by expression
			if (isset($order_by)) {
				$select->setOrderBy($order_by);
			}elseif($print) {
				Debug::print("{$f} order by expressions is undefined");
			}
			// limit
			if (isset($limit)) {
				if ($limit !== 1) {
					Debug::error("{$f} you can only specify a limit of 1");
				}
				$select->setLimit(1);
			}
			// prepare, bind parameters, execute, fetch results
			if ($print) {
				Debug::print("{$f} about to execute query \"{$select}\"");
			}
			$result = $select->executeGetResult($mysqli); // prepareBindExecuteGetResult($mysqli, $typedef, ...$bind_params);
			if ($result == null) {
				if ($print) {
					Debug::error("{$f} result is null");
				}
				return $this->loadFailureHook();
			}elseif(! is_object($result)) {
				$gottype = gettype($result);
				Debug::error("{$f} executeGetResult returned a {$gottype}");
			}
			$count = $result->num_rows;
			if ($count === 0) {
				if ($print) {
					Debug::print("{$f} object not found");
				}
				return $this->loadFailureHook();
			}elseif($count > 1) {
				$decl = $select->getDeclarationLine();
				$did = $select->getDebugId();
				Debug::error("{$f} multiple results for query \"{$select}\"! This function is for loading a single uniquely identifiable object. The select statement was declared {$decl} and has a debug ID {$did}.");
				return $this->setObjectStatus(ERROR_DUPLICATE_ENTRY);
			}
			$results = $result->fetch_all(MYSQLI_ASSOC);
			// processed fetched results
			$status = $this->processQueryResultArray($mysqli, $results[0]);
			// cache
			if (CACHE_ENABLED && $this->hasTimeToLive()) {
				$key = $this->getIdentifierValue();
				cache()->setAPCu($key, $results[0], $this->getTimeToLive());
			}elseif($print) {
				Debug::print("{$f} cache is disabled");
			}
			// load foreign keys stored in intersection tables
			if ($status === SUCCESS) {
				if (! $this instanceof IntersectionData) {
					if ($print) {
						Debug::print("{$f} this is not an intersection data -- about to load intersection table keys");
					}
					$status = $this->loadIntersectionTableKeys($mysqli);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} loadIntersectionTableKeys returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}
				}elseif($print) {
					Debug::print("{$f} skipping intersection table key load for intersection data");
				}
			} else {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} processQueryResultArray returned error status \"{$err}\"");
			}
			// register object to application global object registry
			if ($this->isRegistrable()) {
				$key = $this->getIdentifierValue();
				if (! registry()->hasObjectRegisteredToKey($key)) {
					registry()->registerObjectToKey($key, $this);
				}elseif($print) {
					Debug::print("{$f} an object is already mapped to key \"{$key}\"");
				}
			}elseif($print) {
				Debug::print("{$f} use case is undefined, or this object does not have a key");
				Debug::printStackTraceNoExit();
			}
			// post-loading hook has been moved to processQueryResultArray
			return $this->setObjectStatus($status);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setAutoloadFlags(bool $value = true, ?array $column_names = null): bool{
		$f = __METHOD__;
		$print = false;
		if ($column_names === null) {
			$filter2 = COLUMN_FILTER_AUTOLOAD;
			if ($value) {
				$filter2 = "!{$filter2}";
			}
			$column_names = $this->getFilteredColumnNames(COLUMN_FILTER_FOREIGN, "!" . COLUMN_FILTER_VOLATILE, $filter2);
		}
		foreach ($column_names as $name) {
			$c = $this->getColumn($name);
			if (! $value || $c->hasForeignDataStructureClass() || $c->hasForeignDataStructureClassResolver()) {
				if ($print) {
					if ($value) {
						Debug::print("{$f} flagging column \"{$name}\" for autoload");
					} else {
						Debug::print("{$f} flagging column \"{$name}\" for autoload disabled");
					}
				}
				$c->setAutoloadFlag($value);
			}elseif($print) {
				Debug::print("{$f} datum \"{$name}\" does not have a foreign data structure class and thus cannot be autoloaded");
			}
		}
		return $value;
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
		switch ($storage) {
			case PERSISTENCE_MODE_SESSION:
			case PERSISTENCE_MODE_COOKIE:
				break;
			default:
				Debug::error("{$f} this function can only be called on classes stored in superglobals");
		}
		$obj = new static();
		return $obj->unsetColumnValues(...$column_names);
	}

	public function isRegistrable(): bool{
		$idn = $this->getIdentifierName();
		return $idn !== null && $this->hasColumn($idn) && $this->getKeyGenerationMode() !== KEY_GENERATION_MODE_UNIDENTIFIABLE && $this->getKeyGenerationMode() !== KEY_GENERATION_MODE_NATURAL && $this->hasIdentifierValue();
	}

	public static function isRegistrableStatic(): bool{
		$idn = static::getIdentifierNameStatic();
		return $idn !== null && static::hasColumnStatic($idn) && static::getKeyGenerationMode() !== KEY_GENERATION_MODE_NATURAL;
	}

	/**
	 * returns foreign data structure list member at an integer offset (foreign data structure lists are associative)
	 *
	 * @param string $column_name
	 *        	: name of the foreign data structure list
	 * @param int $offset
	 *        	: e.g. 0 returns the first item
	 * @return mixed
	 */
	public function getForeignDataStructureListMemberAtOffset(string $column_name, int $offset): DataStructure{
		$f = __METHOD__;
		if (! $this->hasForeignDataStructureList($column_name)) {
			Debug::error("{$f} no foreign data structure list for column \"{$column_name}\"");
		}
		$keys = array_keys($this->getForeignDataStructureList($column_name));
		if (! array_key_exists($offset, $keys)) {
			Debug::error("{$f} invalid offset \"{$offset}\"");
		}
		$key = $keys[$offset];
		return $this->getForeignDataStructureListMember($column_name, $key);
	}

	public function setSuppressWarningsFlag(bool $value=true):bool{
		return $this->setFlag("suppressWarnings", $value);
	}
	
	public function getSuppressWarningsFlag():bool{
		return $this->getFlag("suppressWarnings");
	}
	
	public function suppressWarnings(bool $value=true):DataStructure{
		$this->setSuppressWarningsFlag($value);
		return $this;
	}
	
	/**
	 * load the foreign data structure at index $column_name
	 *
	 * @param mysqli $mysqli
	 * @param string $column_name
	 *        	: index of foreign data structure to load
	 * @param boolean $lazy
	 *        	: if true, lazy load the foreign data structure
	 * @param number $recursion_depth
	 *        	: if > 0, call recursively on foreign data structures with $recursion_depth-1
	 * @return NULL|DataStructure
	 */
	public function loadForeignDataStructure(mysqli $mysqli, string $column_name, bool $lazy = false, int $recursion_depth = 0): ?DataStructure{
		$f = __METHOD__;
		try {
			$print = $this->getDebugFlag();
			if ($print) {
				Debug::print("{$f} about to get column value \"{$column_name}\"");
			}
			$column = $this->getColumn($column_name);
			if(!$column->hasValue()){
				if ($print) {
					Debug::print("{$f} key at column \"{$column_name}\" is undefined");
				}
				if($column->hasConverseRelationshipKeyName() && $this->hasIdentifierValue()){
					if($print){
						Debug::print("{$f} we have enough information to load it anyway");
					}
					$struct_class = $column->getForeignDataStructureClass($this);
					$struct = new $struct_class();
					$crkn = $column->getConverseRelationshipKeyName();
					if(!$struct->hasColumn($crkn)){
						Debug::error("{$f} {$struct_class} does not have a column \"{$crkn}\"");
					}else{
						$pm = $struct->getColumn($crkn)->getPersistenceMode();
						switch($pm){
							case PERSISTENCE_MODE_DATABASE:
								if($print){
									Debug::print("{$f} goot news, it's stored in the database");
								}
								$struct->suppressWarnings();
								$this->setForeignDataStructure($column_name, $struct);
								if($print){
									$struct->debug();
								}
								$status = $struct->load($mysqli, $crkn, $this->getIdentifierValue());
								break;
							case PERSISTENCE_MODE_INTERSECTION:
								$struct->suppressWarnings();
								$this->setForeignDataStructure($column_name, $struct);
								$status = $struct->load(
									$mysqli, 
									$struct_class::whereIntersectionalForeignKey(static::class, $crkn), 
									[$this->getIdentifierValue(), $crkn]
								);
								break;
							default:
								if($print){
									Debug::print("{$f} unimplemented: using converse relationship key to load undefined foreign data structure {$column_name} with foreign key persistence mode \"".Debug::getPersistenceModeString($pm)."\"");
								}
								return null;
						}
						$struct->suppressWarnings();
						$this->ejectForeignDataStructure($column_name);
						$struct->suppressWarnings(false);
						if($status === ERROR_NOT_FOUND){
							if($print){
								Debug::print("{$f} not found");
							}
							return null;
						}elseif($status !== SUCCESS){
							$err = ErrorMessage::getResultMessage($status);
							Debug::warning("{$f} loading {$struct_class} by {$crkn} returned error status \"{$err}\"");
							return null;
						}elseif($print){
							Debug::print("{$f} successfully loaded foreign data structure {$column_name} by going through converse relationship key \"{$crkn}\"");
						}
						if($recursion_depth > 0){
							if ($print) {
								Debug::print("{$f} about to load data structures recursively");
							}
							$status = $struct->loadForeignDataStructures($mysqli, $lazy, $recursion_depth - 1);
							if($status !== SUCCESS){
								$err = ErrorMessage::getResultMessage($status);
								Debug::warning("{$f} recursively calling this function on foreign data structure with column \"{$column_name}\" returned error status \"{$err}\"");
								$struct->setObjectStatus($status);
							}
						}elseif($print) {
							Debug::print("{$f} recursion depth is 0");
						}
					}
				}else{
					if($print){
						Debug::print("{$f} nothing we can do here");
					}
					return null;
				}
			}else{
				$key = $this->getColumnValue($column_name);
				if($print) {
					Debug::print("{$f} key \"{$key}\" is stored in column \"{$column_name}\"");
				}
				// return if it was already registered
				if (registry()->hasObjectRegisteredToKey($key)) {
					if ($print) {
						Debug::print("{$f} the registry already knows about our {$column_name} with identifier \"{$key}\"");
					}
					$struct = registry()->getRegisteredObjectFromKey($key);
					if ($struct->isDeleted() || $struct->isNotFound()) {
						if ($print) {
							Debug::print("{$f} data structure is deleted unfortunately");
						}
						return null;
					}
					if ($print) {
						$struct_class = $struct->getClass();
						$did = $struct->getDebugId();
						$decl = $struct->getDeclarationLine();
						Debug::print("{$f} column \"{$column_name}\" maps to a {$struct_class} with debug ID \"{$did}\" instantiated on {$decl}");
					}
					$struct = $this->setForeignDataStructure($column_name, $struct);
					if (! $this->hasForeignDataStructure($column_name)) {
						Debug::error("{$f} immediately after setting foreign data structure at column \"{$column_name}\" it is undefined");
					}
					return $struct;
				}elseif($print) {
					Debug::print("{$f} nothing maps to object \"{$key}\"");
				}
				$column_class = $column->getClass();
				if ($print) {
					Debug::print("{$f} about to ask datum of class \"{$column_class}\" for the class of foreign data structure at column \"{$column_name}\"");
				}
				$struct_class = $column->getForeignDataStructureClass($this);
				if (empty($struct_class)) {
					Debug::error("{$f} struct class is undefined");
				}elseif(! class_exists($struct_class)) {
					Debug::error("{$f} struct class \"{$struct_class}\" does not exist");
				}elseif(is_abstract($struct_class)) {
					Debug::error("{$f} class \"{$struct_class}\" cannot be instantiated");
				}elseif(is_a($struct_class, ClassResolver::class, true)) {
					Debug::error("{$f} foreign data structure class is a ClassResolver");
				}elseif($print) {
					Debug::print("{$f} column \"{$column_name}\" maps to a {$struct_class}");
				}
				$struct = new $struct_class();
				$struct->setIdentifierValue($key);
				// load from cache
				if($struct->isRegistrable() && CACHE_ENABLED && $column->hasTimeToLive()){
					if(cache()->hasAPCu($key)){
						cache()->expireAPCu($key, $column->getTimeToLive());
						$results = cache()->getAPCu($key);
						$status = $struct->processQueryResultArray($mysqli, $results);
						if ($status !== SUCCESS) {
							$err = ErrorMessage::getResultMessage($status);
							Debug::warning("{$f} processing cached query results returned error status \"{$err}\"");
							$this->setObjectStatus($status);
							return null;
						}elseif($print) {
							Debug::print("{$f} successfully loaded cached foreign data structure \"{$column_name}\" with key \"{$key}\"");
						}
						// return $this->setForeignDataStructure($column_name, $struct);
						$struct->setLoadedFlag(true);
						$struct->setObjectStatus(SUCCESS);
					}elseif($print) {
						Debug::print("{$f} cache miss for foreign data structure with key \"{$key}\"");
					}
					$struct->setTimeToLive($column->getTimeToLive());
				}elseif($print) {
					Debug::print("{$f} foreign column does not have a cache duration");
				}
				if($lazy && !$column->getEagerLoadFlag()){
					if ($print) {
						Debug::print("{$f} lazy loading data structure \"{$column_name}\"");
					}
					// event handler for lazy recursive foreign data structure loading
					if ($recursion_depth > 0) {
						if ($print) {
							Debug::print("{$f} about to arm event handler for lazy recursive load");
						}
						$struct->addEventListener(EVENT_AFTER_LOAD, function (AfterLoadEvent $event, DataStructure $target) use ($mysqli, $recursion_depth, $f, $print){
							if ($print) {
								$tc = $target->getClass();
								Debug::print("{$f} lazy recursive loading foreign data structures for a {$tc}");
							}
							$target->removeEventListener($event);
							$status = $target->loadForeignDataStructures($mysqli, true, $recursion_depth - 1);
							if ($status !== SUCCESS) {
								$err = ErrorMessage::getResultMessage($status);
								Debug::warning("{$f} lazy recursive foreign data structure loading returned error status \"{$err}\"");
								$target->setObjectStatus($status);
							}
						});
					}elseif($print) {
						Debug::print("{$f} recursion depth is 0");
					}
					lazy()->deferLoad($struct);
				}else{
					if ($print) {
						Debug::print("{$f} we are not lazy loading this data structure");
					}
					$db = $struct->getDatabaseName();
					$table = $struct->getTableName();
					if ($struct->getLoadedFlag()) {
						$status = SUCCESS;
					} else {
						$status = $struct->loadFromKey($mysqli, $key);
					}
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} loading foreign data structure at column \"{$column_name}\" from table \"{$db}.{$table}\" returned error status \"{$err}\"");
						$struct->setObjectStatus($status);
					}elseif($recursion_depth > 0) {
						if ($print) {
							Debug::print("{$f} about to load data structures recursively");
						}
						$status = $struct->loadForeignDataStructures($mysqli, $lazy, $recursion_depth - 1);
						if ($status !== SUCCESS) {
							$err = ErrorMessage::getResultMessage($status);
							Debug::warning("{$f} recursively calling this function on foreign data structure with column \"{$column_name}\" returned error status \"{$err}\"");
							$struct->setObjectStatus($status);
						}
					}elseif($print) {
						Debug::print("{$f} recursion depth is 0");
					}
				}
			}
			if($print){
				Debug::print("{$f} loaded {$column_name} with key \"{$key}\"");
			}
			return $this->setForeignDataStructure($column_name, $struct);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * return the type specifier string for a column, or multiple columns
	 * if you don't provide any column names it will return the type specifier for all columns
	 *
	 * @param string[] ...$names
	 * @return string
	 */
	public function getColumnTypeSpecifier(...$names): string{
		$string = "";
		if (! isset($names) || empty($names)) {
			$names = $this->getColumnNames();
		}
		if (empty($names)) {
			return $string;
		} else
			foreach ($names as $cn) {
				$string .= $this->getColumn($cn)->getTypeSpecifier();
			}
		return $string;
	}

	/**
	 * load foreign data structure list indexed at KeyListDatum $column_name
	 *
	 * @param mysqli $mysqli
	 * @param string $column_name
	 * @param boolean $lazy
	 * @param number $recursion_depth
	 * @return int
	 */
	protected final function loadForeignDataStructureList(mysqli $mysqli, string $column_name, bool $lazy = false, int $recursion_depth = 0){
		$f = __METHOD__;
		try {
			$print = $this->getDebugFlag();
			$column = $this->getColumn($column_name);
			$type = $column->getRelationshipType();
			switch ($type) {
				case RELATIONSHIP_TYPE_ONE_TO_MANY:
				case RELATIONSHIP_TYPE_MANY_TO_MANY:
					if ($column->getLoadedFlag()) {
						if ($print) {
							Debug::print("{$f} key list \"{$column_name}\" has already been loaded");
							if (! $this->hasForeignDataStructureList($column_name)) {
								Debug::print("{$f} there is no foreign data structure list \"{$column_name}\"");
							} else {
								$count = $this->getForeignDataStructureCount($column_name);
								Debug::print("{$count} foreign data structures in list \"{$column_name}\"");
							}
						}
						return SUCCESS;
					}elseif(! $this->hasIdentifierValue()) {
						if ($print) {
							Debug::print("{$f} this piece of shit doesn't have a key yet");
						}
						return SUCCESS;
					}
					$keys = [];
					if ($print) {
						Debug::print("{$f} this is an X to many relationship and must be loaded from an intersection table");
					}
					$intersections = $column->getAllPossibleIntersectionData();
					foreach ($intersections as $intersection) {
						$fdsc = $intersection->getForeignDataStructureClass();
						$kgm = $fdsc::getKeyGenerationMode();
						switch($kgm){
							case KEY_GENERATION_MODE_HASH:
							case KEY_GENERATION_MODE_PSEUDOKEY:
								break;
							case KEY_GENERATION_MODE_LITERAL:
							case KEY_GENERATION_MODE_NATURAL:
							case KEY_GENERATION_MODE_UNIDENTIFIABLE:
							default:
								if($print){
									Debug::print("{$f} key generation mode {$kgm}, have to skip this one");
								}
								continue 2;
							
						}
						$db = $intersection->getDatabaseName();
						$table = $intersection->getTableName();
						$select = QueryBuilder::select("foreignKey")->from($db, $table)->where(new AndCommand(new WhereCondition("hostKey", OPERATOR_EQUALS), new WhereCondition("relationship", OPERATOR_EQUALS)));
						$select->setTypeSpecifier("ss");
						$params = [
							$this->getIdentifierValue(),
							$column_name
						];
						$select->setParameters($params);
						if ($print) {
							Debug::print("{$f} query for loading foreign keys from intersection table \"{$table}\" is \"{$select}\" with the following parameters:");
							Debug::printArray($params);
						}
						$result = $select->executeGetResult($mysqli);
						$results = $result->fetch_all(MYSQLI_ASSOC);
						$result->free_result();
						foreach ($results as $r) {
							$keys[$r['foreignKey']] = $intersection->getForeignDataStructureClass();
						}
					}
					if (! empty($keys)) {
						if ($print) {
							Debug::print("{$f} loaded the following keys for column \"{$column_name}\":");
							Debug::printArray($keys);
						}
						if (! $column->getRetainOriginalValueFlag()) {
							$column->retainOriginalValue();
						}
						$column->setOriginalValue(array_keys($keys));
					}elseif($print) {
						Debug::print("{$f} failed to load any keys for column \"{$column_name}\"");
					}
					if ($column->hasOriginalValue()) {
						$column->setValue($column->getOriginalValue());
					}
					break;
				default: // unused
					Debug::error("{$f} this should be impossible");
			}
			if (! empty($keys)) {
				foreach ($keys as $key => $struct_class) {
					if (is_array($key)) {
						Debug::error("{$f} value of column \"{$column_name}\" is a multidimensional array");
					}
					if ($print) {
						Debug::print("{$f} about to acquire a {$struct_class} with key \"{$key}\" for foreign data structure list \"{$column_name}\"");
					}
					if ($struct_class::isRegistrableStatic()) {
						if ($print) {
							Debug::print("{$f} class \"{$struct_class}\" is registrable");
						}
						if (registry()->has($key)) {
							$struct = registry()->getRegisteredObjectFromKey($key);
							if ($print) {
								$sc2 = $struct->getClass();
								Debug::print("{$f} there is already a {$sc2} mapped to key \"{$key}\". About to set a {$struct_class} with key \"{$key}\" for column \"{$column_name}\"");
							}
							$this->setForeignDataStructureListMember($column_name, $struct);
							continue;
						}elseif($print) {
							Debug::print("{$f} nothing registered for key \"{$key}\"");
						}
					}elseif($print) {
						Debug::print("{$f} data structure is not registrable");
					}
					$struct = new $struct_class();
					$struct->setIdentifierValue($key);
					if ($lazy) {
						if ($print) {
							Debug::print("{$f} about to defer loading of {$struct_class} with key \"{$key}\"");
						}
						lazy()->deferLoad($struct);
					} else {
						if ($print) {
							Debug::print("{$f} we are not lazy loading this data structure");
						}
						$status = $struct->load($mysqli, $struct_class::getIdentifierNameStatic(), $key);
						if ($status !== SUCCESS) {
							$err = ErrorMessage::getResultMessage($status);
							Debug::warning("{$f} loading foreign data structure returned error status \"{$err}\"");
							$struct->setObjectStatus($status);
						}elseif($recursion_depth > 0) {
							if ($print) {
								Debug::print("{$f} about to load data structures recursively");
							}
							$status = $struct->loadForeignDataStructures($mysqli, $lazy, $recursion_depth - 1);
							if ($status !== SUCCESS) {
								$err = ErrorMessage::getResultMessage($status);
								Debug::warning("{$f} recursively calling this function on foreign data structure with column \"{$column_name}\" returned error status \"{$err}\"");
								$struct->setObjectStatus($status);
							}
						}elseif($print) {
							Debug::print("{$f} recursion depth is 0");
						}
					}
					if ($print) {
						Debug::print("{$f} about to set a {$struct_class} with key \"{$key}\" for column \"{$column_name}\"");
					}
					$this->setForeignDataStructureListMember($column_name, $struct);
				}
			}elseif($print) {
				Debug::print("{$f} key list is empty");
			}
			$column->setLoadedFlag(true);
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * load all foreign data structures that are flagged as autoload
	 *
	 * @param mysqli $mysqli
	 * @param boolean $lazy
	 *        	: if true, lazy load all foreign data structures
	 * @param number $recursion_depth
	 *        	: if > 0, call recursively on all loaded structures with $recursion_depth - 1
	 * @return int
	 */
	public function loadForeignDataStructures(mysqli $mysqli, bool $lazy = false, int $recursion_depth = 0): int{
		$f = __METHOD__;
		try {
			$print = $this->getDebugFlag();
			$columns = $this->getFilteredColumns(COLUMN_FILTER_AUTOLOAD);
			if (empty($columns)) {
				if ($print) {
					Debug::print("{$f} no foreign data structures to load");
				}
				return SUCCESS;
			}elseif($print) {
				$count = count($columns);
				Debug::print("{$f} about to load {$count} foreign data structures from the following columns:");
				Debug::printArray(array_keys($columns));
			}
			foreach ($columns as $column_name => $column) {
				if (! $this->hasColumn($column_name)) {
					if ($print) {
						Debug::print("{$f} no such column \"{$column_name}\"");
					}
					continue;
				}
				if ($column instanceof ForeignKeyDatum) {
					if($print){
						Debug::print("{$f} datum \"{$column_name}\" is a ForeignKeyDatum");
					}
					if($this->hasForeignDataStructure($column_name)){
						if ($print) {
							Debug::print("{$f} already loaded foreign data structure \"{$column_name}\"");
						}
						continue;
					}
					if(
						$column->hasValue() || 
						$column->getPersistenceMode() === PERSISTENCE_MODE_VOLATILE && 
						$column->hasConverseRelationshipKeyName() && 
						$this->hasIdentifierValue()
					){
						$this->loadForeignDataStructure($mysqli, $column_name, $lazy, $recursion_depth);
					}else{
						if($print){
							Debug::print("{$f} column \"{$column_name}\" has no value");
						}
						continue;
					}
				}elseif($column instanceof KeyListDatum) {
					if ($print) {
						Debug::print("{$f} datum \"{$column_name}\" is a KeyListDatum");
					}
					if(
						(
							$column->hasValue() && 
							!$column->applyFilter(COLUMN_FILTER_ALIAS)
						) || $column->applyFilter(COLUMN_FILTER_INTERSECTION)
					){
						if ($print) {
							Debug::print("{$f} about to load ".get_short_class($this)."'s foreign data structure list for column \"{$column_name}\"");
							if ($column->applyFilter(COLUMN_FILTER_INTERSECTION)) {
								Debug::print("{$f} column \"{$column_name}\" is stored in an intersection table");
							}
						}
						$this->loadForeignDataStructureList($mysqli, $column_name, $lazy, $recursion_depth);
					}elseif($print) {
						Debug::print("{$f} no keys at column \"{$column_name}\"");
					}
				} else {
					$column_class = $column->getClass();
					Debug::error("{$f} datum at column \"{$column_name}\" is a {$column_class}");
				}
			}
			if ($print) {
				Debug::print("{$f} assigned data structures; returning normally");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasForeignDataStructureList(string $column_name): bool{
		return isset($this->foreignDataStructures) && is_array($this->foreignDataStructures) && array_key_exists($column_name, $this->foreignDataStructures) && is_array($this->foreignDataStructures[$column_name]) && ! empty($this->foreignDataStructures[$column_name]);
	}

	public function getForeignDataStructureCount(string $column_name): int{
		$f = __METHOD__;
		if (! isset($this->foreignDataStructures)) {
			return 0;
		}elseif(! is_array($this->foreignDataStructures)) {
			Debug::error("{$f} foreignDataStructures is set, but not an array");
		}elseif(! array_key_exists($column_name, $this->foreignDataStructures)) {
			return 0;
		}elseif(is_array($this->foreignDataStructures[$column_name])) {
			return count($this->foreignDataStructures[$column_name]);
		}elseif($this->foreignDataStructures[$column_name] instanceof DataStructure) {
			return 1;
		}
		Debug::error("{$f} none of the above");
	}

	/**
	 * sets the foreign data structure list, destroying the existing one if applicable
	 *
	 * @param string $column_name
	 * @param DataStructure[] $list
	 * @return DataStructure[]
	 */
	public function setForeignDataStructureList(string $column_name, array $list): array{
		$f = __METHOD__;
		$print = false;
		if (! is_array($list)) {
			Debug::error("{$f} list is not an array");
		}elseif(empty($list)) {
			Debug::error("{$f} don't call this function on an empty array");
		}elseif(! isset($this->foreignDataStructures) || ! is_array($this->foreignDataStructures)) {
			$this->foreignDataStructures = [
				$column_name => []
			];
		} else {
			$this->foreignDataStructures[$column_name] = [];
		}
		if ($print && ! $this->hasColumn($column_name)) {
			Debug::print("{$f} no datum at column \"{$column_name}\"");
		}
		foreach ($list as $struct) {
			$this->setForeignDataStructureListMember($column_name, $struct);
		}
		return $list;
	}

	/**
	 * assign a foreign data structure as a member of the list at column $column_name.
	 *
	 * @param string $column_name
	 * @param DataStructure $struct
	 * @return DataStructure
	 */
	public final function setForeignDataStructureListMember(string $column_name, ...$structs): int{
		$f = __METHOD__;
		try {
			$print = false;
			$pushed = 0;
			foreach ($structs as $struct) {
				if (is_array($struct)) {
					Debug::error("{$f} data structure is an array. This function only accepts objects as its second parameter");
				}elseif(! is_object($struct)) {
					$gottype = gettype($struct);
					Debug::error("{$f} structure's type is \"{$gottype}\"");
				}elseif($struct->getObjectStatus() === ERROR_DELETED) {
					Debug::print("{$f} structure is deleted");
					return -1;
				}
				$status = $this->beforeSetForeignDataStructureHook($column_name, $struct);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} beforeSetForeignDataStructureHook returned error status \"{$err}\"");
					$this->setObjectStatus($status);
					return - 1;
				}
				if (! is_array($this->foreignDataStructures)) {
					$this->foreignDataStructures = [];
				}
				if ($struct->hasColumn($struct->getIdentifierName()) && ! $struct->hasIdentifierValue()) {
					if ($print) {
						Debug::print("{$f} foreign data structure does not have a key; generating one now");
					}
					$status = $struct->generateKey();
					if ($status === ERROR_KEY_COLLISION) {
						if ($print) {
							Debug::print("{$f} key collision detected; assigning the already existing object instead");
						}
						$key = $struct->getIdentifierValue();
						$struct = registry()->getRegisteredObjectFromKey($key);
					}elseif($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} generateKey returned error status \"{$err}\"");
						$struct->setObjectStatus($status);
						return -1;
					}
				}
				$key = $struct->getIdentifierValue();
				if (! is_string($key) && ! is_int($key)) {
					$gottype = gettype($key);
					Debug::error("{$f} data structure returned a {$gottype}");
				}elseif(! array_key_exists($column_name, $this->foreignDataStructures)) {
					$this->foreignDataStructures[$column_name] = [];
				}elseif(! is_array($this->foreignDataStructures[$column_name])) {
					Debug::error("{$f} column {$column_name} does not map to an array");
				}
				//
				if (! array_key_exists($key, $this->foreignDataStructures[$column_name])) {
					if ($print) {
						Debug::print("{$f} {$column_name} with key \"{$key}\" is not already assigned");
					}
					if ($this->hasColumn($column_name)) {
						$column = $this->getColumn($column_name);
						if (! $column->inArray($key)) {
							if ($print) {
								Debug::print("{$f} no, array does not have a value \"{$key}\"");
							}
							$column->pushValue($key);
							if (! $this->hasColumnValue($column_name)) {
								Debug::error("{$f} immediatelty after pushing value, column \"{$column_name}\" has no value");
							}elseif(! $column->applyFilter(COLUMN_FILTER_VALUED)) {
								Debug::error("{$f} immediately after pushing value, failed filter " . COLUMN_FILTER_VALUED);
							}elseif($print) {
								Debug::print("{$f} pushed value successfully");
							}
						}elseif($print) {
							Debug::print("{$f} column \"{$column_name}\" already has a value \"{$key}\"");
						}
					}elseif($print) {
						if ($print) {
							Debug::print("{$f} this object does not have a column \"{$column_name}\"");
						}
					}
				}elseif($print) {
					Debug::print("{$f} key \"{$key}\" was already mapped");
				}
				if ($print) {
					Debug::print("{$f} appending data structure with key \"{$key}\" to column \"{$column_name}\"");
				}
				$this->foreignDataStructures[$column_name][$key] = $struct;
				if ($this->hasColumn($column_name)) {
					$column = $this->getColumn($column_name);
					if ($struct->getObjectStatus() === STATUS_PRELAZYLOAD) {
						if ($print) {
							Debug::print("{$f} lazy loading in progress");
						}
					} else {
						if ($column->hasConverseRelationshipKeyName()) {
							if ($print) {
								Debug::print("{$f} datum \"{$column_name}\" has an converse relationship column; about to assign this object to foreign data structure");
							}
							$this->reciprocateRelationship($column_name, $struct);
						}elseif($print) {
							Debug::print("{$f} datum \"{$column_name}\" does not have an converse relationship key name");
						}
					}
				}elseif($print) {
					Debug::print("{$f} this object does not have a column \"{$column_name}\"");
				}
				$status = $this->afterSetForeignDataStructureHook($column_name, $struct);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} afterSetForeignDataStructureHook returned error status \"{$err}\"");
					$this->setObjectStatus($status);
					return - 1;
				}
				$pushed ++;
			}
			return $pushed;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function isUninitialized(): bool{
		$f = __METHOD__;
		$print = false;
		if ($print) {
			if ($this->getObjectStatus() === STATUS_PRELAZYLOAD) {
				Debug::print("{$f} lazy load in progress");
			}elseif(parent::isUninitialized()) {
				Debug::print("{$f} parent function returned true");
			} else {
				$err = ErrorMessage::getResultMessage($this->getObjectStatus());
				Debug::print("{$f} nope, status is \"${err}\"");
			}
		}
		return $this->hasObjectStatus() && $this->getObjectStatus() === STATUS_PRELAZYLOAD || parent::isUninitialized();
	}

	public function getDeleteStatement(): DeleteStatement{
		return QueryBuilder::delete()->from($this->getDatabaseName(), $this->getTableName())->where(new WhereCondition($this->getIdentifierName(), OPERATOR_EQUALS))->limit(1);
	}

	public function setDeleteForeignDataStructuresFlag($value){
		return $this->setFlag(DIRECTIVE_DELETE_FOREIGN, $value);
	}

	public function getDeleteForeignDataStructuresFlag(){
		return $this->getFlag(DIRECTIVE_DELETE_FOREIGN);
	}

	protected function beforeDeleteForeignDataStructuresHook(mysqli $mysqli): int{
		$this->dispatchEvent(new BeforeDeleteForeignDataStructuresEvent());
		return SUCCESS;
	}

	/**
	 * deletes foreign data structures that are flagged for deletion
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function deleteForeignDataStructures(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$print = $this->getDebugFlag();
			$status = $this->permit(user(), DIRECTIVE_DELETE_FOREIGN);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} permission returner error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$status = $this->beforeDeleteForeignDataStructuresHook($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} beforeDeleteForeignDataSttructuresHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$columns = $this->getFilteredColumns(COLUMN_FILTER_FOREIGN);
			$delete_us = [];
			foreach ($columns as $column_name => $column) {
				$column = $this->getColumn($column_name);
				if ($column instanceof KeyListDatum) {
					if ($this->hasForeignDataStructureList($column_name)) {
						foreach ($this->getForeignDataStructureList($column_name) as $key => $fds) {
							if ($fds->getDeleteFlag()) {
								if ($print) {
									$fdsc = $fds->getClass();
									Debug::print("{$f} {$fdsc} with key \"{$key}\" is flagged for deletion");
								}
								array_push($delete_us, $fds);
							}
						}
					}elseif($print) {
						Debug::print("{$f} no foreign data structure list \"{$column_name}\"");
					}
				}elseif($column instanceof ForeignKeyDatum) {
					if (! $this->hasForeignDataStructure($column_name)) {
						continue;
					}
					$struct = $this->getForeignDataStructure($column_name);
					if (! $struct->getDeleteFlag()) {
						if ($print) {
							Debug::print("{$f} foreign data structure at column \"{$column_name}\" is NOT flagged for deletion");
						}
						continue;
					}elseif($print) {
						Debug::print("{$f} foreign data structure at column \"{$column_name}\" IS flagged for deletion");
						if ($column_name === "userKey") {
							$fdsk = $this->getColumnValue($column_name);
							Debug::error("{$f} attempting to delete user -- FDS key is \"{$fdsk}\"");
						}
					}
					$this->setColumnValue($column_name, null);
					array_push($delete_us, $struct);
				} else {
					Debug::error("{$f} neither of the above for column \"{$column_name}\"");
				}
			}
			if (! empty($delete_us)) {
				foreach ($delete_us as $struct) {
					$status = $struct->delete($mysqli);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} deleting object returned error status \"{$err}\"");
					}
				}
			}elseif($print) {
				Debug::print("{$f} nothing to delete");
			}
			$status = $this->afterDeleteForeignDataStructuresHook($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} afterDeleteForeignDataSttructuresHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			if ($print) {
				Debug::print("{$f} returning normally");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function afterDeleteForeignDataStructuresHook(mysqli $mysqli): int{
		$this->dispatchEvent(new AfterDeleteForeignDataStructuresEvent());
		return SUCCESS;
	}

	/**
	 * override this to create additional functionality that gets called before deletion
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	protected function beforeDeleteHook(mysqli $mysqli): int{
		$f = __METHOD__;
		$print = false;
		try {
			// before delete event
			$status = $this->dispatchEvent(new BeforeDeleteEvent());
			// flag foreign data structures for recursive deletion, or contract vertices
			foreach ($this->getFilteredColumns(COLUMN_FILTER_FOREIGN) as $column_name => $column) {
				if ($column->getRecursiveDeleteFlag()) {
					if ($print) {
						Debug::print("{$f} column \"{$column_name}\" is flagged for recursive deletion");
					}
					if ($column instanceof ForeignKeyDatum) {
						$this->getForeignDataStructure($column_name)->setDeleteFlag(true);
					}elseif($column instanceof KeyListDatum) {
						if (! $this->hasForeignDataStructureList($column_name)) {
							continue;
						}
						foreach ($this->getForeignDataStructureList($column_name) as $member) {
							$member->setDeleteFlag(true);
						}
					}
					$this->setDeleteForeignDataStructuresFlag(true);
				}elseif($column->getContractVertexFlag()) {
					if ($print) {
						Debug::print("{$f} column \"{$column_name}\" has its contractVertex flag set");
					}
					$status = $column->contractVertex($mysqli);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} contractVertex returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}
				}elseif($print) {
					Debug::print("{$f} column \"{$column_name}\" does not have either of its recursiveDelete or contractVertex flag set");
				}
			}
			// delete cascade triggers
			if ($this->getFlag("cascadeDelete")) {
				$this->cascadeDelete($mysqli);
			}elseif($print) {
				Debug::print("{$f} cascade delete flag is not set");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getDefaultPersistenceMode(): int{ // XXX not implemented: non-static default persistence mode
		return static::getDefaultPersistenceModeStatic();
	}

	public static function getPermissionStatic(string $name, $data){
		return new AdminOnlyAccountTypePermission($name);
	}

	/**
	 * delete this object from the database
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function delete(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$print = false;
			// for superglobals
			$storage = $this->getDefaultPersistenceMode();
			if ($storage !== PERSISTENCE_MODE_DATABASE) {
				Debug::error("{$f} to delete superglobal data use unsetColumnValues");
			}
			// check permissions
			$status = $this->permit(user(), DIRECTIVE_DELETE);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} delete permission returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$this->log(DIRECTIVE_DELETE);
			// start a database transaction if one hasn't already
			if (! db()->hasPendingTransactionId()) {
				$transactionId = sha1(random_bytes(32));
				db()->beginTransaction($mysqli, $transactionId);
			}
			// pre-deletion hook. Includes a special check for rejecting the deletion because a shared foreign data structure is in use by multiple objects.
			$status = $this->beforeDeleteHook($mysqli);
			switch ($status) {
				case RESULT_DELETE_FAILED_IN_USE:
					if ($print) {
						Debug::print("{$f} object is still in use -- forgoing deletion");
					}
					return SUCCESS;
				case SUCCESS:
					break;
				default:
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} before delete hook returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
			}
			// delete foreign data structures that were explicitly flagged, if applicable
			if ($this->getDeleteForeignDataStructuresFlag()) {
				$status = $this->deleteForeignDataStructures($mysqli);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} deleteForeignDataStructures returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}
				Debug::print("{$f} successfully deleted subordinate data structures");
			}
			// prepare bind execute delete query
			$select = $this->getDeleteStatement();
			if (empty($select)) {
				Debug::error("{$f} deletion query is undefined");
			}
			$typedef = $this->getColumn($this->getIdentifierName())
				->getTypeSpecifier();
			$id = $this->getIdentifierValue();
			if ($id == null) {
				Debug::error("{$f} unique identifier is null");
			}
			$st = $select->prepareBindExecuteGetStatement($mysqli, $typedef, $id);
			if ($st == null) {
				$status = $this->getObjectStatus();
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} prepared query statement returned error status \"{$err}\"");
				return $status;
			}elseif($print) {
				Debug::print("{$f} deletion successful");
			}
			// post-deletion hook
			$status = $this->afterDeleteHook($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} after delete hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif(isset($transactionId)) {
				db()->commitTransaction($mysqli, $transactionId);
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * override this to define additional functionality that occurs after deletion
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	protected function afterDeleteHook(mysqli $mysqli): int{
		$this->afterEditHook($mysqli, directive());
		$this->dispatchEvent(new AfterDeleteEvent());
		return SUCCESS;
	}

	public function getForeignDataStructureKey($column_name){
		return $this->getForeignDataStructure($column_name)->getIdentifierValue();
	}

	public function hasOldDataStructures(){
		return isset($this->oldDataStructures) && is_array($this->oldDataStructures) && ! empty($this->oldDataStructures);
	}

	public function hasOldDataStructureList(string $column_name): bool{
		return $this->hasOldDataStructures() && array_key_exists($column_name, $this->oldDataStructures) && is_array($this->oldDataStructures[$column_name]) && ! empty($this->oldDataStructures[$column_name]);
	}

	public function getForeignDataStructureList(string $column_name): array{
		$f = __METHOD__;
		if (! $this->hasForeignDataStructureList($column_name)) {
			Debug::error("{$f} no foreign data structure list at column \"{$column_name}\"");
			return [];
		}
		return $this->foreignDataStructures[$column_name];
	}

	public function getForeignDataStructureListMember(string $column_name, $key): DataStructure{
		$f = __METHOD__;
		if (! $this->hasForeignDataStructureListMember($column_name, $key)) {
			Debug::error("{$f} undefined foreign data structure list member at column \"{$column_name}\", key \"{$key}\"");
		}
		$list = $this->getForeignDataStructureList($column_name);
		$s = $list[$key];
		return $s;
	}

	public function getForeignDataStructureListMemberCommand(string $column_name, $key): GetForeignDataStructureListMemberCommand{
		return new GetForeignDataStructureListMemberCommand($this, $column_name, $key);
	}

	protected function beforeInsertForeignDataStructuresHook(mysqli $mysqli, string $when): int{
		$this->dispatchEvent(new BeforeInsertForeignDataStructuresEvent($when));
		return SUCCESS;
	}

	/**
	 * inserts foreign data structures that are flagged for insertion
	 *
	 * @param mysqli $mysqli
	 * @param string $when
	 *        	: "before" or "after" -- specify whether the data structures being inserted are those that must be inserted before this object is inserted (b/c this object has a constrained foreign key that references them) or afterward (b/c the foreign data structures have constrained foreign keys that reference this object, or if it doesn't matter)
	 * @return int
	 */
	private function insertForeignDataStructures(mysqli $mysqli, string $when): int{
		$f = __METHOD__;
		try {
			$print = $this->getDebugFlag();
			switch ($when) {
				case CONST_BEFORE:
					if ($print) {
						Debug::print("{$f} inserting foreign data structures that come before this object");
					}
					$columns = $this->getFilteredColumns(CONST_BEFORE);
					break;
				case CONST_AFTER:
					if ($print) {
						Debug::print("{$f} inserting foreign data structures that come after this object");
					}
					$columns = $this->getFilteredColumns(CONST_AFTER);
					break;
				default:
					Debug::error("{$f} invalid relative insertion sequence \"{$when}\"");
			}
			$status = $this->beforeInsertForeignDataStructuresHook($mysqli, $when);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} beforeInsertForeignDataStructuresHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print) {
				Debug::print("{$f} beforeInsertForeignDataStructureHook returned success");
			}
			if (empty($columns)) {
				if ($print) {
					Debug::print("{$f} foreign data structure indices array is empty");
				}
				return SUCCESS;
			}
			$structs = [];
			$dupes = [];
			$old_structs = [];
			foreach ($columns as $column_name => $column) {
				if ($print) {
					Debug::print("{$f} about to evaluate column \"{$column_name}\"");
				}
				if ($column instanceof KeyListDatum) {
					$multiple = true;
				} else {
					$multiple = false;
				}
				if ($multiple && $this->hasForeignDataStructureList($column_name)) {
					$list = $this->getForeignDataStructureList($column_name);
					if (empty($list)) {
						Debug::error("{$f} list \"{$column_name}\" returned empty");
					}
					foreach ($list as $struct_key => $struct) {
						if ($struct->getInsertFlag()) {
							if ($print) {
								Debug::print("{$f} insert flag is set on foreign data structure list member at column \"{$column_name}\"");
							}
							if (! $struct->getDeleteFlag()) {
								if (! $struct->hasIdentifierValue() || ! array_key_exists($struct->getIdentifierValue(), $dupes)) {
									array_push($structs, $struct);
									if ($struct->hasIdentifierValue()) {
										$dupes[$struct->getIdentifierValue()] = $struct;
									}
								}elseif($print) {
									Debug::print("{$f} struct has an identifier, and it has already been added to the insertion queue");
								}
							}elseif($print) {
								Debug::print("{$f} {$column_name} member with key \"{$struct_key}\" is apoptotic before ever getting inserted");
							}
						}elseif($print) {
							Debug::print("{$f} insert flag is not set for foreign data structure list \"{$column_name}\" member \"{$struct_key}\"");
						}
					}
					if ($this->hasOldDataStructureList($column_name)) {
						foreach ($this->getOldDataStructureList($column_name) as $old) {
							if ($old->getDeleteFlag()) {
								$old_structs[$old->getIdentifierValue()] = $old;
							}elseif($print) {
								Debug::print("{$f} deletion flag is not set");
							}
						}
					}
				}elseif($this->hasForeignDataStructure($column_name)) {
					if ($print) {
						Debug::print("{$f} yes, this object has a foreign data structure at column \"{$column_name}\"");
					}
					$struct = $this->getForeignDataStructure($column_name);
					if (! $struct->getInsertFlag() || $struct->getDeleteFlag()) {
						if ($print) {
							Debug::print("{$f} structure \"{$column_name}\" does not have its insert flag set");
						}
						continue;
					}
					if (! $struct->hasIdentifierValue() || ! array_key_exists($struct->getIdentifierValue(), $dupes)) {
						array_push($structs, $struct);
						if ($struct->hasIdentifierValue()) {
							$dupes[$struct->getIdentifierValue()] = $struct;
						}
					}elseif($print) {
						Debug::print("{$f} struct has an identifier, and it has already been added to the insertion queue");
					}
					// array_push($structs, $struct);
					if ($print) {
						Debug::print("{$f} structure at column \"{$column_name}\" is ready to insert");
					}
					if ($this->hasOldDataStructure($column_name)) {
						$old_struct = $this->getOldDataStructure($column_name);
						if ($old_struct->getDeleteFlag()) {
							if ($print) {
								Debug::print("{$f} object has an old structure at column \"{$column_name}\" -- about to delete it");
							}
							array_push($old_structs, $old_struct);
						}elseif($print) {
							Debug::print("{$f} old structure at column \"{$column_name}\" does not have its delete flag set");
						}
					}elseif($print) {
						Debug::print("{$f} this object does not have an old foreign data structure for column \"{$column_name}\"");
					}
				} else {
					if ($print) {
						Debug::print("{$f} data structure with column {$column_name} is undefined");
					}
					continue;
				}
			}
			if (empty($structs)) {
				Debug::error("{$f} there are no foreign data structures to insert");
			}
			foreach ($structs as $struct_num => $struct) {
				$pretty = $struct->getClass();
				if ($print) {
					$key = $struct->hasIdentifierValue() ? $struct->getIdentifierValue() : "[undefined]";
					Debug::print("{$f} about to insert {$pretty} with ID \"{$key}\" at position {$struct_num}");
				}
				if (! $this->getBlockInsertionFlag()) {
					$status = $struct->insert($mysqli);
				} else {
					if ($print) {
						Debug::print("{$f} block insertion flag is set");
					}
					$status = SUCCESS;
				}
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} writing {$pretty} to database returned error message \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print) {
					Debug::print("{$f} successfully wrote new foreign data structure at column \"{$column_name}\"");
				}
			}
			if (! empty($old_structs)) {
				if ($print) {
					Debug::print("{$f} there are old structures to delete");
				}
				foreach ($old_structs as $old_struct) {
					if (! $this->getBlockInsertionFlag()) {
						$status = $old_struct->delete($mysqli);
					} else {
						if ($print) {
							Debug::print("{$f} block insertion flag is set");
						}
						$status = SUCCESS;
					}
					switch ($status) {
						// case STATUS_DELETED:
						case SUCCESS:
							if ($print) {
								Debug::print("{$f} successfully deleted old structure at column \"{$column_name}\"");
							}
							continue 2;
						case RESULT_DELETE_FAILED_IN_USE:
							if ($print) {
								Debug::print("{$f} did not delete old structure at column \"{$column_name}\", it's still in use");
							}
							continue 2;
						default:
							$err = ErrorMessage::getResultMessage($status);
							$osk = $old_struct->getIdentifierValue();
							$osc = $old_struct->getClass();
							Debug::error("{$f} deleting old structure of class \"{$osc}\" with identifier \"{$osk}\" returned error status \"{$err}\"");
							return $this->setObjectStatus($status);
					}
				}
			}elseif($print) {
				Debug::print("{$f} no old data structures to delete");
			}

			$status = $this->afterInsertForeignDataStructuresHook($mysqli, $when);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} afterInsertForeignDataStructuresHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print) {
				Debug::print("{$f} afterInsertForeignDataStructureHook returned success");
			}
			if ($print) {
				Debug::print("{$f} returning normally");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function afterInsertForeignDataStructuresHook(mysqli $mysqli, string $when): int{
		$this->dispatchEvent(new AfterInsertForeignDataStructuresEvent($when));
		return SUCCESS;
	}

	public function setPreInsertForeignDataStructuresFlag(bool $value = true): bool{
		$f = __METHOD__;
		$print = false;
		if ($print) {
			$did = $this->getDebugId();
			Debug::printStackTraceNoExit("{$f} entered. Debug Id id \"{$did}\"");
		}
		return $this->setFlag(DIRECTIVE_PREINSERT_FOREIGN, $value);
	}

	public function getPreInsertForeignDataStructuresFlag(): bool{
		return $this->getFlag(DIRECTIVE_PREINSERT_FOREIGN);
	}

	/**
	 * this is called by beforeSave and beforeDelete
	 *
	 * @param mysqli $mysqli
	 * @param string $directive
	 * @return int
	 */
	protected function beforeEditHook(mysqli $mysqli, string $directive): int{
		$this->dispatchEvent(new BeforeEditEvent(), [
			'directive' => $directive
		]);
		return SUCCESS;
	}

	public function validate(): int{
		if ($this->hasValidationClosure()) {
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
		try {
			$print = false;
			$status = $this->beforeEditHook($mysqli, $directive);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} beforeEditHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print) {
				Debug::print("{$f} beforeEditHook successful");
			}
			$status = $this->validate();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} validate returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print) {
				Debug::print("{$f} validation successful");
			}
			// deal with mutually referential one to one relationships for objects being inserted simultaneously
			if($print){
				Debug::print("{$f} about to deal with mutually referential 1:1 foreign keys for class ".$this->getShortClass());
			}
			$columns = $this->getFilteredColumns(ForeignKeyDatum::class, COLUMN_FILTER_VALUED, COLUMN_FILTER_UPDATE, "!" . COLUMN_FILTER_INTERSECTION);
			if (! empty($columns)) {
				foreach ($columns as $cn => $column) {
					if ($column->getRelationshipType() !== RELATIONSHIP_TYPE_ONE_TO_ONE) { // XXX maybe make this a filter
						if ($print) {
							Debug::print("{$f} column \"{$cn}\" is not one to one");
						}
						continue;
					}elseif($print) {
						Debug::print("{$f} column \"{$cn}\" is one-to-one");
					}
					$status = $column->fulfillMutualReference();
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} fulfillMutualReference for column \"{$cn}\" returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}elseif($print) {
						Debug::print("{$f} fulfillMutualReference succeeded for column \"{$cn}\"");
					}
				}
			}elseif($print) {
				Debug::print("{$f} no foreign key datums with defined values");
				$this->debugMutualOneToOneForeignKeys();
			}
			// moved this from right above validate()
			$this->dispatchEvent(new BeforeSaveEvent(), [
				'directive' => $directive
			]);
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function debugMutualOneToOneForeignKeys(){
		$f = __METHOD__;
		$print = false;
		if (! $this->getDebugFlag()) {
			return SUCCESS;
		}
		$columns = $this->getFilteredColumns(ForeignKeyDatum::class, COLUMN_FILTER_VALUED, COLUMN_FILTER_UPDATE, "!" . COLUMN_FILTER_INTERSECTION);
		if (empty($columns)) {
			Debug::error("{$f} there are no mututal 1:1 foreign keys to debug");
		}elseif($print) {
			Debug::print("{$f} success");
		}
		return SUCCESS;
	}

	/**
	 * generates keys for all foreign data structures that persist in the database in some way, and that don't already have keys
	 *
	 * @return int
	 */
	protected function generateUndefinedForeignKeys(){
		$f = __METHOD__;
		try {
			$print = false && $this->getDataType() === DATATYPE_LINE_ITEM;
			$columns = $this->getFilteredColumns(COLUMN_FILTER_FOREIGN);
			if (empty($columns)) {
				if ($print) {
					Debug::print("{$f} no foreign key/key list datums that get stored in the database");
				}
				return SUCCESS;
			}
			foreach ($columns as $name => $column) {
				$p = $column->getPersistenceMode();
				switch ($p) {
					case PERSISTENCE_MODE_DATABASE:
					case PERSISTENCE_MODE_EMBEDDED:
					case PERSISTENCE_MODE_ENCRYPTED:
					case PERSISTENCE_MODE_INTERSECTION:
						if ($print) {
							Debug::print("{$f} persistence mode \"{$p}\" gets stored in the database");
						}
						break;
					case PERSISTENCE_MODE_ALIAS:
					case PERSISTENCE_MODE_COOKIE:
					case PERSISTENCE_MODE_SESSION:
					case PERSISTENCE_MODE_VOLATILE:
						if ($print) {
							Debug::print("{$f} persistence mode \"{$p}\" does not get stored in the database");
						}
						continue 2;
					default:
						Debug::error("{$f} undefined persistence mode \"{$p}\"");
				}
				if ($column instanceof ForeignKeyDatum) {
					if (! $this->hasForeignDataStructure($name)) {
						if ($print) {
							Debug::print("{$f} no foreign data structure \"{$name}\"");
						}
						continue;
					}elseif($column->hasValue()){
						if ($print) {
							$value = $column->getValue();
							Debug::print("{$f} column \"{$name}\" already has a value, and it's \"{$value}\"");
						}
						continue;
					}elseif($print) {
						Debug::print("{$f} about to generate key for foreign data structure \"{$name}\"");
					}
					$struct = $this->getForeignDataStructure($name);
					$status = $struct->generateKey();
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} generating key for foreign data structure \"{$name}\" returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}
					$this->setForeignDataStructure($name, $struct);
				}elseif($column instanceof KeyListDatum) {
					if (! $this->hasForeignDataStructureList($name)) {
						continue;
					}
					$list = $this->getForeignDataStructureList($name);
					if (empty($list)) {
						if ($print) {
							Debug::print("{$f} foreign data structure list \"{$name}\" is empty");
						}
						continue;
					}
					foreach ($list as $struct) {
						if ($struct->hasIdentifierValue()) {
							if ($print) {
								Debug::print("{$f} one of the foreign data structures for column \"{$name}\" already has an identifier");
							}
							continue;
						}elseif($print) {
							Debug::print("{$f} about to generate key for foreign data structure list member of column \"{$name}\"");
						}
						$status = $struct->generateKey();
						if ($status !== SUCCESS) {
							$err = ErrorMessage::getResultMessage($status);
							Debug::warning("{$f} generating key for foreign data structure list member of column \"{$name}\" returned error status \"{$err}\"");
							return $this->setObjectStatus($status);
						}
						$this->setForeignDataStructureListMember($name, $struct);
					}
				} else {
					Debug::error("{$f} column \"{$name}\" is somehow not a foreign key or key list");
				}
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * override this to define additional functionality that occurs after insertion
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	protected function beforeInsertHook(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$print = false;
			$status = $this->generateUndefinedForeignKeys();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} generate undefined foreign keys returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print) {
				Debug::print("{$f} generateUndefinedKeys executed successfully");
			}
			$status = $this->loadForeignDataStructures($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} loadForeignDataStructures returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print) {
				Debug::print("{$f} successfully loaded foreign data structures");
			}
			if (! $this->getFlag("expandForeign")) {
				$status = Loadout::expandForeignDataStructures($this, $mysqli);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} expandForeignDataStructures returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print) {
					Debug::print("{$f} successfully expanded foreign data structures");
				}
			}elseif($print) {
				Debug::print("{$f} already expanded foreign data structures");
			}
			// beforeSaveHook gets called inside beforeInsert and beforeUpdate
			$status = $this->beforeSaveHook($mysqli, DIRECTIVE_INSERT);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} beforeSaveHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print) {
				Debug::print("{$f} beforeSaveHook executed successfully");
			}
			$this->dispatchEvent(new BeforeInsertEvent()); // moved from top
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function beforeExpandHook(mysqli $mysqli): int{
		$this->dispatchEvent(new BeforeExpandEvent());
		return SUCCESS;
	}

	public function afterExpandHook(mysqli $mysqli): int{
		$this->dispatchEvent(new AfterExpandEvent());
		return SUCCESS;
	}

	/**
	 * returns all embedded data structures, i.e.
	 * those that are stored in separate tables
	 *
	 * @return NULL|EmbeddedData[]
	 */
	public function getEmbeddedDataStructures(): ?array{
		$f = __METHOD__;
		try {
			$print = false;
			$columns = $this->getFilteredColumns(COLUMN_FILTER_EMBEDDED);
			if (empty($columns)) {
				if ($print) {
					Debug::print("{$f} there are no embedded columns");
				}
				return null;
			}
			$groups = [];
			foreach ($columns as $column_name => $column) {
				if ($print) {
					Debug::print("{$f} column \"{$column_name}\" is embedded");
				}
				$groupname = $column->getEmbeddedName();
				if (array_key_exists($groupname, $groups)) {
					if ($print) {
						Debug::print("{$f} EmbeddedData for group \"{$groupname}\" already exists");
					}
					$replica = $column->replicate();
					$replica->setPersistenceMode(PERSISTENCE_MODE_DATABASE);
					$replica->setDataStructure($groups[$groupname]);
					$groups[$groupname]->pushColumn($replica);
					if ($column->getUpdateFlag()) {
						if ($print) {
							Debug::print("{$f} yes, embedded column \"{$column_name}\" is flagged for update");
						}
						$groups[$groupname]->setUpdateFlag(true);
					}elseif($print) {
						Debug::print("{$f} no, embedded column \"{$column_name}\" is NOT flagged for update");
					}
					continue;
				}
				$embedme = new EmbeddedData();
				$embedme->setName($groupname);
				$embedme->setSubsumingObject($this);
				if ($this->getDebugFlag()) {
					$idn = $embedme->getIdentifierName();
					if (! $embedme->hasColumnValue($idn)) {
						if (! $this->hasIdentifierValue()) {
							$idn = $this->getIdentifierName();
							Debug::error("{$f} this object's identifier \"{$idn}\" is undefined");
						}
						Debug::error("{$f} embedded data identifier \"{$idn}\" is undefined");
					}
				}
				$replica = $column->replicate();
				$replica->setPersistenceMode(PERSISTENCE_MODE_DATABASE);
				$replica->setDataStructure($embedme);
				$embedme->pushColumn($replica);
				if ($column->getUpdateFlag()) {
					$embedme->setUpdateFlag(true);
					if ($print) {
						Debug::print("{$f} yes, embedded column \"{$column_name}\" is flagged for update");
					}
				}elseif($print) {
					Debug::print("{$f} no, embedded column \"{$column_name}\" is NOT flagged for update");
				}
				$groups[$groupname] = $embedme;
				if ($print) {
					Debug::print("{$f} created EmbeddedData for group \"{$groupname}\" and pushed column \"{$column_name}\"");
				}
			}
			$print = false;
			if ($print) {
				foreach ($groups as $name => $e) {
					Debug::print("{$f} about to update the following columns for embed group \"{$name}\":");
					foreach ($groups as $column_name => $column) {
						Debug::print("{$f} {$column_name}");
					}
					Debug::print("{$f} done printing updatable columns for embed group \"{$name}\"");
					if ($e->getColumnCount() < 2) {
						Debug::error("{$f} embedded data structure \"{$name}\" has < 2 columns");
					} else {
						Debug::print("{$f} embedded group \"{$name}\" has a sufficient number of columns; it's probably your create table statement that's fucking up");
					}
				}
			}
			return $groups;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * insert data stored in intersection tables
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	private final function insertIntersectionData(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$print = false;
			if($print){
				Debug::print("{$f} entered for a(n) ".$this->getShortClass()." with key ".$this->getIdentifierValue());
			}
			$polys = $this->getFilteredColumns(COLUMN_FILTER_INTERSECTION, COLUMN_FILTER_VALUED, "!".COLUMN_FILTER_ONE_SIDED);
			if (! empty($polys)) {
				foreach ($polys as $name => $poly) {
					if ($print) {
						if ($poly instanceof ForeignKeyDatum) {
							$key = $poly->getValue();
							Debug::print("{$f} about to insert intersection data for column \"{$name}\" which has key \"{$key}\"");
							if (strlen($key) == 0) {
								$decl = $poly->getDeclarationLine();
								Debug::error("{$f} zero length foreign key. Instantiated {$decl}");
							}
						}elseif($poly instanceof KeyListDatum) {
							Debug::print("{$f} about to insert intersection data for column \"{$name}\"");
						} else {
							$class = $poly->getClass();
							Debug::error("{$f} unsupported class \"{$class}\"");
						}
					}
					if ($poly instanceof KeyListDatum) {
						$vc = $poly->getValueCount();
						$fdsc = $this->getForeignDataStructureCount($name);
						if ($vc !== $fdsc) {
							Debug::error("{$f} key list column value count {$vc} does not equal foreign data structure count {$fdsc} for relation \"{$name}\"");
						}
					}
					if (! $this->getBlockInsertionFlag()) {
						$status = $poly->insertIntersectionData($mysqli);
					} else {
						if ($print) {
							Debug::print("{$f} block insertion flag is set");
						}
						$status = SUCCESS;
					}
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} insertIntersectionData for column \"{$name}\" returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}elseif($print) {
						Debug::print("{$f} successfully inserted intersection data for column \"{$name}\"");
					}
				}
			}elseif($print) {
				Debug::print("{$f} no polymorphic keys with actual values");
			}
			if($print){
				Debug::print("{$f} returning for a(n) ".$this->getShortClass()." with key ".$this->getIdentifierValue());
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function beforeDeriveForeignDataStructuresHook(): int{
		$this->dispatchEvent(new BeforeDeriveForeignDataStructuresEvent());
		return SUCCESS;
	}

	/**
	 * XXX TODO under construction
	 * this is for generating foreign data structures that can derive themselves form a template
	 * e.g.
	 * a record of a taxable event that is generated from the tax object
	 *
	 * @return int
	 */
	public function deriveForeignDataStructures(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$print = $this->getDebugFlag();
			if ($this->getFlag("derived")) {
				if ($print) {
					Debug::print("{$f} already called this function");
				}
				return SUCCESS;
			}
			$status = $this->beforeDeriveForeignDataStructuresHook();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("{$f} beforeDeriveForeignDataStructuresHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$derived = $this->getFilteredColumns(COLUMN_FILTER_TEMPLATE);
			if (empty($derived)) {
				if($print){
					Debug::print("{$f} there are no derivable columns to process");
				}
				$this->setFlag("derived", true);
				return $this->afterDeriveForeignDataStructuresHook();
			}elseif($print){
				Debug::print("{$f} about to process ".count($derived)." foreign columns");
			}
			foreach ($derived as $name => $column) {
				if (! $column->hasValue() && ! $column->hasOriginalValue()) {
					if ($print) {
						Debug::print("{$f} column \"{$name}\" lacks an original or current value, continuing");
					}
					continue;
				}elseif($print) {
					Debug::print("{$f} about to generate/delete derived foreign data structures for column \"{$name}\"");
				}
				$inv = $column->getAppliedTemplateColumnName();
				$new = $column->hasValue() ? $column->getValue() : [];
				$old = $column->hasOriginalValue() ? $column->getOriginalValue() : [];
				// generate derived foreign data structures, and flag them for insertion
				$insert_us = array_diff($new, $old);
				if (! empty($insert_us)) {
					if ($print) {
						$count = count($insert_us);
						Debug::print("{$f} {$count} foreign data structures to generate");
					}
					foreach ($insert_us as $key) {
						if ($print) {
							Debug::print("{$f} about to apply template for foreign data structure \"{$name}\" with key \"{$key}\"");
						}
						$fds = registry()->get($key)->applyTemplate($mysqli, $this);
						if ($fds === null) {
							if ($print) {
								Debug::print("{$f} applyTemplate returned null, continuing");
							}
							return null;
						}
						$fds->setInsertFlag(true);
						$this->setForeignDataStructureListMember($inv, $fds);
					}
					$this->setPostInsertForeignDataStructuresFlag(true);
					$this->setPostUpdateForeignDataStructuresFlag(true);
				}elseif($print) {
					Debug::print("{$f} there are no new foreign data structure to generate");
				}
				// flag old foreign data structures for deletion that are no longer needed
				$delete_us = array_diff($old, $new);
				if (! empty($delete_us)) {
					if ($print) {
						$count = count($delete_us);
						Debug::print("{$f} {$count} template keys to delete");
					}
					foreach ($this->getForeignDataStructureList($inv) as $key => $fds) {
						if (in_array($fds->getColumnValue("templateKey"), $delete_us, true)) {
							if ($print) {
								Debug::print("{$f} foreign data structure in list \"{$name}\" with key \"{$key}\" has an unused template key and will be flagged for deletion");
							}
							$fds->setDeleteFlag(true);
							$this->setPostUpdateForeignDataStructuresFlag(true);
							$this->setDeleteForeignDataStructuresFlag(true);
						}elseif($print) {
							Debug::print("{$f} foreign data structure in list \"{$name}\" with key \"{$key}\" will NOT be flagged for deletion");
						}
					}
				}elseif($print) {
					Debug::print("{$f} there are no template keys to delete");
				}
			}
			$this->setFlag("derived", true);
			$status = $this->afterDeriveForeignDataStructuresHook();
			if ($print) {
				Debug::print("{$f} returning normally");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function afterDeriveForeignDataStructuresHook(): int{
		$this->dispatchEvent(new AfterDeriveForeignDataStructuresEvent());
		return SUCCESS;
	}

	/**
	 * insert this object into the database
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public /*final*/ function insert(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$print = false;
			if ($this->getFlag("inserting")) {
				Debug::error("{$f} this object is already being inserted");
			}elseif(! isset($mysqli)) {
				Debug::warning("{$f} mysqli object is undefined");
				return $this->setObjectStatus(ERROR_MYSQL_CONNECT);
			}elseif($print) {
				$class = $this->getClass();
				$did = $this->getDebugId();
				Debug::print("{$f} inserting {$class} with debug ID \"{$did}\"");
			}
			$this->setFlag("inserting", true);
			// validate insert permission
			$status = $this->permit(user(), DIRECTIVE_INSERT);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} permission returned error status \"{$err}\"");
				Debug::printStackTraceNoExit();
				return $this->setObjectStatus($status);
			}
			// start database transaction
			if (! db()->hasPendingTransactionId()) {
				$transactionId = sha1(random_bytes(32));
				db()->beginTransaction($mysqli, $transactionId);
			}
			// pre-insertion hook
			$status = $this->beforeInsertHook($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} before insert hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			// generate initial columns values
			$status = $this->generateInitialValues();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} generateInitialValues returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			// ensure this item has not already been inserted
			if (! $this->getOnDuplicateKeyUpdateFlag()) {
				$status = $this->preventDuplicateEntry($mysqli);
				switch ($status) {
					case SUCCESS:
						if ($print) {
							Debug::print("{$f} This is not a duplicate entry");
						}
						break;
					case ERROR_DUPLICATE_ENTRY:
						Debug::warning("{$f} duplicate entry detected");
						$recourse = $this->getDuplicateEntryRecourse();
						switch ($recourse) {
							case RECOURSE_ABORT:
								return $this->setObjectStatus($status);
							case RECOURSE_CONTINUE:
								return SUCCESS;
							case RECOURSE_IGNORE:
								break 2;
							case RECOURSE_EXIT:
							case RECOURSE_RETRY:
							default:
								Debug::error("{$f} duplicate entry");
						}
					default:
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} checking for duplicate entries returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
				}
			}elseif($print) {
				Debug::print("{$f} on duplicate entry update");
			}
			$this->log(DIRECTIVE_INSERT);
			// insert foreign data structures that must exist prior to this object
			if ($this->getPreInsertForeignDataStructuresFlag()) {
				if ($print) {
					Debug::print("{$f} insert before foreign data structures flag is set");
				}
				$status = $this->insertForeignDataStructures($mysqli, CONST_BEFORE);
				$this->setPreInsertForeignDataStructuresFlag(false);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} insertForeignDataStructures returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}
			}elseif($print) {
				Debug::print("{$f} preinsert foreign data structures flag is not set");
			}
			// generate param signature and execute prepared insertion query
			$insert = $this->getInsertStatement();
			$idn = $this->getIdentifierName();
			$typedef = "";
			$duplicate = "";
			$params = [];
			$params2 = [];
			$columns = $this->getFilteredColumns(DIRECTIVE_INSERT);
			foreach ($columns as $column_name => $column) {
				if($print){
					Debug::print("{$f} inserting column \"{$column_name}\"");
				}
				$typedef .= $column->getTypeSpecifier();
				array_push($params, $column->getDatabaseEncodedValue());
				if ($this->getOnDuplicateKeyUpdateFlag() && $column_name !== $idn) {
					$duplicate .= $column->getTypeSpecifier();
					array_push($params2, $column->getDatabaseEncodedValue());
				}
			}
			if ($this->getOnDuplicateKeyUpdateFlag()) {
				$typedef .= $duplicate;
				$params = array_merge($params, $params2);
			}
			$length = strlen($typedef);
			$count = count($params);
			if ($length === 0) {
				Debug::error("{$f} type specifier is empty string");
			}elseif($count === 0) {
				Debug::error("{$f} insert parameter count is 0");
			}elseif($length !== $count) {
				Debug::warning("{$f} type definition string \"{$typedef}\" does not match parameter count {$count} for query statement \"{$insert}\" with the following parameters:");
				Debug::printArray($params);
				Debug::printStackTrace();
			}elseif($print) {
				Debug::print("{$f} about to prepare insertion query \"{$insert}\" with type definition string \"{$typedef}\" and parameter the following {$count} parameters");
				Debug::printArray($params);
			}
			if (! $this->getBlockInsertionFlag()) {
				$status = $insert->prepareBindExecuteGetStatus($mysqli, $typedef, ...$params);
			} else {
				if ($print) {
					Debug::print("{$f} block insertion flag is set");
				}
				$status = SUCCESS;
			}
			$this->setInsertedFlag(true);
			if ($this->getInsertFlag()) {
				$this->setInsertFlag(false);
			}
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} failed to execute prepared insert query \"{$insert}\": \"{$err}\"");
				return $this->setObjectStatus(ERROR_MYSQL_EXECUTE);
			}elseif($print) {
				Debug::print("{$f} successfully executed prepared insertion query statement \"{$insert}\"");
			}
			// insert embedded data
			$embeds = $this->getEmbeddedDataStructures();
			if (! empty($embeds)) {
				foreach ($embeds as $groupname => $embed) {
					if ($print) {
						Debug::print("{$f} about to insert embedded data structure \"{$groupname}\"");
					}
					if (! $this->getBlockInsertionFlag()) {
						$status = $embed->insert($mysqli);
					} else {
						if ($print) {
							Debug::print("{$f} block insertion flag is set");
						}
						$status = SUCCESS;
					}
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} inserting embedded data structure \"{$groupname}\" returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}
				}
				if ($print) {
					Debug::print("{$f} successfully inserted embedded data structure \"{$groupname}\"");
				}
			}elseif($print) {
				Debug::print("{$f} there are no embedded data structures to insert");
			}
			// insert foreign data structures with foreign key constraints referring to this object
			if ($this->getPostInsertForeignDataStructuresFlag()) {
				if ($print) {
					Debug::print("{$f} insert foreign data structures flag is set");
				}
				$status = $this->insertForeignDataStructures($mysqli, CONST_AFTER);
				$this->setPostInsertForeignDataStructuresFlag(false);
				if ($this->getPostInsertForeignDataStructuresFlag()) {
					Debug::error("{$f} postinsertforeign flag is not getting clleared properly");
				}
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} insertForeignDataStructures returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}
			}elseif($print) {
				Debug::print("{$f} post insert foreign data structures flag is not set");
			}
			$this->setObjectStatus(SUCCESS);
			// insert polymorphic foreign key intersection data. This has to happen after dealing with foreign data structures because intersection tables have foreign key constraints
			$status = $this->insertIntersectionData($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} insertIntersectionData returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print) {
				Debug::print("{$f} successfully inserted IntersectionData");
			}
			// post-insertion hook
			$status = $this->afterInsertHook($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::error("{$f} after insert hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif(isset($transactionId)) {
				db()->commitTransaction($mysqli, $transactionId);
			}
			return $this->setObjectStatus($status);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setInvalidateCacheFlag(bool $value = true): bool{
		return $this->setFlag("invalidateCache", $value);
	}

	public function getInvalidateCacheFlag(): bool{
		return $this->getFlag("invalidateCache");
	}

	protected function afterEditHook(mysqli $mysqli, string $directive): int{
		if (cache()->enabled() && $this->getInvalidateCacheFlag()) {
			$ftn = new FullTableName($this->getDatabaseName(), $this->getTableName());
			$sql = $ftn->toSQL();
			$sha = sha1($sql);
			if (cache()->has("table_{$sha}")) {
				cache()->delete($sha);
			}
		}
		$this->dispatchEvent(new AfterEditEvent(), [
			'directive' => $directive
		]);
		return SUCCESS;
	}

	protected function afterSaveHook(mysqli $mysqli, string $directive): int{
		$f = __METHOD__;
		$print = false;
		$status = $this->afterEditHook($mysqli, $directive);
		if ($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} afterEditHook returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}elseif($print) {
			Debug::print("{$f} afterEditHook returned successfully");
		}
		$this->dispatchEvent(new AfterSaveEvent(), [
			'directive' => $directive
		]);
		return SUCCESS;
	}

	protected function afterInsertHook(mysqli $mysqli): int{
		$f = __METHOD__;
		$print = false;
		$status = $this->afterSaveHook($mysqli, DIRECTIVE_INSERT);
		if ($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} afterSaveHook returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		$this->dispatchEvent(new AfterInsertEvent());
		$status = $this->getObjectStatus();
		if (is_int($status) && $status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} dispatching afterInsert changed status to \"{$err}\"");
			return $status;
		}elseif($print) {
			Debug::print("{$f} successfully dispatched afterInsert event");
		}
		return SUCCESS;
	}

	/**
	 * XXX this is garbage
	 *
	 * @param ThrottleMeterData $meter
	 * @return ThrottleMeterData
	 */
	protected function initializeThrottleMeter(ThrottleMeterData $meter): ThrottleMeterData{
		$limit = 10; // 5; //60;
		$meter->setLimitPerMinute($limit);
		return $meter;
	}

	/**
	 * push foreign data structure list members to foreign dat astructure list $phylum
	 *
	 * @param string $phylum
	 * @param DataStructure[] ...$structs
	 * @return int
	 */
	public function pushForeignDataStructureListMember(string $phylum, ...$structs): int{
		$f = __METHOD__;
		$print = false;
		if (! isset($structs)) {
			Debug::error("{$f} missing second parameter");
		}
		$pushed = 0;
		foreach ($structs as $struct) {
			$this->setForeignDataStructureListMember($phylum, $struct);
			$key = $struct->getIdentifierValue();
			if (! $this->hasForeignDataStructureListMember($phylum, $key)) {
				if ($print) {
					Debug::print("{$f} pushed new child with identifier \"{$key}\"");
				}
				$pushed ++;
			}elseif($print) {
				Debug::print("{$f} already have a child with identifier \"{$key}\"");
			}
		}
		return $pushed;
	}

	/**
	 * prevent the current IP address from flooding the database with garbage
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function throttle(mysqli $mysqli): int{
		$meter = static::createThrottleMeterObject();
		$meter = $this->initializeThrottleMeter($meter);
		$timestamp = time();
		$quota_operator = OPERATOR_LESSTHANEQUALS;
		$select = $this->select()
			->where("insertIpAddress")
			->withTypeSpecifier('s')
			->withParameters([
			$_SERVER['REMOTE_ADDR']
		]);
		if ($meter->meter($mysqli, $timestamp, $quota_operator, $select)) {
			return SUCCESS;
		} else {
			return FAILURE;
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

	/**
	 * returns SUCCESS if the database does not already contain a row determined to be a duplicate of this object,
	 * ERROR_DUPLICATE_ENTRY otherwise.
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public function preventDuplicateEntry(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$print = false;
			$or = new OrCommand();
			$varnames = [];
			$typedef = "";
			$params = [];
			foreach ($this->getFilteredColumns(COLUMN_FILTER_DATABASE, COLUMN_FILTER_UNIQUE, COLUMN_FILTER_VALUED) as $vn => $column) {
				$or->pushParameters(new WhereCondition($vn, OPERATOR_EQUALS));
				array_push($varnames, $vn);
				array_push($params, $column->getValue());
				$typedef .= $column->getTypeSpecifier();
			}
			$composites = $this->getCompositeUniqueColumnNames();
			if (! empty($composites)) {
				foreach ($composites as $group) {
					if (! is_array($group)) {
						Debug::error("{$f} getCompositeUniqueColumnNames must return a multidimensional array");
					}
					$and = new AndCommand();
					foreach ($group as $column_name) {
						$column = $this->getColumn($column_name);
						if ($column->getUniqueFlag()) {
							Debug::error("{$f} datum at column \"{$column_name}\" cannot be singularly and composite unique");
						}elseif($column instanceof ForeignKeyDatum && $column->getPersistenceMode() === PERSISTENCE_MODE_INTERSECTION) {
							if ($print) {
								Debug::print("{$f} column \"{$column_name}\" is composite unique, foreign and stored in an intersection table");
							}
							if (! $column->hasValue()) {
								if ($print) {
									Debug::print("{$f} column \"{$column_name}\" has no value");
								}
								if ($column->hasForeignDataTypeName()) {
									$typename = $column->getForeignDataTypeName();
								}elseif($column->hasForeignDataSubtypeName()) {
									$typename = $column->getForeignDataSubtypeName();
								}
								$and->pushParameters(new WhereCondition($typename, OPERATOR_IS_NULL));
								array_push($varnames, $typename);
								continue;
							}
							if ($print) {
								Debug::print("{$f} column {$column_name} is stored in an intersection table");
							}
							// generate a WhereCondition selecting rows from the intersection table
							$where2 = static::whereIntersectionalHostKey($column->getForeignDataStructureClass(), $column_name);
							$typedef .= $column->getTypeSpecifier() . "s";
							array_push($params, $column->getValue(), $column_name);
						} else {
							array_push($varnames, $column_name);
							array_push($params, $column->getValue());
							$typedef .= $column->getTypeSpecifier();
							$where2 = new WhereCondition($column_name, OPERATOR_EQUALS);
						}
						$and->pushParameters($where2);
					}
					$or->pushParameters($and);
				}
			}
			if (! $or->hasParameters()) {
				if ($print) {
					Debug::print("{$f} no unique variables");
				}
				return SUCCESS;
			}
			$db = $this->getDatabaseName();
			$table = $this->getTableName();
			$select = QueryBuilder::select(...$varnames)->from($db, $table)->where($or);
			if ($print) {
				Debug::print("{$f} query for checking duplicate entries is \"{$select}\"");
			}
			$count = $select->prepareBindExecuteGetResultCount($mysqli, $typedef, ...$params);
			if ($count === 0) {
				if ($print) {
					Debug::print("{$f} no duplicates, you're good");
				}
				return SUCCESS;
			}elseif($print) {
				if ($this->hasColumn("name")) {
					$name = $this->getName();
				} else {
					$name = "unnamed";
				}
				$key = $this->getIdentifierValue();
				Debug::warning("{$f} object \"{$name}\" with unique identifier \"{$key}\" already exists in table \"{$db}{$table}\"; query statement is \"{$select}\"; parameters are as follows");
				Debug::printArray($params);
			}
			return ERROR_DUPLICATE_ENTRY;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getKeyGenerationMode(): int{
		return KEY_GENERATION_MODE_PSEUDOKEY;
	}

	public function setIdentifierName(?string $idn): ?string{
		$f = __METHOD__;
		if ($idn === null) {
			unset($this->identifierName);
			return null;
		}elseif(! is_string($idn)) {
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
		if ($this->hasIdentifierName()) {
			if ($print) {
				Debug::print("{$f} non-static identifier name is \"{$this->identifierName}\"");
			}
			return $this->identifierName;
		}elseif($print) {
			Debug::print("{$f} falling back to static identifier name");
		}
		return static::getIdentifierNameStatic();
	}

	public static function getIdentifierNameStatic():?string{
		$f = __METHOD__;
		$mode = static::getKeyGenerationMode();
		switch ($mode) {
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

	public function setIdentifierValue($value){
		return $this->setColumnValue($this->getIdentifierName(), $value);
	}

	public function getDataStructure(): DataStructure{
		return $this;
	}

	public static function getObjectFromKey(mysqli $mysqli, $key, ?int $mode = null): ?DataStructure{
		return static::getObjectFromVariable($mysqli, static::getIdentifierNameStatic(), $key, $mode);
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
		if ($mysqli->connect_errno) {
			Debug::error("{$f} Failed to connect to MySQL: ({$mysqli->connect_errno}) {$mysqli->connect_error}");
		}elseif(! $mysqli->ping()) {
			Debug::error("{$f} mysqli connection failed ping test: \"" . $mysqli->error . "\"");
		}
		return QueryBuilder::tableExists($mysqli, $this->getDatabaseName(), $this->getTableName());
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
		$f = __METHOD__;
		$print = false;
		if (is_abstract(static::class)) {
			Debug::error("{$f} don't call this on abstract classes");
		}elseif($print) {
			Debug::printStackTraceNoExit("{$f} entered; allocation mode is {$mode}");
		}
		$obj = new static($mode);
		$status = $obj->load($mysqli, $varname, $value);
		if ($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} loading object with {$varname} \"{$value}\" returned error status \"{$err}\"");
			return null;
		}
		return $obj;
	}

	public function loadFromKey(mysqli $mysqli, $key): int{
		$f = __METHOD__;
		$print = false;
		$status = $this->load($mysqli, $this->getIdentifierName(), $key);
		if ($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} loading object with key \"{$key}\" returned error status \"{$err}\"");
		}elseif($print) {
			Debug::print("{$f} loaded object with key \"{$key}\" successfully");
		}
		return $status;
	}

	public function ejectInsertIpAddress(){
		return $this->ejectColumnValue("insertIpAddress");
	}

	public function hasInsertIpAddress()
	{
		return $this->hasColumnValue("insertIpAddress");
	}

	public function setInsertIpAddress($ip){
		return $this->setColumnValue("insertIpAddress", $ip);
	}

	public function getInsertIpAddress(){
		return $this->getColumnValue("insertIpAddress");
	}

	/**
	 *
	 * @return InsertStatement
	 */
	public function getInsertStatement(){
		$expressions = [];
		foreach ($this->getFilteredColumnNames(COLUMN_FILTER_INSERT) as $column_name) {
			$expressions[$column_name] = new QuestionMark();
		}
		$insert = QueryBuilder::insert()->into($this->getDatabaseName(), $this->getTableName())
			->set($expressions);
		if ($this->getOnDuplicateKeyUpdateFlag()) {
			$expressions = [];
			foreach ($this->getFilteredColumnNames(COLUMN_FILTER_INSERT, "!" . COLUMN_FILTER_ID) as $column_name) {
				$expressions[$column_name] = new QuestionMark();
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
		$f = __METHOD__;
		$print = false;
		if ($print) {
			Debug::warning("{$f} override this is derived classes to determine which data are returned in the toArray function for a given use case");
		}
		switch ($config_id) {
			case "default":
			default:
				$print = false;
				if ($print) {
					if ($this->hasElementClass()) {
						Debug::print("{$f} element class is defined");
					} else {
						Debug::print("{$f} element class is undefined");
						if ($this->getSearchResultFlag()) {
							Debug::error("{$f} element class must be defined for object with search result flag set");
						}
					}
				}
				$config = [
					"num" => $this->hasColumn("num"),
					$this->getIdentifierName() => $this->hasColumn($this->getIdentifierName()),
					"insertTimestamp" => $this->hasColumn("insertTimestamp"),
					"updatedTimestamp" => $this->hasColumn("updatedTimestamp"),
					"dataType" => $this->hasColumn("dataType"),
					"searchResult" => $this->hasColumn("searchResult") && $this->getSearchResultFlag(),
					"status" => $this->hasColumn("status"),
					"prettyClassName" => false,
					"elementClass" => $this->hasElementClass()
				];
				//some common ones
				if($this->hasColumnValue('subtype') || $this instanceof StaticSubtypeInterface){
					$config['subtype'] = true;
				}
				if ($this->hasColumn("name")) {
					$config['name'] = true;
				}
				break;
		}
		return $config;
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
		if (is_string($config_id) || is_int($config_id)) {
			$keyvalues = $this->getArrayMembershipConfiguration($config_id);
		}elseif(is_array($config_id)) {
			$keyvalues = $config_id;
		} else {
			Debug::error("{$f} config ID must be a string, integer or array");
		}
		if ($print) {
			Debug::print("{$f} got the following array membership configuration:");
			Debug::printArray($keyvalues);
		}
		foreach ($keyvalues as $column_name => $value) {
			if (! $this->hasColumn($column_name)) {
				Debug::error("{$f} datum at column \"{$column_name}\" does not exist");
			}
			$this->getColumn($column_name)->configureArrayMembership($value);
		}
		return SUCCESS;
	}

	public function debugPrintForeignDataStructures(){
		$f = __METHOD__;
		foreach ($this->getForeignDataStructuresArray() as $key => $value) {
			if (is_array($value)) {
				foreach (array_keys($value) as $subvalue) {
					Debug::print("{$f} index \"{$key}\", ID \"{$subvalue}\"");
				}
			} else {
				Debug::print("{$f} index \"{$key}\"");
			}
		}
		Debug::printStackTrace();
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
		try {
			$num = new SerialNumberDatum("num");
			$num->setHumanReadableName(_("Serial number"));
			$insert = new TimestampDatum("insertTimestamp");
			$insert->setHumanReadableName(_("Insert timestamp"));
			// $insert->setSortable(true);
			$updated = new TimestampDatum("updatedTimestamp");
			$updated->setUserWritableFlag(true);
			// $updated->setSortable(true);
			$updated->setHumanReadableName(_("Update timestamp"));
			$insert_ip = new IpAddressDatum("insertIpAddress");
			$insert_ip->setSensitiveFlag(true);
			$datatype = new VirtualDatum("dataType");
			$status = new VirtualDatum("status");
			$pretty = new VirtualDatum("prettyClassName");
			$search_result = new VirtualDatum("searchResult");
			$elementClass = new VirtualDatum("elementClass");
			$columns = [
				$num,
				$insert,
				$updated,
				$insert_ip,
				$datatype,
				$status,
				$pretty,
				$search_result,
				$elementClass
			];

			if ($ds instanceof SoftDeletableInterface) {
				$soft = new TimestampDatum("softDeletionTimestamp");
				$soft->setDefaultValue(null);
				$soft->setUserWritableFlag(true);
				array_push($columns, $soft);
			}

			$mode = static::getKeyGenerationMode();
			if ($mode !== KEY_GENERATION_MODE_NATURAL) {
				switch ($mode) {
					case KEY_GENERATION_MODE_PSEUDOKEY:
						$key = new PseudokeyDatum('uniqueKey');
						break;
					case KEY_GENERATION_MODE_HASH:
						$key = new HashKeyDatum('uniqueKey');
						break;
					default:
						Debug::error("{$f} invalid key generation mode \"{$mode}\"");
				}
				$key->setUniqueFlag(true);
				$key->setIndexFlag(true);
				array_push($columns, $key);
			}
			if($ds === null){
				$ds = new static();
			}
			if($ds instanceof StaticSubtypeInterface && !$ds->hasColumn("subtype")){
				$subtype = new VirtualDatum("subtype");
				array_push($columns, $subtype);
			}
		} catch (Exception $x) {
			x($f, $x);
		}
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
		try {
			$print = false;
			$return = [];
			$ps = static::getDefaultPersistenceModeStatic();
			foreach ($columns as $column) {
				$vn = $column->getName();
				if ($print) {
					Debug::print("{$f} colunn {$vn}");
				}
				if (empty($column)) {
					Debug::error("{$f} datum is undefined");
				}elseif(! $column instanceof AbstractDatum) {
					$gottype = is_object($column) ? $column->getClass() : gettype($column);
					Debug::error("{$f} datum is a {$gottype}");
				}elseif($column instanceof DatumBundle) {
					$components = $column->generateComponents($ds);
					foreach ($components as $component) {
						$component->setDeclaredFlag(true);
						if (is_array($component)) {
							Debug::error("{$f} component is an array");
						}elseif(! $component->hasPersistenceMode()) {
							$component->setPersistenceMode($ps);
						}
						if ($ds !== null) {
							$component->setDataStructure($ds);
						}
						$return[$component->getName()] = $component;
					}
					continue;
				}
				$column->setDeclaredFlag(true);
				if ($ds !== null) {
					$column->setDataStructure($ds);
				}
				$return[$vn] = $column;
				static::reconfigureColumnEncryption($column);
				if (! $column->hasEncryptionScheme()) {
					if (! $column->hasPersistenceMode()) {
						$column->setPersistenceMode($ps);
					}elseif($print) {
						$ps2 = $column->getPersistenceMode();
						Debug::print("{$f} datum \"{$vn}\" already has its storage mode set to {$ps2}");
					}
					continue;
				}
				$scheme_class = $column->getEncryptionScheme();
				if (is_int($scheme_class)) {
					Debug::error("{$f} encryption scheme \"{$scheme_class}\" is an integer");
				}elseif(! class_exists($scheme_class)) {
					Debug::error("{$f} encryption scheme class \"{$scheme_class}\" does not exist");
				}
				$scheme_obj = new $scheme_class();
				if ($scheme_obj instanceof SharedEncryptionSchemeInterface) {
					if (! array_key_exists("replacementKeyRequested", $columns)) {
						if (! $ds instanceof DataStructure) {
							Debug::error("{$f} this part breaks down if you don't provide a data structure");
						}elseif($print) {
							Debug::print("{$f} replacementKeyRequested has not already been pushed");
						}
						$requested = new BooleanDatum("replacementKeyRequested");
						$requested->setDefaultValue(false);
						$requested->setDataStructure($ds);
						$return["replacementKeyRequested"] = $requested;
					}
				}
				$scheme = new $scheme_class($column);
				$components = $scheme->generateComponents($ds);
				$mode = $column->hasPersistenceMode() ? $column->getPersistenceMode() : $ps;
				foreach ($components as $component_name => $component) {
					if ($print) {
						Debug::print("{$f} about to set original variable name \"{$vn}\" for component at index \"{$component_name}\"");
					}
					$component->setOriginalDatumIndex($vn);
					if ($component->getPersistenceMode() !== PERSISTENCE_MODE_ENCRYPTED) {
						$component->setPersistenceMode($mode);
					}
					if ($column->isNullable()) {
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
		} catch (Exception $x) {
			x($f, $x);
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
		switch ($column_name) {
			case "dataType":
				return $this->getDataType();
			case "elementClass":
				return get_short_class($this->getElementClass());
			case "insertTimestampString":
				return $this->getInsertTimestampString();
			case "prettyClassName":
				return $this->getPrettyClassName();
			case "searchResult":
				return $this->getSearchResultFlag();
			case "status":
				return $this->getObjectStatus();
			case "updatedTimestampString":
				return $this->getUpdatedTimestampString();
			default:
				Debug::error("{$f} override this in derived classes -- column name is \"{$column_name}\"");
		}
	}

	/**
	 * Override accessor for foreign data structures by column name
	 *
	 * @param string $column_name
	 * @param string $key_or_offset
	 */
	public function getVirtualForeignDataStructure(string $column_name, $key_or_offset = null){
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	/**
	 * return true if the specified VirtualDatum's value can be returned, false otherwise
	 *
	 * @param string $column_name
	 * @return boolean
	 */
	public function hasVirtualColumnValue(string $column_name): bool{
		$f = __METHOD__;
		switch ($column_name) {
			case "status":
				return $this->hasObjectStatus();
			case "dataType":
			case "prettyClassName":
				return true;
			case "elementClass":
				return $this->hasElementClass();
			case "insertTimestampString":
				return $this->hasInsertTimestamp();
			case "searchResult":
				return $this->getSearchResultFlag();
			case "updatedTimestampString":
				return $this->hasUpdatedTimestamp();
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
		if ($config_id !== null) {
			if ($print) {
				Debug::print("{$f} about to call configureArrayMembership with the following parameter:");
				Debug::print($config_id);
			}
			$this->configureArrayMembership($config_id);
		}elseif($print) {
			Debug::print("{$f} config ID is null");
		}
		$columns = $this->getFilteredColumns(COLUMN_FILTER_ARRAY_MEMBER);
		if (count($columns) == 0) {
			Debug::error("{$f} column count is 0");
		}
		$arr = [];
		foreach ($columns as $column_name => $column) {
			if (! $column->getArrayMembershipFlag()) {
				if ($print) {
					Debug::error("{$f} datum \"{$column_name}\" does not have its array membership flag set");
				}
				continue;
			}elseif($column instanceof JsonDatum) {
				$value = $column->getValue();
			} else {
				if ($print) {
					Debug::print("{$f} about to contribute value of datum \"{$column_name}\" to the array");
				}
				$value = $column->getHumanReadableValue();
				while ($value instanceof ValueReturningCommandInterface) {
					if ($print) {
						Debug::print("{$f} datum {$column_name}'s value is a value-returning command; about to evaluate");
					}
					$value = $value->evaluate();
				}
			}
			$arr[$column_name] = $value;
		}
		if ($print) {
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
		$this->dispatchEvent(new BeforeGenerateKeyEvent());
		return SUCCESS;
	}

	protected function afterGenerateKeyHook($key): int{
		$this->dispatchEvent(new AfterGenerateKeyEvent($key));
		return SUCCESS;
	}

	protected function beforeGenerateInitialValuesHook(): int{
		if ($this->hasColumn("insertTimestamp")) {
			$time = $this->generateInsertTimestamp();
		} else {
			$time = time();
		}
		if ($this->hasColumn("updatedTimestamp") && ! $this->hasColumnValue("updatedTimestamp")) {
			$this->setUpdatedTimestamp($time);
		}
		if ($this->hasColumn("insertIpAddress") && ! $this->hasColumnValue("insertIpAddress")) {
			if (isset($_SERVER['REMOTE_ADDR'])) {
				$ip = $_SERVER['REMOTE_ADDR'];
			} else {
				$ip = SERVER_PUBLIC_IP_ADDRESS;
			}
			$this->setInsertIpAddress($ip);
		}
		if ($this->hasColumn("version") && ! $this->hasColumnValue("version")) {
			$this->setColumnValue("version", VERSION_CURRENT);
		}
		return SUCCESS;
	}

	/**
	 * generate initial column values prior to insertion
	 *
	 * @return int
	 */
	protected function generateInitialValues(): int{
		$f = __METHOD__;
		try {
			$print = false;
			// before generate initial values hook
			$status = $this->beforeGenerateInitialValuesHook();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} before generate key hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			// generate this object's identifier
			$status = $this->generateKey();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} generateKey returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print) {
				Debug::print("{$f} successfully generated key");
			}
			// iterate through the other columns and set their values
			$idn = $this->getIdentifierName();
			foreach ($this->getFilteredColumns("!" . COLUMN_FILTER_VIRTUAL, "!" . COLUMN_FILTER_ALIAS, "!" . COLUMN_FILTER_ID) as $name => $column) {
				if ($column->hasValue()) {
					if ($print) {
						Debug::print("{$f} column \"{$name}\" has already generated its value \"" . $column->getValue() . "\"");
					}
					continue;
				}elseif($idn !== null && $name === $idn) {
					if ($print) {
						Debug::print("{$f} skipping identifier column \"{$idn}\" generation");
					}
					continue;
				}elseif($print) {
					Debug::print("{$f} about to generate initial value for column \"{$name}\"");
				}
				if ($column->getPersistenceMode() !== PERSISTENCE_MODE_COOKIE || ! headers_sent()) {
					$status = $column->generate();
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} column \"{$name}\" returned error status \"{$err}\" when generating initial value");
						return $this->setObjectStatus($status);
					}elseif($print) {
						Debug::print("{$f} generated initial value for column \"{$name}\"");
					}
				}elseif($print) {
					Debug::print("{$f} this column is stored in cookies, and headers were already sent");
				}
			}
			// after generate initial values hook
			$status = $this->afterGenerateInitialValuesHook();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} afterGenerateInitialValuesHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print) {
				Debug::print("{$f} returning normally");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function afterGenerateInitialValuesHook(): int{
		return SUCCESS;
	}

	/**
	 * generate a unique identifier for this object.
	 *
	 * @return int : status code
	 */
	public function generateKey():int{
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				Debug::printStackTraceNoExit("{$f} entered");
			}
			$status = $this->beforeGenerateKeyHook();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} before generate key hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$mode = $this->getKeyGenerationMode();
			switch ($mode) {
				case KEY_GENERATION_MODE_NATURAL:
					if (! $this->hasIdentifierValue()) {
						Debug::printPost("{$f} natural key generation mode -- identifier is undefined");
					}
					break; // return SUCCESS;
				case KEY_GENERATION_MODE_LITERAL:
					Debug::error("{$f} don't call this for objects with literal gey generation mode");
				default:
			}
			$key = null;
			$idn = $this->getIdentifierName();
			if ($idn === null) {
				if ($print) {
					Debug::print("{$f} this object has no identifier whatsoever");
				}
			}elseif(! $this->hasColumn($idn)) {
				if ($print) {
					Debug::print("{$f} this object does not have a column \"{$idn}\"");
				}
			}elseif(! $this->hasIdentifierValue()) {
				if ($print) {
					Debug::print("{$f} key has not been generated");
				}
				if ($mode !== KEY_GENERATION_MODE_NATURAL) {
					if ($this->hasColumnValue('uniqueKey')) {
						$key = $this->getIdentifierValue();
						Debug::error("{$f} key was already generated -- returning \"{$key}\"");
						return SUCCESS;
					}elseif($print) {
						$column = $this->getColumn('uniqueKey');
						$column_class = $column->getClass();
						Debug::print("{$f} key generation mode is \"{$mode}\"; about to generate key with datum of class \"{$column_class}\"");
					}
					$status = $this->getColumn('uniqueKey')->generate();
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} generating key returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}
					$key = $this->getColumn('uniqueKey')->getValue();
					if ($print) {
						Debug::print("{$f} generated key \"{$key}\"");
					}
					// $this->setIdentifierValue($key);
					if (registry()->hasObjectRegisteredToKey($key)) {
						if ($this->getKeyGenerationMode() !== KEY_GENERATION_MODE_HASH) {
							Debug::error("{$f} impermissable key collision");
						}elseif($print) {
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
					registry()->registerObjectToKey($key, $this);
				}elseif($print) {
					Debug::print("{$f} natural key generation mode");
				}
			}elseif($print) {
				Debug::print("{$f} object's key was already generated");
			}

			$status = $this->afterGenerateKeyHook($key);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} after generate key hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function hasInsertTimestamp(){
		return $this->hasColumnValue('insertTimestamp');
	}

	public function getInsertTimestamp(){
		return $this->getColumnValue('insertTimestamp');
	}

	public function setInsertTimestamp($ts){
		return $this->setColumnValue("insertTimestamp", $ts);
	}

	public function setUpdatedTimestamp($ts){
		return $this->setColumnValue("updatedTimestamp", $ts);
	}

	public function generateInsertTimestamp(){
		$f = __METHOD__;
		try {
			if (! $this->hasInsertTimestamp()) {
				// Debug::print("{$f} generating insert timestamp now");
				return $this->setInsertTimestamp(time());
			}
			// Debug::print("{$f} insert timestamp already generated");
			return $this->getInsertTimestamp();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function hasColumnStatic(string $column_name): bool{
		$class = static::class;
		$ds = new $class();
		return $ds->hasColumn($column_name);
	}

	public static function hasConcreteColumnStatic(string $column_name): bool{
		$class = static::class;
		$ds = new $class();
		return $ds->hasColumn($column_name) && $ds->getColumn($column_name)->getPersistenceMode() === PERSISTENCE_MODE_DATABASE;
	}

	public function getSerialNumber():int{
		$f = __METHOD__;
		if(!$this->hasSerialNumber()){
			Debug::error("{$f} serial number is undefined");
		}
		return $this->getColumnValue("num");
	}

	public function setSerialNumber(int $num): int{
		return $this->setColumnValue("num", $num);
	}

	public function setInsertFlag(bool $value = true): bool{
		$f = __METHOD__;
		$print = false;
		if ($value && $this->getDeleteFlag()) {
			Debug::error("{$f} cannot simultaneously flag an object for both insertion and deletion. The object may have tripped its apoptosis signal");
			return $this->setDeleteFlag(false);
		}elseif($print) {
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
		if ($print) {
			$did = $this->getDebugId();
			Debug::printStackTraceNoExit("{$f} entered. Debug ID is \"{$did}\"");
		}
		return $this->setFlag(DIRECTIVE_DELETE, $value);
	}

	public function getDeleteFlag(): bool{
		return $this->getFlag(DIRECTIVE_DELETE);
	}

	public function setDeleteOldDataStructuresFlag(bool $value = true): bool{
		return $this->setFlag("deleteOld", $value);
	}

	public function getDeleteOldDataStructuresFlag(): bool{
		return $this->getFlag("deleteOld");
	}

	/**
	 * processes the input parameters of a subordinate form (i.e.
	 * one that is subindexed in a superior form) with the foreign data structure at index $column_name
	 * XXX TODO this function is a behemoth that needs to be broken up into several smaller ones
	 *
	 * @param AjaxForm $superior_form
	 *        	: Nextmost superior form containing the subindexed inputs of the subordinate
	 * @param array $arr
	 *        	: parameters to be processed by subordinate form
	 * @param array $files
	 *        	: files to be processed by subordinate form
	 * @param array $column_name
	 *        	: column name of this object's foreign key datum whose foreign data structure will process the subordinate form, and index in the superior form of the subordinate form for that foreign key datum
	 * @return int
	 */
	private function processSubordinateForm(AjaxForm $superior_form, ?array $arr, ?array $files, string $column_name){
		$f = __METHOD__;
		try {
			$print = false;
			if (! isset($superior_form)) {
				Debug::error("{$f} this function now requires a form class to retrieve subordinate form data indices");
			}
			$column = $this->getColumn($column_name);
			$subclass = $column->getForeignDataStructureClass($this);
			$mode = $column->getUpdateBehavior();
			switch ($mode) {
				case FOREIGN_UPDATE_BEHAVIOR_DELETE:
					if ($print) {
						Debug::print("{$f} delete foreign update behavior");
					}
					if (is_a($subclass, FileData::class, true)) {
						if ($print) {
							Debug::print("{$f} {$subclass} is a FileData");
						}
						$delete = is_array($files); // && array_key_exists($column_name, $files);
						if ($print) {
							if ($delete) {
								Debug::print("{$f} going to delete the old FileData");
							}elseif(! is_array($files)) {
								Debug::print("{$f} files array is not an array");
							}elseif(! array_key_exists($column_name, $files)) {
								Debug::print("{$f} key \"{$column_name}\" is not present is files array");
								Debug::printArray($files);
							}
						}
					} else {
						if ($print) {
							Debug::print("${f} {$subclass} is not a FileData");
						}
						$delete = true;
					}
					break;
				case FOREIGN_UPDATE_BEHAVIOR_NORMAL:
					if ($print) {
						Debug::print("{$f} default foreign update behavior");
					}
					$delete = false;
					break;
				default:
					Debug::error("{$f} invalid update behavior \"{$mode}\"");
			}
			if ($column instanceof ForeignKeyDatum) {
				if ($print) {
					Debug::print("{$f} datum is a foreign key datum");
				}
				$multiple = false;
			}elseif($column instanceof KeyListDatum) {
				if ($print) {
					Debug::print("{$f} datum is a key list datum");
				}
				$multiple = true;
			} else {
				Debug::error("{$f} neither of the above");
			}
			// get the data structure (same as old one)
			$existing = false;
			$idn = $subclass::getIdentifierNameStatic();
			if ((! $multiple && $this->hasForeignDataStructure($column_name)) || ($multiple && array_key_exists($idn, $arr) && $this->hasForeignDataStructureListMember($column_name, $arr[$idn]))) {
				if ($print) {
					Debug::print("{$f} there is already an existing data structure at index \"{$column_name}\"");
				}
				if ($multiple) {
					$old_struct = $this->getForeignDataStructureListMember($column_name, $arr[$idn]);
				} else {
					$old_struct = $this->getForeignDataStructure($column_name);
				}
				if ($old_struct->isDeleted() ){
					if ($print) {
						Debug::print("{$f} old data structure was already deleted");
					}
					if ($multiple) {
						$this->ejectForeignDataStructureListMember($column_name, $arr[$idn]);
					} else {
						$this->ejectForeignDataStructure($column_name);
					}
					$old_struct = null;
					$subordinate_structure = new $subclass();
					if ($print) {
						Debug::print("{$f} subordinate {$subclass} was deleted");
					}
				} else {
					$existing = true;
					if ($print) {
						Debug::print("{$f} a foreign data structure for column \"{$column_name}\" already exists");
					}
					if ($delete) {
						if ($print) {
							Debug::print("{$f} existing data structure is to be deleted");
						}
						$subordinate_structure = new $subclass();
					} else {
						if ($print) {
							Debug::print("{$f} existing data struture at index \"{$column_name}\" is not to be deleted");
						}
						$subordinate_structure = $old_struct;
					}
				}
			} else {
				if ($print) {
					Debug::print("{$f} creating new subordinate data structure of class \"{$subclass}\"");
				}
				$subordinate_structure = new $subclass();
			}
			// process subordinate form
			$subordinate_form = $superior_form->getSubordinateForm($column_name, $subordinate_structure);
			if ($print) {
				$sfc = $subordinate_form->getClass();
				Debug::print("{$f} subordinate form class for column \"{$column_name}\" is \"{$sfc}\"");
			}
			$status = $subordinate_structure->processForm($subordinate_form, $arr, $files);
			switch ($status) {
				case STATUS_UNCHANGED:
					if ($print) {
						Debug::print("{$f} processing form for subordinate structure does nothing useful");
					}
					return STATUS_UNCHANGED;
				case SUCCESS:
					if ($print) {
						Debug::print("{$f} successfully processed form");
					}
					if ($subordinate_structure->getDeleteFlag()) {
						if ($print) {
							Debug::print("{$f} subordinate structure is flagged for deletion");
						}
					}
					break;
				default:
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} processing subordinate form returned error status \"{$err}\"");
					return $status;
			}
			// flag existing structure for update
			if ($existing) {
				if ($print) {
					Debug::print("{$f} there was an existing data structure at column \"{$column_name}\"");
				}
				if ($delete) {
					if ($print) {
						Debug::print("{$f} foreign data structure \"{$column_name}\" wants you to delete the thing it's replacing");
					}
					if (DataStructure::equals($old_struct, $subordinate_structure)) {
						Debug::print("{$f} old data structure has not changed a bit");
						return SUCCESS;
					}
					if ($multiple) {
						$this->setOldDataStructureListMember($column_name, $old_struct);
					} else {
						$this->setOldDataStructure($column_name, $old_struct);
					}
					$old_struct->setDeleteFlag(true);
					$this->setDeleteOldDataStructuresFlag(true);
				} else {
					if ($print) {
						Debug::print("{$f} foreign data structure \"{$column_name}\" wants you to update it");
					}
					$subordinate_structure->setUpdateFlag(true);
					if ($column->getRelativeSequence() === CONST_BEFORE) {
						if ($print) {
							Debug::print("{$f} foreign data structure \"{$column_name}\" gets updated before this object");
						}
						$this->setPreUpdateForeignDataStructuresFlag(true);
					} else {
						if ($print) {
							Debug::print("{$f} foreign data structure \"{$column_name}\" gets updated after this object");
						}
						$this->setPostUpdateForeignDataStructuresFlag(true);
					}
				}
				$column->setUpdateFlag(true);
				$subordinate_structure->setObjectStatus(STATUS_READY_WRITE);
				// $status = $this->flagForeignDataStructureForUpdate($column, $delete); //XXX ???
			} else {
				if ($print) {
					Debug::print("{$f} there was not an existing data structure at column \"{$column_name}\"");
				}
				if ($subordinate_structure->getDeleteFlag()) {
					if ($print) {
						Debug::print("{$f} existing data structure was flagged for deletion before it was inserted into the database. This is only possible if the object was told to apoptose.");
					}
					return STATUS_UNCHANGED;
				}elseif($print) {
					Debug::print("{$f} new data structure did not pre-apoptose");
				}
			}

			// flag data foreign data structure for insertion
			if (! $existing || ($existing && $delete)) {
				if ($print) {
					if ($delete) {
						Debug::print("{$f} an existing data structure \"{$column_name}\" needs to be deleted");
					} else {
						Debug::print("{$f} no existing data structure \"{$column_name}\" to delete");
					}
				}
				// generate key if necessary
				$status = SUCCESS;
				$keygen = $subordinate_structure->getKeyGenerationMode();
				if ($keygen == KEY_GENERATION_MODE_NATURAL) {
					if ($print) {
						Debug::print("{$f} subordinate {$subclass} has a natural key");
					}
				}elseif($subordinate_structure->hasIdentifierValue()) {
					if ($print) {
						Debug::print("{$f} subordinate structure already has a key");
					}
				} else {
					$status = $subordinate_structure->generateKey();
					if ($status === ERROR_KEY_COLLISION) {
						if ($print) {
							Debug::print("{$f} key collision detected; skipping insertion");
						}
						$key = $subordinate_structure->getIdentifierValue();
						$subordinate_structure = registry()->getRegisteredObjectFromKey($key);
					}elseif($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} generateKey returned error status \"{$err}\"");
					}elseif($print) {
						Debug::print("{$f} no key collision detected; setting insert flag");
					}
				}
				// mark for insertion, unless it's apoptotic
				if ($subordinate_structure->getDeleteFlag()) {
					if ($print) {
						Debug::print("{$f} subordinate structure was already flagged for deletion");
					}
					$subordinate_structure->setDeleteFlag(false);
					if ($existing) {
						if ($multiple) {
							$this->ejectOldDataStructureListMember($column_name, $old_struct->getIdentifierValue());
						} else {
							$this->ejectOldDataStructure($column_name);
						}
					}
				} else {
					$subordinate_structure->setInsertFlag(true);
					if ($column->getRelativeSequence() === CONST_BEFORE) {
						if ($print) {
							Debug::print("{$f} foreign data structure \"{$column_name}\" gets inserted before this object");
						}
						$this->setPreInsertForeignDataStructuresFlag(true);
					} else {
						if ($print) {
							Debug::print("{$f} foreign data structure \"{$column_name}\" gets inserted after this object");
						}
						$this->setPostInsertForeignDataStructuresFlag(true);
					}
					$subordinate_structure->setObjectStatus(STATUS_READY_WRITE);
				}
			}elseif($print) {
				Debug::print("{$f} foreign data structure \"{$column_name}\" will not get inserted today");
			}
			// set reference to foreign data structure
			if ($multiple) {
				if ($print) {
					Debug::print("{$f} about to set foreign data structure list member at column name \"{$column_name}\"");
				}
				$this->setForeignDataStructureListMember($column_name, $subordinate_structure);
			} else {
				if ($print) {
					Debug::print("{$f} about to set foreign data structure at column name \"{$column_name}\"");
				}
				$this->setForeignDataStructure($column_name, $subordinate_structure);
			}
			if ($print) {
				Debug::print("{$f} returning normally (success)");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function apoptoseHook($caller): int{
		$this->dispatchEvent(new ApoptoseEvent($caller));
		return SUCCESS;
	}

	/**
	 * marks this object for deletion
	 */
	public final function apoptose($caller): int{
		$f = __METHOD__;
		try {
			$print = false;
			$this->setDeleteFlag(true);
			$status = $this->apoptoseHook($caller);
			switch ($status) {
				case SUCCESS:
					if ($print) {
						Debug::print("{$f} apoptoseHook returned success");
					}
					break;
				default:
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} apoptoseHook returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	private function processFormInput(string $column_name, InputInterface $input, int &$unchanged, ?array $arr, ?array $files): int{
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				$input_class = $input->getClass();
				Debug::print("{$f} about to bind datum \"{$column_name}\" to a {$input_class}");
			}
			$column = $this->getColumn($column_name);
			$input->bindContext($column);
			// have the input process input parameters to look for its value
			if (is_array($arr)) {
				if ($print) {
					Debug::print("{$f} processing array for input \"{$column_name}\"");
					$input->debug();
				}
				$status = $input->processArray($arr);
				if ($status !== SUCCESS) {
					if ($print) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} processArray for {$input_class} input {$column_name} returned error status \"{$err}\"");
					}
					return $column->setObjectStatus($status);
				}
			}elseif($print) {
				Debug::print("{$f} input variable that is supposed to be an array is not");
			}
			// CheckedInput has an implicit value of false if its name is not found
			if ($input instanceof CheckedInput && ! $input->isChecked()) {
				if ($print) {
					Debug::print("{$f} input is a checkbox or something like that and unchecked");
				}
				if ($column instanceof BooleanDatum) {
					$column->setValue(false);
				}elseif($column->hasValue()) {
					if ($print) {
						Debug::print("{$f} datum has a value");
					}
					$column->ejectValue();
				}elseif($print) {
					Debug::print("{$f} datum does not have a value");
				}
				return SUCCESS;
			}elseif( // input isn't a checkbox/file and has no apparent value
			! $input instanceof CheckedInput && ! $input instanceof FileInput && ! $input->hasValueAttribute() && ! $column->getSensitiveFlag()) // XXX TODO what is this for again ???
			{
				if ($print) {
					Debug::print("{$f} input at column \"{$column_name}\" does not have a value attribute");
					Debug::printArray($arr);
				}
				if ($column->getProcessValuelessInputFlag()) {
					if ($print) {
						Debug::print("{$f} column \"{$column_name}\" is flagged to process valueless input anyway");
					}
				} else {
					if ($print) {
						Debug::print("{$f} column \"{$column_name}\" does is NOT flagged to process valueless inputs");
					}
					if ($column->hasValue()) {
						if ($print) {
							Debug::print("{$f} column \"{$column_name}\" already has a value; ejecting it now");
						}
						$column->ejectValue();
						return SUCCESS;
					} else {
						$unchanged ++;
						if ($print) {
							Debug::print("{$f} column \"{$column_name}\" does not have a value; incremented unchanged column count to {$unchanged}");
						}
						return STATUS_UNCHANGED;
					}
				}
			}elseif($print) {
				Debug::print("{$f} input \"{$column_name}\" has a value attribute");
			}
			if ($print) {
				$column_class = $column->getClass();
				Debug::print("{$f} about to call {$column_class}->processInput() for column \"{$column_name}\"");
			}
			// have the datum ask the input to transfer a value
			if ($print) {
				$column->debug();
			}
			$status = $column->processInput($input);
			if ($input instanceof FileInput && $this instanceof FileData) {
				if ($print) {
					Debug::print("{$f} input at column \"{$column_name}\" is a file input");
				}
				$subfiles = null;
				if (is_array($files) && array_key_exists($column_name, $files)) {
					if ($print) {
						Debug::print("{$f} name \"{$column_name}\" exists in the files array");
					}
					$subfiles = $files[$column_name];
				}elseif($print) {
					Debug::print("{$f} name \"{$column_name}\" does NOT exist in the files array");
					Debug::print($files);
				}
				if (! empty($subfiles)) {
					if ($print) {
						Debug::print("{$f} about to process an array of repacked incoming files");
					}
					$status = $this->processRepackedIncomingFiles($subfiles);
				} else {
					$sub_arr = is_array($arr) && array_key_exists($column_name, $arr) ? $arr[$column_name] : null;
					if (! empty($sub_arr) && array_key_exists("resized", $sub_arr)) {
						if ($print) {
							Debug::print("{$f} time to process resized file");
						}
						$status = $this->processResizedFiles($sub_arr);
					}elseif($print) {
						Debug::print("{$f} not going to bother looking for resized files");
					}
				}
			}elseif($print) {
				Debug::print("{$f} input at column \"{$column_name}\" is not a file input");
			}
			if ($status === STATUS_UNCHANGED) {
				if ($print) {
					Debug::print("{$f} column \"{$column_name}\" did not change");
				}
				$unchanged ++;
			}elseif($print) {
				Debug::print("{$f} column \"{$column_name}\" changed");
			}
			if ($print) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::print("{$f} after processing for input for column \"{$column_name}\" returning status code \"{$err}\" with unchanged column count {$unchanged}");
			}
			return $status;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * process the input parameters of a submitted AjaxForm
	 *
	 * @param AjaxForm $form
	 *        	: instance of the form that was submitted
	 * @param array $arr
	 *        	: input parameters to process
	 * @param array $files
	 *        	: files submitted with the form
	 * @return int
	 */
	public function processForm(AjaxForm $form, ?array $arr, ?array $files = null): int{
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				Debug::print("{$f} about to process the following array:");
				Debug::printArray($arr);
			}
			// $this->setFlag("processedForm", true);
			$input_classes = $form->getFormDataIndices($this);
			if ($print) {
				$fc = $form->getClass();
				Debug::print("{$f} about to process the following input classes for form \"{$fc}\":");
				Debug::printArray($input_classes);
			}
			$unchanged = 0;
			foreach ($input_classes as $column_name => $input_class) {
				if ($print) {
					Debug::print("{$f} column name \"{$column_name}\"; current unchanged column count is {$unchanged}");
				}
				$column = $this->getColumn($column_name);
				if ($print) {
					$column->debug();
				}
				$input = new $input_class(ALLOCATION_MODE_FORM);
				///$input->setColumnName($column_name);
				// if the input is a subordinate form, match the subordinate form the the foreign data structure at the same index
				if ($input instanceof AjaxForm) {
					$print = false;
					if ($print) {
						Debug::print("{$f} column name \"{$column_name}\" is a subordinate form");
					}
					if (is_array($files) && array_key_exists($column_name, $files)) {
						if ($print) {
							Debug::print("{$f} yes, there are incoming files to process");
						}
						$subfiles = $files[$column_name];
					} else {
						if ($print) {
							Debug::print("{$f} no, there are no incoming files to process");
						}
						$subfiles = null;
					}
					if (is_array($arr) && array_key_exists($column_name, $arr)) {
						$sub_arr = $arr[$column_name];
						if ($print) {
							Debug::print("{$f} about to print sub array \"{$column_name}\"");
							Debug::printarray($sub_arr);
						}
					} else {
						$sub_arr = null;
					}
					if ($print && empty($sub_arr)) {
						Debug::warning("{$f} key \"{$column_name}\" does not exist");
						Debug::printArray($arr);
					}
					if ($column instanceof KeyListDatum) {
						if (! is_array($sub_arr)) {
							Debug::error("{$f} subordinate array at column name \"{$column_name}\" is not an array");
						}
						$child_count = count($sub_arr);
						if ($print) {
							Debug::print("{$f} processing {$child_count} objects for a KeyListDatum");
						}
						if ($child_count > 0) {
							$unchanged_children = 0;
							foreach (array_keys($sub_arr) as $child_key) {
								if (is_array($sub_arr) && array_key_exists($child_key, $sub_arr)) {
									$child_arr = $sub_arr[$child_key];
								} else {
									$child_arr = null;
								}
								if (is_array($subfiles) && array_key_exists($child_key, $subfiles)) {
									$child_files = $subfiles[$child_key];
								} else {
									$child_files = null;
								}
								if (! empty($child_arr) || ! empty($child_files)) {
									if ($print) {
										Debug::print("{$f} about to process subordinate form at column name \"{$column_name}\" with iterator \"{$child_key}\"");
									}
									$status = $this->processSubordinateForm($form, $child_arr, $child_files, $column_name);
									switch ($status) {
										case STATUS_UNCHANGED:
											if ($print) {
												Debug::print("{$f} one item has not changed");
											}
											$unchanged_children ++;
											continue 2;
										case SUCCESS:
											break;
										default:
											$err = ErrorMessage::getResultMessage($status);
											Debug::error("{$f} processing subordinate form at column name \"{$column_name}\" returned error status \"{$err}\"");
									}
								}elseif($print) {
									Debug::print("{$f} subordinate form at column \"{$column_name}\" will not be processed");
								}
							}
							if ($unchanged_children === count($sub_arr)) {
								$unchanged ++;
								if ($print) {
									Debug::print("{$f} unchanged child object count for column \"{$column_name}\" is equal to the number of children; incremented unchanged column count to {$unchanged}");
								}
							} else {
								if ($print) {
									Debug::print("{$f} at least one foreign data structure list member changed; unchanged column count remains the same at {$unchanged}");
								}
								$status = SUCCESS;
							}
							$unchanged_children = 0;
						}
					}elseif($column instanceof ForeignKeyDatum) {
						if (! empty($sub_arr) || ! empty($subfiles)) {
							if ($print) {
								Debug::print("{$f} about to process subordinate form at column \"{$column_name}\"");
							}
							$status = $this->processSubordinateForm($form, $sub_arr, $subfiles, $column_name);
							// this was added after disabling the increment on line 5265
							if ($status === STATUS_UNCHANGED) {
								if ($print) {
									Debug::print("{$f} processing subordinate form for column \"{$column_name}\" resulted in no change");
								}
								$unchanged ++;
							}elseif($print) {
								Debug::print("{$f} processing subordinate form resulted in a change to foreign data structure \"{$column_name}\"");
							}
						}elseif($print) {
							Debug::print("{$f} subordinate form at column \"{$column_name}\" will not be processed");
						}
					} else {
						Debug::error("{$f} neither of the above");
					}
				} else { // input is not a subordinate form
					$form->attachNegotiator($input);
					$status = $this->processFormInput($column_name, $input, $unchanged, $arr, $files);
				}
				// deal with results of handling input processing for this column
				if (! isset($status)) {
					Debug::error("{$f} status is undefined");
					return $this->setObjectStatus(FAILURE);
				}elseif($status === SUCCESS) {
					if ($print) {
						Debug::print("{$f} assigned value to column name \"{$column_name}\"; unchanged column count is {$unchanged}");
					}
				}elseif($status === STATUS_UNCHANGED) {
					if ($print) {
						Debug::print("{$f} value at column \"{$column_name}\" did not change");
					}
					continue;
				} else {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} processInput for datum at column \"{$column_name}\" returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}
			}
			$status = $this->getObjectStatus();
			if ($status === STATUS_UNCHANGED) {
				if ($print) {
					Debug::print("{$f} this object was marked unchanged at some point");
				}
				return $status;
			}elseif($unchanged === count($input_classes)) {
				if ($print) {
					Debug::print("{$f} nothing changed ({$unchanged} unchanged columns)");
				}
				return STATUS_UNCHANGED;
			}elseif($print) {
				Debug::print("{$f} returning normally");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function ejectSerialNumber():?int{
		return $this->ejectColumnValue("num");
	}

	/**
	 *
	 * @param Datum[] $columns
	 * @return Datum[]
	 */
	public static function reconfigureColumns(array &$columns, ?DataStructure $ds = null): void{}

	/**
	 *
	 * @param string $column_name
	 * @return Datum
	 */
	public static function getDatumClassStatic(string $column_name):string{
		$f = __METHOD__;
		try {
			$class = static::class;
			$ds = new $class();
			return $ds->getColumn($column_name)->getClass();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getTypeSpecifierStatic(...$column_names): string{
		$dummy = new static();
		if (count($column_names) === 1 && is_array($column_names[0])) {
			$column_names = $column_names[0];
		}
		return $dummy->getTypeSpecifier($column_names);
	}

	public function getTypeSpecifier(array $column_names): string{
		$f = __METHOD__;
		try {
			$string = "";
			foreach ($column_names as $column_name) {
				if (is_object($column_name)) {
					if ($column_name instanceof TypeSpecificInterface) {
						$string .= $column_name->getTypeSpecifier();
					} else {
						Debug::error("{$f} column name is an object created " . $column_name->getDeclarationLine());
					}
				}elseif(is_string($column_name)) {
					$string .= $this->getColumn($column_name)->getTypeSpecifier();
				} else {
					Debug::error("{$f} column name is neither string nor type specifiying object");
				}
			}
			return $string;
		} catch (Exception $x) {
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
		try {
			$print = false;
			if ($this->getKeyGenerationMode() === KEY_GENERATION_MODE_LITERAL) {
				return $this->getIdentifierName();
			}
			$vn = $this->getIdentifierName();
			if ($vn == null) {
				if ($print) {
					Debug::print("{$f} identifier name is null");
				}
				return null;
			}elseif($print) {
				Debug::print("{$f} identifier name is \"{$vn}\"");
			}
			$key = $this->getColumnValue($vn);
			if (! is_int($key) && ! is_string($key)) {
				$gottype = gettype($key);
				$decl = $this->getDeclarationLine();
				Debug::error("{$f} key is a {$gottype}. Declared {$decl}");
			}
			iF ($print) {
				Debug::print("{$f} returning \"{$key}\"");
			}
			return $key;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function acquireForeignDataStructure(mysqli $mysqli, string $column_name){
		return $this->loadForeignDataStructure($mysqli, $column_name, false, 3);
	}

	public function getUpdateViewName(): string{
		return $this->getTableName();
	}

	public function getIdentifierNameCommand(): GetIdentifierNameCommand{
		return new GetIdentifierNameCommand($this);
	}

	public function select(...$column_names): SelectStatement{
		return static::selectStatic($this, ...$column_names);
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
	public static function selectStatic(?DataStructure $object = null, ...$column_names): SelectStatement{
		$f = __METHOD__;
		try {
			$print = $object !== null && $object->getDebugFlag();
			if ($object === null) {
				$object = new static();
			}
			$db_column_names = [];
			$embedded_columns = [];
			$alias_column_names = [];
			if (isset($column_names) && is_array($column_names) && ! empty($column_names)) {
				foreach ($column_names as $column_name) {
					if (is_array($column_name)) {
						Debug::error("{$f} column name is an array");
					}
					$column = $object->getColumn($column_name);
					if ($column->applyFilter(COLUMN_FILTER_DISABLED)) {
						if ($print) {
							Debug::print("{$f} column \"{$column_name}\" is disabled");
						}
						continue;
					}elseif($column->applyFilter(COLUMN_FILTER_DATABASE)) {
						array_push($db_column_names, $column_name);
					}elseif($column->applyFilter(COLUMN_FILTER_EMBEDDED)) {
						$group = $column->getEmbeddedName();
						if (! array_key_exists($group, $embedded_columns)) {
							$embedded_columns[$group] = [];
						}
						array_push($embedded_columns[$group], $column_name);
					}elseif($column->applyFilter(COLUMN_FILTER_ALIAS)) {
						array_push($alias_column_names, $column_name);
					} else {
						Debug::error("{$f} attempting to select non-database, non-embedded column \"{$column_name}\"");
					}
				}
			} else {
				$db_column_names = $object->getFilteredColumnNames(COLUMN_FILTER_DATABASE);
				$alias_column_names = $object->getFilteredColumnNames(COLUMN_FILTER_ALIAS);
				$temp = $object->getFilteredColumns(COLUMN_FILTER_EMBEDDED);
				if (! empty($temp)) {
					foreach ($temp as $column_name => $column) {
						$group = $column->getEmbeddedName();
						if (! array_key_exists($group, $embedded_columns)) {
							$embedded_columns[$group] = [];
						}
						array_push($embedded_columns[$group], $column_name);
					}
				}
			}
			$identifierName = static::getIdentifierNameStatic();
			// if there are any embedded or subqueried columns and you did not opt to select the identifier, select it as it is needed to match the embedded/subqueried columns
			if (! empty($embedded_columns) || ! empty($alias_column_names)) {
				if (false === array_search($identifierName, $db_column_names)) {
					array_push($db_column_names, $identifierName);
				}elseif($print) {
					Debug::print("{$f} identifier column name is already getting queried");
				}
			}
			$db = $object->getDatabaseName();
			$table = $object->getTableName();
			$embedded_structures = $object->getEmbeddedDataStructures();
			if (! empty($embedded_columns) || ! empty($alias_column_names)) {
				$select_us = [];
				foreach ($db_column_names as $column_name) {
					$column_name_escaped = back_quote($column_name);
					array_push($select_us, new GetDeclaredVariableCommand("t0.{$column_name_escaped} as {$column_name_escaped}"));
				}
				$select = new SelectStatement();
				$select->withJoinExpressions(TableFactor::create()->withDatabaseName($db)->withTableName($table)->as("t0"));
				if (! empty($alias_column_names)) {
					foreach ($alias_column_names as $column_name) {
						$column = $object->getColumn($column_name);
						$alias = $column->getColumnAlias();
						$expr = $alias->getExpression();
						if ($expr instanceof SelectStatement) {
							if ($expr->hasParameters()) {
								if ($print) {
									Debug::print("{$f} column alias \"{$alias}\" has parameters");
								}
								$select->setFlag("unassigned", true);
							}
						}
						array_push($select_us, $alias);
					}
				}
				$mysqli = db()->getConnection();
				if (! empty($embedded_columns)) {
					if ($print) {
						Debug::print("{$f} about to create join expressions for embedded data structures");
					}
					// if the user passsed column names and one or more of them is an embedded column, only select those columns
					foreach ($embedded_structures as $group => $e) {
						
						if(!$e->tableExists($mysqli)){
							$e->createTable($mysqli);
						}
						
						foreach ($embedded_columns[$group] as $column_name) {
							array_push($select_us, new ColumnAlias(new ColumnAliasExpression($group, $column_name), $column_name));
						}
						$bexpr = BinaryExpressionCommand::equals(new ColumnAliasExpression($group, "joinKey"), new ColumnAliasExpression("t0", $identifierName));
						$bexpr->setEscapeType(ESCAPE_TYPE_NONE);
						$select->leftJoin(TableFactor::create()->withDatabaseName($e->getDatabaseName())->withTableName($e->getTableName())->as($group))->on($bexpr);
					}
				}
				$select->select(...$select_us);
			} else {
				if ($print) {
					Debug::print("{$f} there are no embedded data structures");
				}
				$select = new SelectStatement(...$db_column_names);
				$select->from($db, $table);
			}
			if ($print) {
				$string = $select->toSQL();
				Debug::print("{$f} returning query statement \"{$string}\"");
			}
			return $select;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getReloadedFlag():bool{
		return $this->getFlag("reloaded");
	}

	public function setReloadedFlag(bool $value = true):bool{
		return $this->setFlag("reloaded", $value);
	}

	/**
	 * call reload() on foreign data structures
	 *
	 * @param mysqli $mysqli
	 * @param boolean $foreign
	 * @return int
	 */
	public final function reloadForeignDataStructures(mysqli $mysqli, bool $foreign = true): int{
		$f = __METHOD__;
		try {
			$print = false;
			$columns = $this->getFilteredColumns(COLUMN_FILTER_FOREIGN);
			if (! empty($columns)) {
				foreach ($columns as $column_name => $column) {
					if ($column instanceof ForeignKeyDatum) {
						if ($this->hasForeignDataStructure($column_name)) {
							$fds = $this->getForeignDataStructure($column_name);
							if (! $fds->getLoadedFlag() || $fds->getReloadedFlag()) {
								if ($print) {
									if (! $fds->getLoadedFlag()) {
										Debug::print("{$f} foreign data structure \"{$column_name}\" was not loaded from the database in the first place");
									}elseif($fds->getReloadedFlag()) {
										Debug::print("{$f} foreign data structure \"{$column_name}\" has already been reloaded");
									}
								}
								continue;
							}
							$status = $fds->reload($mysqli, $foreign);
							if ($status !== SUCCESS) {
								$err = ErrorMessage::getResultMessage($status);
								$fdsc = $fds->getClass();
								$fdsk = $fds->getIdentifierValue();
								Debug::warning("{$f} reloading foreign data structure {$fdsc} \"{$column_name}\" with key \"{$fdsk}\" returned error status \"{$err}\"");
								return $this->setObjectStatus($status);
							}
						}elseif($print) {
							Debug::print("{$f} foreign object \"{$column_name}\" will not be reloaded");
						}
					}elseif($column instanceof KeyListDatum) {
						if ($this->hasForeignDataStructureList($column_name)) {
							$list = $this->getForeignDataStructureList($column_name);
							foreach ($list as $foreign_key => $fds) {
								if (! $fds->getLoadedFlag() || $fds->getReloadedFlag()) {
									if ($print) {
										if (! $fds->getLoadedFlag()) {
											Debug::print("{$f} foreign data structure \"{$column_name}\" with key \"{$foreign_key}\" was not loaded from the database in the first place");
										}elseif($fds->getReloadedFlag()) {
											Debug::print("{$f} foreign data structure \"{$column_name}\" with key \"{$foreign_key}\" has already been reloaded");
										}
									}
									continue;
								}
								$status = $fds->reload($mysqli, $foreign);
								if ($status !== SUCCESS) {
									$err = ErrorMessage::getResultMessage($status);
									Debug::warning("{$f} reloading foreign data structure from list \"{$column_name}\" with key \"{$foreign_key}\" returned error status \"{$err}\"");
									return $this->setObjectStatus($status);
								}
							}
						}elseif($print) {
							Debug::print("{$f} no foreign data structure list \"{$column_name}\"");
						}
					}
				}
			}elseif($print) {
				Debug::print("{$f} no foreign key columns");
			}
			return SUCCESS;
		} catch (Exception $x) {
			X($f, $x);
		}
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
		$f = __METHOD__;
		try {
			$print = false;
			// $this->clearFlags();
			$idn = $this->getIdentifierName();
			$iv = $this->getIdentifierValue();
			$results = $this->select()->where($idn)->prepareBindExecuteGetResult($mysqli, $this->getColumnTypeSpecifier($idn), $iv)->fetch_all(MYSQLI_ASSOC);
			if (! array_key_exists(0, $results)) {
				if ($print) {
					Debug::warning("{$f} failed to reload object: not found");
				}
				return $this->loadFailureHook();
			}elseif($print) {
				Debug::print("{$f} successfully reloaded object with key \"{$iv}\"");
			}
			$status = $this->processQueryResultArray($mysqli, $results[0]);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} processQueryResultArray returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$this->setReloadedFlag(true);
			if ($foreign) {
				$status = $this->reloadForeignDataStructures($mysqli, $foreign);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} reloadForeignDataStructures returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}
			}elseif($print) {
				Debug::print("{$f} skipping foreign data structure reload");
			}
			$this->setReloadedFlag(false);
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	private final function reloadCopy(mysqli $mysqli, bool $foreign = true): DataStructure{
		$f = __METHOD__;
		try {
			ErrorMessage::deprecated($f);
			$print = false;
			$reloaded = new static();
			$reloaded->setDatabaseName($this->getDatabaseName());
			$reloaded->setTableName($this->getTableName());
			$idn = $this->getIdentifierName();
			$idv = $this->getIdentifierValue();
			$status = $reloaded->load($mysqli, $idn, $idv);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} reloading object with {$idn} \"{$idv}\" returned error status \"{$err}\"");
				$reloaded->setObjectStatus($status);
				return null; // $reloaded;
			}
			if ($this->hasForeignDataStructures()) {
				foreach (array_keys($this->foreignDataStructures) as $fdsk) {
					if (is_array($this->foreignDataStructures[$fdsk])) {
						foreach ($this->getForeignDataStructureList($fdsk) as $struct) {
							if ($struct->getInsertFlag()) {
								$reloaded->setForeignDataStructureListMember($fdsk, $struct->reload($mysqli, false));
							} else {
								$reloaded->setForeignDataStructureListMember($fdsk, $struct);
							}
						}
					} else {
						if (! $this->hasForeignDataStructure($fdsk)) {
							continue;
						}
						$struct = $this->getForeignDataStructure($fdsk);
						if ($struct->getInsertFlag()) {
							$reloaded->setForeignDataStructure($fdsk, $struct->reload($mysqli, false));
						} else {
							$reloaded->setForeignDataStructure($fdsk, $struct);
						}
					}
				}
			}elseif($print) {
				Debug::print("{$f} foreign data structures are undefined");
			}
			if ($foreign) {
				if ($print) {
					Debug::print("{$f} reloading foreign data structures");
				}
				$status = $reloaded->loadForeignDataStructures($mysqli, false, 3);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} reloading foreign data structures returned error status \"{$err}\"");
					$reloaded->setObjectStatus($status);
					return $reloaded;
				}
			}elseif($print) {
				Debug::print("{$f} not going to reload foreign data structures");
			}
			// copy flags, but not all of them
			if ($this->hasFlags()) {
				foreach ($this->declareFlags() as $flag) {
					if (! $this->getFlag($flag) || in_array($flag, [
						// 'backupDisabled',
						DIRECTIVE_REPLICATE,
						DIRECTIVE_DELETE,
						"expanded",
						DIRECTIVE_INSERT,
						DIRECTIVE_PREINSERT_FOREIGN,
						DIRECTIVE_POSTINSERT_FOREIGN,
						'lazy',
						'processedForm',
						'searchResult',
						DIRECTIVE_UPDATE,
						DIRECTIVE_PREUPDATE_FOREIGN,
						DIRECTIVE_POSTUPDATE_FOREIGN
					], true)) {
						continue;
					}
					$reloaded->setFlag($flag, true);
				}
			}
			$reloaded->setObjectStatus(SUCCESS);
			if ($reloaded->isRegistrable()) {
				registry()->update($reloaded->getIdentifierValue(), $reloaded);
			}
			return $reloaded;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getIdentifierValueCommand(){
		return new GetColumnValueCommand($this, $this->getIdentifierName());
	}

	public function updateTimestamp(mysqli $mysqli){
		$f = __METHOD__;
		try {
			$this->setUpdatedTimestamp(time());
			$status = $this->update($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} update() returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			// Debug::print("{$f} timestamp update succeeded");
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getInsertTimestampString(){
		return getDateTimeStringFromTimestamp($this->getInsertTimestamp());
	}

	public function getUpdatedTimestampString(){
		return getDateTimeStringFromTimestamp($this->getUpdatedTimestamp());
	}

	public function isDeleted():bool{
		return $this->hasObjectStatus() && $this->getObjectStatus() === STATUS_DELETED;
	}

	public function hasUpdatedTimestamp():bool{
		return $this->hasColumnValue("updatedTimestamp");
	}

	public function getUpdatedTimestamp(){
		if (! $this->hasUpdatedTimestamp()) {
			return $this->getInsertTimestamp();
		}
		return $this->getColumnValue("updatedTimestamp");
	}

	protected function beforeUpdateForeignDataStructuresHook(mysqli $mysqli, string $when): int{
		$this->dispatchEvent(new BeforeUpdateForeignDataStructuresEvent($when));
		return SUCCESS;
	}

	/**
	 * update foreign data structures that are flagged for update
	 *
	 * @param mysqli $mysqli
	 * @param string $when
	 *        	: see description of similarly-named parameter for insertForeignDataStructures()
	 * @return int
	 */
	private function updateForeignDataStructures(mysqli $mysqli, string $when): int{
		$f = __METHOD__;
		try {
			$print = $this->getDebugFlag();
			switch ($when) {
				case CONST_BEFORE:
					$columns = $this->getFilteredColumns(COLUMN_FILTER_BEFORE);
					// $status = $this->permit(user(), DIRECTIVE_PREUPDATE_FOREIGN);
					break;
				case CONST_AFTER:
					$columns = $this->getFilteredColumns(COLUMN_FILTER_AFTER);
					// $status = $this->permit(user(), DIRECTIVE_POSTUPDATE_FOREIGN);
					break;
				default:
					Debug::error("{$f} invalid foreign data structure type");
			}
			$status = $this->beforeUpdateForeignDataStructuresHook($mysqli, $when);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} beforeUpdateForeignDataStructuresHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print) {
				Debug::print("{$f} beforeUpdateForeignDataStructureHook returned success");
			}
			foreach ($columns as $column_name => $column) {
				if ($column instanceof KeyListDatum) {
					$multiple = true;
				}elseif($column instanceof ForeignKeyDatum) {
					$multiple = false;
				} else {
					Debug::error("{$f} neither of the above for column \"{$column_name}\"");
				}
				if (($multiple && ! $this->hasForeignDataStructureList($column_name)) || ! $this->hasForeignDataStructure($column_name)) {
					continue;
				}
				if ($multiple) {
					$structs = $this->getForeignDataStructureList($column_name);
				} else {
					$struct = $this->getForeignDataStructure($column_name);
					if (! $struct->getUpdateFlag()) {
						continue;
					}
					$structs = [
						$struct
					];
				}
				foreach ($structs as $struct) {
					if (! $struct->getUpdateFlag()) {
						if ($print) {
							Debug::print("{$f} struct at column \"{$column_name}\" does not have its update flag set");
						}
						continue;
					}elseif($print) {
						Debug::print("{$f} about to update subordinate data structure at column \"{$column_name}\"");
					}
					$status = $struct->update($mysqli);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} updating data structure at column \"{$column_name}\" returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}elseif($print) {
						Debug::print("{$f} successfully updated subordinate data structure at column \"{$column_name}\"");
					}
				}
			}
			$status = $this->afterUpdateForeignDataStructuresHook($mysqli, $when);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} afterUpdateForeignDataStructuresHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print) {
				Debug::print("{$f} afterUpdateForeignDataStructureHook returned success");
			}

			if ($print) {
				Debug::print("{$f} returning normally");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function afterUpdateForeignDataStructuresHook(mysqli $mysqli, string $when): int{
		$this->dispatchEvent(new AfterUpdateForeignDataStructuresEvent($when));
		return SUCCESS;
	}

	public function getUpdateDatabaseName(): string{
		return $this->getDatabaseName();
	}

	public function getUpdateStatement($write_indices):UpdateStatement{
		return QueryBuilder::update($this->getUpdateDatabaseName(), $this->getUpdateViewName())->set($write_indices)->where(new WhereCondition($this->getIdentifierName(), OPERATOR_EQUALS));
	}

	public function getPreUpdateForeignDataStructuresFlag():bool{
		return $this->getFlag(DIRECTIVE_PREUPDATE_FOREIGN);
	}

	public function setPreUpdateForeignDataStructuresFlag(bool $value=true):bool{
		return $this->setFlag(DIRECTIVE_PREUPDATE_FOREIGN, $value);
	}

	public function getPostUpdateForeignDataStructuresFlag():bool{
		return $this->getFlag(DIRECTIVE_POSTUPDATE_FOREIGN);
	}

	public function setPostUpdateForeignDataStructuresFlag(bool $value=true):bool{
		return $this->setFlag(DIRECTIVE_POSTUPDATE_FOREIGN, $value);
	}

	protected function beforeUpdateHook(mysqli $mysqli): int{
		$f = __METHOD__;
		$print = false;
		$this->dispatchEvent(new BeforeUpdateEvent());
		$status = $this->generateUndefinedForeignKeys();
		if ($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::error("{$f} generate undefined foreign keys returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}elseif($print) {
			Debug::print("{$f} generateUndefinedForeignKeys returned successfully");
		}
		$status = $this->loadForeignDataStructures($mysqli);
		if ($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} loadUpdatedForeignDataStructures returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}elseif($print) {
			Debug::print("{$f} loadForeignDataStructures returned successfully");
		}
		$status = $this->beforeSaveHook($mysqli, DIRECTIVE_UPDATE);
		if ($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} beforeSaveHook returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}elseif($print) {
			Debug::print("{$f} beforeSaveHook returned successfully");
		}
		return SUCCESS;
	}

	public function hasIdentifierValue(): bool{
		return $this->getKeyGenerationMode() === KEY_GENERATION_MODE_LITERAL || $this->hasColumnValue($this->getIdentifierName());
	}

	public static function getColumnStatic(string $column_name): Datum{
		$f = __METHOD__;
		if (is_abstract(static::class)) {
			Debug::error("{$f} cannot instantiate abstract class");
		}
		$dummy = new static();
		$column = $dummy->getColumn($column_name);
		unset($dummy);
		return $column;
	}

	public function ejectIdentifierValue(){
		return $this->ejectColumnValue($this->getIdentifierName());
	}

	private final function postUpdateForeignDataStructures(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$print = $this->getDebugFlag();
			// insert foreign data structures to which this object does not have a constrained reference, or which have a constrained reference to this object
			if ($this->getPostInsertForeignDataStructuresFlag()) {
				if ($print) {
					Debug::print("{$f} post-insert foreign data structure flag is set");
				}
				$status = $this->insertForeignDataStructures($mysqli, CONST_AFTER);
				$this->setPostInsertForeignDataStructuresFlag(false);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} post-inserting foreign data structures returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print) {
					Debug::print("{$f} successfully post-inserted subordinate data structure(s)");
				}
			}elseif($print) {
				Debug::print("{$f} post-insert foreign data structures flag is not set");
			}
			// like the above, except update
			if ($this->getPostUpdateForeignDataStructuresFlag()) {
				if ($print) {
					Debug::print("{$f} post-update foreign data structures flag is set -- about to see if any of them need to be updated");
				}
				$status = $this->updateForeignDataStructures($mysqli, CONST_AFTER);
				$this->setPostUpdateForeignDataStructuresFlag(false);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} post-updateForeignDataStructures returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print) {
					Debug::print("{$f} successfully post-updated foreign data structures");
				}
			}elseif($print) {
				Debug::print("{$f} post-update foreign data structures flag is not set");
			}
			// delete foreign data structures
			if ($this->getDeleteForeignDataStructuresFlag()) {
				$status = $this->deleteForeignDataStructures($mysqli);
				$this->setDeleteForeignDataStructuresFlag(false);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::print("{$f} deleteForeignDataStructures returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}
			}elseif($print) {
				Debug::print("{$f} deleteForeignDataStructures flag is not set");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	private final function updateForeignColumns(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$print = false;
			$embedded = $this->getEmbeddedDataStructures();
			if (! empty($embedded)) {
				foreach ($embedded as $groupname => $e) {
					if (! $e->getUpdateFlag()) {
						if ($print) {
							Debug::print("{$f} embedded data structure \"{$groupname}\" does not need an update");
						}
						continue;
					}
					$status = $e->update($mysqli);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} updating embedded data structure \"{$groupname}\" returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}elseif($print) {
						Debug::print("{$f} updated embedded data structure \"{$groupname}\"");
					}
				}
			}elseif($print) {
				Debug::print("{$f} no embedded data structures");
			}
			// update polymorphic keys stored in intersection tables
			$polys = $this->getFilteredColumns(COLUMN_FILTER_INTERSECTION, COLUMN_FILTER_UPDATE);
			if (! empty($polys)) {
				if ($print) {
					Debug::print("{$f} about to update the following foreing columns stored in intersection tables:");
					Debug::printArray(array_keys($polys));
				}
				foreach ($polys as $vn => $poly) {
					if ($print) {
						Debug::print("{$f} about to call updateIntersectionTables on column \"{$vn}\"");
					}
					$status = $poly->updateIntersectionTables($mysqli);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} updating intersection table for datum \"{$vn}\" returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}elseif($print) {
						Debug::print("{$f} successfully updated intersection tables on column \"{$vn}\"");
					}
				}
			}elseif($print) {
				Debug::print("{$f} no polymorphic foreign keys to update");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	private final function preUpdateForeignDataStructures(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$print = false;
			// insert foreign data structures to which this object has a constrained reference
			if ($this->getPreInsertForeignDataStructuresFlag()) {
				if ($print) {
					Debug::print("{$f} preinsert subordinate data structure flag is set");
				}
				$status = $this->insertForeignDataStructures($mysqli, CONST_BEFORE);
				$this->setPreInsertForeignDataStructuresFlag(false);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} preinserting foreign data structure returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print) {
					Debug::print("{$f} successfully preinserted foreign data structure(s)");
				}
			}elseif($print) {
				Debug::print("{$f} preinsert foreign data structures flag is not set");
			}
			// update foreign data structures to which this object has a constrained reference
			if ($this->getPreUpdateForeignDataStructuresFlag()) {
				if ($print) {
					Debug::print("{$f} preupdate foreign data structures flag is set -- about to see if any of them need to be updated");
				}
				$status = $this->updateForeignDataStructures($mysqli, CONST_BEFORE);
				$this->setPreUpdateForeignDataStructuresFlag(false);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} preupdateForeignDataStructures returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}elseif($print) {
					Debug::print("{$f} successfully preupdated foreign data structures");
				}
			}elseif($print) {
				Debug::print("{$f} preupdate foreign data structures flag is not set");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * update this object's row in the database
	 *
	 * @param mysqli $mysqli
	 * @return int
	 */
	public final function update(mysqli $mysqli): int{
		$f = __METHOD__;
		try {
			$print = false;
			if ($mysqli == null) {
				Debug::error("{$f} mysqli is null");
			}
			// check user has permission to update this object
			$status = $this->permit(user(), DIRECTIVE_UPDATE);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} update permission for class ".$this->getShortClass()." returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
			$this->log(DIRECTIVE_UPDATE);
			// start database transaction
			$transactionId = null;
			if (! db()->hasPendingTransactionId()) {
				$transactionId = sha1(random_bytes(32));
				db()->beginTransaction($mysqli, $transactionId);
			}
			// pre-update hook
			$status = $this->beforeUpdateHook($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} before update hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print) {
				Debug::print("{$f} beforeUpdateHook returned successfully");
			}
			// insert/update foreign data structures that must be dealt with before this object's column is updated
			if ($this->getPreInsertForeignDataStructuresFlag() || $this->getPreUpdateForeignDataStructuresFlag()) {
				$status = $this->preUpdateForeignDataStructures($mysqli);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} preUpdateForeignDataStructures returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}
			}elseif($print) {
				Debug::print("{$f} preinsert and preupdate foreign data structures flags are both not set");
			}
			// list the datums to update
			$write_indices = [];
			$typedef = "";
			$params = [];
			$cache_columns = $this->getFilteredColumns(COLUMN_FILTER_UPDATE, '!'.COLUMN_FILTER_VOLATILE);
			if ($this instanceof EmbeddedData) {
				$columns = $cache_columns;
			} else {
				$columns = $this->getFilteredColumns(COLUMN_FILTER_DATABASE, COLUMN_FILTER_UPDATE);
			}
			foreach ($columns as $vn => $column) {
				if ($column->getUpdateFlag()) {
					if ($print) {
						Debug::print("{$f} datum \"{$vn}\" has its update flag set");
					}
					$column->setUpdateFlag(false);
					$write_indices[$vn] = new QuestionMark();
					$typedef .= $column->getTypeSpecifier();
					array_push($params, $column->getDatabaseEncodedValue());
				}
			}
			// write datums that are flagged for update
			if (! empty($write_indices)) {
				if ($this->hasConcreteColumn("updatedTimestamp") && ! array_key_exists("updatedTimestamp", $write_indices)) {
					if ($print) {
						Debug::print("{$f} updating timestamp");
					}
					$now = $this->setUpdatedTimestamp(time());
					$write_indices["updatedTimestamp"] = new QuestionMark();
					$typedef .= "i";
					array_push($params, $now);
				}elseif($print) {
					Debug::print("{$f} timestamp does not exist or is already getting updated");
				}
				$identifier = $this->getIdentifierName();
				if ($print) {
					Debug::print("{$f} about to update the following indices:");
					Debug::printArray($write_indices);
					Debug::print("{$f} ... with the following values:");
					Debug::printArray($params);
				}
				$update = $this->getUpdateStatement($write_indices);
				$typedef .= $this->getColumn($identifier)->getTypeSpecifier();
				if ($print) {
					Debug::print("{$f} type specifier is \"{$typedef}\"");
				}
				array_push($params, $this->getColumn($identifier)->getDatabaseEncodedValue());
				if ($print) {
					Debug::print("{$f} about to execute the following update statement: \"{$update}\"");
				}
				$status = $update->prepareBindExecuteGetStatus($mysqli, $typedef, ...$params);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} update query returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}
			}elseif($print) {
				Debug::print("{$f} write indices array is empty");
			}
			$this->setUpdateFlag(false);
			// insert, update or delete foreign data structures that must be dealt with after this object is updated
			if (
				$this->getPostInsertForeignDataStructuresFlag() 
				|| $this->getPostUpdateForeignDataStructuresFlag() 
				|| $this->getDeleteForeignDataStructuresFlag()
			) {
				$print = false;
				if ($print) {
					if ($this->getPostInsertForeignDataStructuresFlag()) {
						Debug::print("{$f} post insert flag is set");
					}
					if ($this->getPostUpdateForeignDataStructuresFlag()) {
						Debug::print("{$f} post update flag is set");
					}
					if ($this->getDeleteForeignDataStructuresFlag()) {
						Debug::print("{$f} delete flag is set");
					}
				}
				$status = $this->postUpdateForeignDataStructures($mysqli);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} postUpdateForeignDataStructures returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}
			}
			// update embedded and polymorphic columns
			if (! $this->getFlag("inserting")) {
				if ($print) {
					Debug::print("{$f} this object is NOT in the middle of getting inserted");
				}
				$status = $this->updateForeignColumns($mysqli);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} updateForeignColumns returned error status \"{$err}\"");
					return $this->setObjectStatus();
				}
			}elseif($print) {
				Debug::print("{$f} this object is in the middle of getting inserted; will allow insertIntersectionData to handle polymorphic foreign keys");
			}
			// update cache but don't touch the TTL
			if (CACHE_ENABLED && $this->isRegistrable()) {
				$key = $this->getIdentifierValue();
				if (cache()->hasAPCu($key)) {
					$hit = cache()->getAPCu($key);
					foreach ($cache_columns as $column_name => $column) {
						$hit[$column_name] = $column->getDatabaseEncodedValue();
					}
					cache()->setAPCu($key, $hit);
				}elseif($print) {
					Debug::print("{$f} cache miss");
				}
			}elseif($print) {
				Debug::print("{$f} non-registrable");
			}
			// post-update hook
			$status = $this->afterUpdateHook($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} agter update hook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($transactionId !== null) {
				db()->commitTransaction($mysqli, $transactionId);
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function afterUpdateHook(mysqli $mysqli): int{
		$f = __METHOD__;
		$status = $this->afterSaveHook($mysqli, DIRECTIVE_UPDATE);
		if ($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} afterSaveHook returned error status \"{$err}\"");
			return $this->setObjectStatus($status);
		}
		$this->dispatchEvent(new AfterUpdateEvent());
		return SUCCESS;
	}

	/**
	 * copy values from another object to this one
	 *
	 * @param DataStructure $that
	 * @return int
	 */
	public function copy(DataStructure $that): int{
		$f = __METHOD__;
		try {
			$print = false;
			foreach ($that->getColumns() as $column_name => $column) {
				$this->getColumn($column_name)->copy($column);
			}
			$column_names = $this->getFilteredColumnNames(COLUMN_FILTER_FOREIGN);
			foreach ($column_names as $column_name) {
				if ($that->hasForeignDataStructure($column_name)) {
					if ($this->hasColumn($column_name)) {
						$column = $this->getColumn($column_name);
					} else {
						$column = null;
					}
					if (! $this->hasColumn($column_name) || $column instanceof ForeignKeyDatum) {
						$this->setForeignDataStructure($column_name, $that->getForeignDataStructure($column_name));
					}elseif($column instanceof KeyListDatum) {
						if ($that->hasForeignDataStructureList($column_name)) {
							$structs = $that->getForeignDataStructureList($column_name);
							foreach ($structs as $struct) {
								$this->setForeignDataStructureListMember($column_name, $struct);
							}
						} else {
							if ($print) {
								Debug::print("{$f} no foreign data structure list \"{$column_name}\"");
							}
							continue;
						}
					} else {
						$dc = $column->getClass();
						Debug::error("{$f} datum is an instance of \"{$dc}\"");
					}
				}
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getForeignDataStructureListCommand(string $phylum): GetForeignDataStructureListCommand{
		return new GetForeignDataStructureListCommand($this, $phylum);
	}

	/**
	 * create a replica of this object without accessing the database
	 *
	 * @return NULL|DataStructure
	 */
	public final function replicate(): ?DataStructure{
		$f = __METHOD__;
		try {
			$print = false;
			$status = $this->beforeReplicateHook();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} before replica hook returned error status \"{$err}\"");
				$this->setObjectStatus($status);
				return null;
			}
			$mode = $this->hasAllocationMode() ? $this->getAllocationMode() : ALLOCATION_MODE_EAGER;
			if ($print) {
				Debug::print("{$f} allocation mode is \"{$mode}\"");
			}
			$replica = new static($mode);
			$replica->setReplicaFlag(true);
			$replica->setReceptivity(DATA_MODE_PASSIVE);
			if ($print) {
				$count = $replica->getColumnCount();
				if ($count === 0) {
					$count2 = $this->getColumnCount();
					if ($count2 === 0) {
						Debug::error("{$f} column count is zero for both original and replica");
					}
					Debug::error("{$f} replica column count is 0, but this object has {$count2} columns");
				}elseif($print) {
					Debug::print("{$f} replica column count is {$count}");
				}
			}
			$replica->copy($this);
			$replica->setReceptivity(DATA_MODE_DEFAULT);
			$status = $this->afterReplicateHook($replica);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} after replica hook returned error status \"{$err}\"");
				$this->setObjectStatus($status);
				return null;
			}
			return $replica;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function whereIntersectionalHostKey($foreignClass, string $relationship, string $operator = OPERATOR_EQUALS): WhereCondition{
		return static::generateIntersectionalWhereCondition($foreignClass, "hostKey", $relationship, $operator);
	}

	public static function whereIntersectionalForeignKey($foreignClass, string $relationship, string $operator = OPERATOR_EQUALS): WhereCondition{
		return static::generateIntersectionalWhereCondition($foreignClass, "foreignKey", $relationship, $operator);
	}

	/**
	 * generates a WhereCondition for locating something in an intersections table that references an object of this class
	 *
	 * @param string|DataStructure $foreignClass
	 *        	: foreign class used to generate IntersectionData
	 * @param string $relationship
	 *        	: name of foreign key -- needed because of embedded columns
	 * @param string $operator
	 *        	: operator used to build WhereConditions
	 * @return WhereCondition
	 */
	private static function generateIntersectionalWhereCondition($foreignClass, string $select_expr, string $relationship, string $operator = OPERATOR_EQUALS): WhereCondition{
		$f = __METHOD__;
		$print = false;
		if (is_object($foreignClass)) {
			$foreignClass = $foreignClass->getClass();
		}
		$idn = static::getIdentifierNameStatic();
		if ($print) {
			$dsc = static::class;
			Debug::print("{$f} about to create new IntersectionData({$dsc}, {$foreignClass}, {$relationship})");
		}
		$intersection = new IntersectionData(static::class, $foreignClass, $relationship);
		switch ($select_expr) {
			case "hostKey":
				$column_name = "foreignKey";
				break;
			case "foreignKey":
				$column_name = "hostKey";
				break;
			default:
				Debug::error("{$f} shite");
		}
		$select = new SelectStatement($select_expr);
		$select->from($intersection->getDatabaseName(), $intersection->getTableName())->where(new AndCommand(new WhereCondition($column_name, $operator, 's'), new WhereCondition("relationship", $operator, 's')));
		$ret = new WhereCondition($idn, OPERATOR_IN, static::getTypeSpecifierStatic($idn), $select);
		if ($print) {
			$ret->setParameterCount(1);
			Debug::print("{$f} returning \"{$ret}\"");
			$ret->setParameterCount(null);
		}
		return $ret;
	}

	/**
	 * generates a select statement useful for selecting keys from an intersection table
	 *
	 * @param string $foreignClass
	 *        	: foreign class with which this class has an intersection table
	 * @param string $foreignKeyName
	 *        	: name of the foreign key stored in that intersection table. Provide it if you're selecting the foreign data structure, leave it blank if you're selecting the current class.
	 * @param SelectStatement $subquery
	 *        	: optional subquery for specifying key selection criteria. Leave it blank and it will match the an alias for this class's identifying column
	 * @return SelectStatement
	 */
	// XXX TODO this is confusing because it does different things based off whether the second parameter is present; break it into 2 functions, one for selecting host keys and the other for foreign keys. Thse third parameter is also rather confusing
	public static function generateLazyAliasExpression(string $foreignClass, ?string $foreignKeyName = null, ?SelectStatement $subquery = null): SelectStatement{
		$f = __METHOD__;
		try {
			$print = false;
			$idn = $foreignClass::getIdentifierNameStatic();
			if ($foreignKeyName !== null) {
				$fkn = $foreignKeyName;
			} else {
				$fkn = $idn;
			}
			$intersection = new IntersectionData(static::class, $foreignClass, $fkn);
			$table = $intersection->getTableName();
			$alias = "{$table}_alias";
			if ($foreignKeyName !== null) {
				$subq_expr = new ColumnAlias(new ColumnAliasExpression($alias, "foreignKey"), $foreignKeyName);
				$get_key = new ColumnAliasExpression($alias, "hostKey");
			} else {
				$subq_expr = new ColumnAlias(new ColumnAliasExpression($alias, "hostKey"), $idn);
				$get_key = new ColumnAliasExpression($alias, "foreignKey");
			}
			$foreignKey = new ForeignKeyDatum($fkn);
			$foreignKey->setSubqueryTableAlias($alias);
			$foreignKey->setSubqueryDatabaseName($intersection->getDatabaseName());
			$foreignKey->setSubqueryTableName($table);
			$foreignKey->setSubqueryExpression($subq_expr);
			if ($subquery === null) {
				$where1 = CommandBuilder::equals($get_key, new GetDeclaredVariableCommand("t0." . back_quote($idn)));
			} else {
				$where1 = new WhereCondition($get_key, OPERATOR_EQUALS, null, $subquery);
			}
			$where2 = new WhereCondition((new GetDeclaredVariableCommand("{$alias}.relationship"))->withTypeSpecifier("s"), OPERATOR_EQUALS, 's');
			$foreignKey->setSubqueryWhereCondition(CommandBuilder::and($where1, $where2));
			$select = $foreignKey->getAliasExpression()->escape(ESCAPE_TYPE_PARENTHESIS);
			if ($print) {
				Debug::print("{$f} returning \"{$select}\"");
			}
			return $select;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * returns distance from this object along a chain of objects linked by foreign key reference in column $column_name until key $value is reached;
	 * negative values are returned if there is no association
	 *
	 * @param string $column_name
	 * @param mixed $value
	 * @return int
	 */
	public function getAssociationDistance(string $column_name, $value): int{
		$f = __METHOD__;
		try {
			$print = false;
			if ($this->getIdentifierValue() === $value) {
				return 0;
			}elseif(! $this->hasColumn($column_name) || ! $this->hasColumnValue($column_name)) {
				if ($print) {
					Debug::print("{$f} column \"{$column_name}\" does not exist, or it has a different value");
				}
				return - 1;
			}
			$column = $this->getColumn($column_name);
			if (! $column->applyFilter(COLUMN_FILTER_FOREIGN)) {
				Debug::error("{$f} column \"{$column_name}\" is not a foreign key");
			}elseif($value === $column->getValue()) {
				return 1;
			}
			$fds = $this->getForeignDataStructure($column_name);
			$distance = $fds->getAssociationDistance($column_name, $value);
			if ($distance < 0) {
				return $distance;
			}
			return $distance + 1;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	private function log(string $directive): int{
		if ($this->getFlag("disableLog")) {
			return 0;
		}
		$class = static::getShortClass();
		if ($this instanceof IntersectionData) {
			if ($this->hasHostDataStructureClass()) {
				$class .= " between " . $this->getHostDataStructureClass()::getShortClass();
				if ($this->hasHostKey()) {
					$class .= " (with host key \"" . $this->getHostKey() . "\")";
				}
				if ($this->hasForeignDataStructureClass()) {
					$class .= " and " . $this->getForeignDataStructureClass()::getShortClass();
					if ($this->hasForeignKey()) {
						$class .= " (with foreign key \"" . $this->getForeignKey() . "\")";
					}
				}
			}
		}
		$idn = $this->getIdentifierName();
		$key = $idn === null ? "[unidentifiable]" : $this->getIdentifierValue();
		$did = $this->getDebugId();
		$decl = $this->getDeclarationLine();
		return debug()->log("{$directive} {$class} with key {$key} (debug ID {$did}, declared {$decl})");
	}

	/**
	 *
	 * @param mysqli $mysqli
	 * @param string|string[] ...$column_names
	 * @return int
	 */
	public static function migrateMonomorphicToPoly(mysqli $mysqli, ...$column_names): int{
		$f = __METHOD__;
		try {
			$print = false;
			if (empty($column_names)) {
				Debug::error("{$f} column names are empty");
				return FAILURE;
			}
			$that = new static();

			foreach ($column_names as $column_name) {
				$column = $that->getColumn($column_name);
				if (! $column instanceof ForeignKeyDatum) {
					Debug::error("{$f} column \'{$column_name}\" is not a ForeignKeyDatum");
					continue;
				}elseif(! $column->applyFilter(COLUMN_FILTER_INTERSECTION)) {
					Debug::error("{$f} column \"{$column_name}\" is not set up to be polymorphic, make it so before calling this function on it");
				}
				// 1. create intersection tables if they don't already exist
				$status = $column->createIntersectionTables($mysqli);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} createIntersectionTables for column \"{$column_name}\" returned error status \"{$err}\"");
					return $status;
				}
			}
			// 2. load all instances of this class, update all keys
			$select = $that->select();
			$select->pushExpressions(...$column_names);
			$result = $select->executeGetResult($mysqli);
			$results = $result->fetch_all(MYSQLI_ASSOC);
			$result->free_result();
			foreach ($results as $result) {
				$obj = new static();
				$obj->getColumn($column_name)->setRetainOriginalValueFlag(false);
				$status = $obj->processQueryResultArray($mysqli, $result);
				$key = $obj->getIdentifierValue();
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} processQueryResultArray for object with identifier \"{$key}\" returned error status \"{$err}\"");
				}
				// 3. set foreign data structures for each column
				foreach ($column_names as $column_name) {
					if (! $obj->hasColumnValue($column_name)) {
						Debug::error("{$f} object with identifier \"{$key}\" does not have a value for column \"{$column_name}\", cannot migrate");
						continue;
					}
					$fk = $obj->getColumnValue($column_name);
					if (! registry()->has($fk)) {
						Debug::warning("{$f} registry does not have n object with identifier \"{$fk}\"");
						continue;
					}
					$struct = registry()->get($fk);
					$obj->setForeignDataStructure($column_name, $struct);
					$column = $obj->getColumn($column_name);
					if (! $column->hasValue()) {
						Debug::error("{$f} after setting foreign data structure, column \"{$column_name}\" lacks an actual value");
					}
					$intersection = $column->generateIntersectionData();
					if (! $intersection->hasForeignKey()) {
						Debug::error("{$f} intersection data lacks a foreign key");
					}elseif(! $intersection->hasHostKey()) {
						Debug::error("{$f} intersection data lacks a host key");
					}elseif(! $intersection->hasRelationship()) {
						Debug::error("{$f} intersection data lacks a relationship");
					}
					// see if the intersection data already exists
					$select = $intersection->select()->where(
						CommandBuilder::and(
							new WhereCondition("hostKey", OPERATOR_EQUALS), 
							new WhereCondition("relationship", OPERATOR_EQUALS)
						)
					)->withTypeSpecifier('ss')->withParameters([
						$key,
						$column_name
					]);
					$count = $select->executeGetResultCount($mysqli);
					if ($count === 0) {
						if ($print) {
							Debug::print("{$f} marking object with key \"{$key}\" foreign column \"{$column_name}\" for update");
						}
						$column->setUpdateFlag(true);
						$obj->setUpdateFlag(true);
					}elseif($count === 1) {
						if ($print) {
							Debug::print("{$f} intersection data already exists for object with key \"{$key}\" foreign column \"{$column_name}\"");
						}
						continue;
					} else {
						Debug::error("{$f} illegal intersection data count {$count} for object with key \"{$key}\" foreign column \"{$column_name}\"");
					}
				}
				// 4. update the object
				if (! $obj->getUpdateFlag()) {
					if ($print) {
						Debug::print("{$f} object with ID \"{$key}\" is not flagged for update");
					}
					continue;
				}elseif($print) {
					Debug::print("{$f} about to update object with key \"{$key}\"");
				}
				$status = $obj->update($mysqli);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} updating object with ID \"{$key}\" returned error status \"{$err}\"");
					return $status;
				}elseif($print) {
					Debug::print("{$f} successfully updated object with ID \"{$key}\"");
				}
			}
			if ($print) {
				Debug::print("{$f} migration successful");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setInsertingFlag(bool $value = true): bool{
		return $this->setFlag("inserting", $value);
	}

	public function getInsertingFlag(): bool{
		return $this->getFlag("inserting");
	}

	public function getForeignDataStructureCountCommand(string $column_name): GetForeignDataStructureCountCommand{
		return new GetForeignDataStructureCountCommand($this, $column_name);
	}

	public function unshiftForeignDataStructureListMember(string $column_name, DataStructure $struct){
		$f = __METHOD__;
		$print = false;
		if (! $this->hasForeignDataStructureList($column_name)) {
			if ($print) {
				Debug::print("{$f} no foreign data struture list for column \"{$column_name}\"");
			}
			$this->setForeignDataStructureListMember($column_name, $struct);
			return SUCCESS;
		}elseif($print) {
			Debug::print("{$f} about to unshift a foreign data structure to the beginning of list \"{$column_name}\"");
		}
		$backup = $this->getForeignDataStructureList($column_name);
		$temp = [
			$struct->getIdentifierValue() => $struct
		];
		$this->setForeignDataStructureList($column_name, array_merge($temp, $backup));
		unset($backup);
		return SUCCESS;
	}

	public function setExpandedFlag(bool $value = true): bool{
		return $this->setFlag('expanded', $value);
	}

	public function getExpandedFlag(): bool{
		return $this->getFlag('expanded');
	}

	public function getFirstRelationship(string $tree_name): ?DataStructure{
		return $this->getForeignDataStructureListMemberAtOffset($tree_name, 0);
	}

	public function getDescendants(string $phylum): array{
		$f = __METHOD__;
		try {
			$print = false;
			if (! $this->hasForeignDataStructureList($phylum)) {
				if ($print) {
					Debug::print("{$f} child count is 0");
				}
				return [];
			}elseif($print) {
				Debug::print("{$f} entered; about to get child array \"{$phylum}\"");
			}
			$children = $this->getForeignDataStructureList($phylum);
			$descendants = [];
			foreach ($children as $child) {
				$descendants[$child->getIdentifierValue()] = $child;
				if (! $child->hasForeignDataStructureList($phylum)) {
					if ($print) {
						Debug::print("{$f} child does not have any children in phylum \"{$phylum}\"");
					}
					continue;
				}
				if ($print) {
					$key = $child->getIdentifierValue();
					Debug::print("{$f} calling this recursively on a child with key \"{$key}\" node for phylum \"{$phylum}\"");
				}
				$grandchildren = $child->getDescendants($phylum);
				$descendants = array_merge($descendants, $grandchildren);
			}
			return $descendants;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function getJavaScriptClassPath(): ?string{
		$fn = get_class_filename(DataStructure::class);
		return substr($fn, 0, strlen($fn) - 3) . "js";
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->elementClass);
		unset($this->eventListeners);
		unset($this->databaseName);
		unset($this->tableName);
		unset($this->foreignDataStructures);
		unset($this->oldDataStructures);
		unset($this->iterator);
		unset($this->receptivity);
	}
}