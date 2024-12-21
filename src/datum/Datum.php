<?php

namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\close_enough;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use function JulianSeymour\PHPWebApplicationFramework\set_secure_cookie;
use function JulianSeymour\PHPWebApplicationFramework\starts_with;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\PermissiveInterface;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\PermissiveTrait;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayKeyProviderInterface;
use JulianSeymour\PHPWebApplicationFramework\common\DisabledFlagTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ElementBindableTrait;
use JulianSeymour\PHPWebApplicationFramework\common\HitPointsInterface;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\UpdateFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ValuedTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\CipherDatum;
use JulianSeymour\PHPWebApplicationFramework\crypt\schemes\SharedEncryptionSchemeInterface;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructuralTrait;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructureClassTrait;
use JulianSeymour\PHPWebApplicationFramework\data\EmbeddedData;
use JulianSeymour\PHPWebApplicationFramework\data\EventSourceData;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatumInterface;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\KeyListDatum;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\AfterEjectValueEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterSetValueEvent;
use JulianSeymour\PHPWebApplicationFramework\event\AfterUnsetValueEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeEjectValueEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeSetValueEvent;
use JulianSeymour\PHPWebApplicationFramework\event\BeforeUnsetValueEvent;
use JulianSeymour\PHPWebApplicationFramework\event\ReleaseParentNodeEvent;
use JulianSeymour\PHPWebApplicationFramework\input\InputElement;
use JulianSeymour\PHPWebApplicationFramework\json\JsonDatum;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\Constraint;
use JulianSeymour\PHPWebApplicationFramework\validate\DatumValidator;
use Closure;
use Exception;
use mysqli;

/**
 * a single variable that is stored under a column in the database, or in a single index of a superglobal array
 *
 * @author j
 */
abstract class Datum extends AbstractDatum 
implements ArrayKeyProviderInterface, ColumnDefinitionInterface, PermissiveInterface, ReplicableInterface, SQLInterface, StaticPropertyTypeInterface{

	use ColumnAliasableTrait;
	use ColumnDefinitionTrait;
	use DataStructuralTrait;
	use DataStructureClassTrait;
	use DisabledFlagTrait;
	use ElementBindableTrait;
	use PermissiveTrait;
	use StaticPropertyTypeTrait;
	use TranscryptionKeyNamesTrait;
	use UpdateFlagBearingTrait;
	use ValuedTrait;

	/**
	 * Attempting to pass this value to setValue will trigger the apoptose() function on the
	 * DataStructure that contains this Datum
	 *
	 * @var mixed
	 */
	protected $apoptoticSignal;

	/**
	 * fallback for loose datums declared without a data structure
	 *
	 * @var string
	 */
	protected $dataStructureClass;

	/**
	 * closure for generating initial value prior to insertion
	 *
	 * @var Closure
	 */
	protected $generationClosure;

	/**
	 * indices that mirror the value of this one
	 *
	 * @var array
	 */
	private $mirrorIndices;

	/**
	 * A copy of the original value as loaded from the database.
	 * Not retained by default because it is rarely necessary.
	 *
	 * @var mixed
	 */
	protected $originalValue;

	/**
	 * similar to the generationClosure, but for columns with special logic for generating a new, non-initial value, i.e.
	 * when changing password
	 *
	 * @var Closure
	 */
	protected $regenerationClosure;

	public abstract function parseValueFromSuperglobalArray($value);

	public abstract function parseValueFromQueryResult($raw);

	public abstract function getUrlEncodedValue();

	public abstract function getHumanReadableValue();

	public abstract function getHumanWritableValue();

	protected abstract function getConstructorParams(): ?array;

	public function __construct(string $name=null){
		$f = __METHOD__;
		parent::__construct();
		if($name !== null){
			$this->setName($name);
		}
		$this->setRewritableFlag(true);
		$this->setTrimmableFlag(true);
	}

	public static function declareFlags():?array{
		Debug::checkMemoryUsage("gothca", 112000000);
		return array_merge(parent::declareFlags(), [
			"adminInterface", // true => automatically generate an input for this datum in a DefaultForm
			COLUMN_FILTER_ALWAYS_VALID,
			COLUMN_FILTER_APOPTOTIC, // true => apoptotic signal has been defined, possibly as null
			COLUMN_FILTER_ARRAY_MEMBER, // true => datum's value will be in the array generated by its DataStructure->toArray()
			"deallocating",
			COLUMN_FILTER_DECLARED, // true => this datum was declared by the containing structure
			COLUMN_FILTER_DISABLED,
			COLUMN_FILTER_INDEX, // true => this column is an index
			"ignoreInequivalence", // true => this columns is ignored in DataStructure::equals()
			"neverLeaveServer", // true => value should never leave the database server
			"paginator", // true => column is part of a paginator's GET parameters
			COLUMN_FILTER_PRIMARY_KEY, // true => column has a primary key constraint
			'processValuelessInput', // true => processInput will get called in DataStructure->processForm even if the input does not have a value attribute
			COLUMN_FILTER_DIRTY_CACHE, // datum has been flagged as temporarily dirty and you need to update the cache
			COLUMN_FILTER_REPLICA, // true => this datum is a replica
			COLUMN_FILTER_RETAIN_ORIGINAL_VALUE, // true => keep a backup copy of the original value in memory
			COLUMN_FILTER_REWRITABLE, // true => allow this datum to change value once it has been assigned
			COLUMN_FILTER_SEALED,
			COLUMN_FILTER_SEARCHABLE, // true => a checkbox is generated to search for this datum in SearchForm
			COLUMN_FILTER_SENSITIVE, // true => don't print the contents of this datum to human readable inputs
			COLUMN_FILTER_SORTABLE, // true => this datum can be used as an OrderBy term
			COLUMN_FILTER_TRIMMABLE,
			COLUMN_FILTER_UNIQUE, // true => this datum has a unique constraint
			DIRECTIVE_UPDATE, // true => this datum has been updated and the new value is ready to write
			"userWritable" // true => column is part of the user editable view created for the table
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"adminInterface",
			COLUMN_FILTER_ALWAYS_VALID,
			COLUMN_FILTER_APOPTOTIC,
			COLUMN_FILTER_INDEX,
			"ignoreInequivalence",
			"neverLeaveServer",
			COLUMN_FILTER_PRIMARY_KEY,
			'processValuelessInput',
			COLUMN_FILTER_RETAIN_ORIGINAL_VALUE,
			COLUMN_FILTER_REWRITABLE,
			COLUMN_FILTER_SEARCHABLE,
			COLUMN_FILTER_SENSITIVE,
			COLUMN_FILTER_SORTABLE,
			COLUMN_FILTER_TRIMMABLE,
			COLUMN_FILTER_UNIQUE,
			DIRECTIVE_UPDATE, //needed for embedded columns to update properly
			"userWritable"
		]);
	}
	
	public function copy($that): int{
		$f = __METHOD__;
		$print = false;
		$ret = parent::copy($that);
		if($that->hasCollationName()){
			$this->setCollationName(replicate($that->getCollationName()));
		}
		if($that->hasComment()){
			$this->setComment(replicate($that->getComment()));
		}
		if($that->hasArrayProperty("constraints")){ //do not use hasConstraints because that always returns true for ForeignKeyDatum, which will for sure cause problems
			$this->setConstraints(replicate($that->getConstraints()));
		}
		if($that->hasElementClass()){
			$this->setElementClass(replicate($that->getElementClass()));
		}
		if($that->hasIndexName()){
			$this->setIndexName(replicate($that->getIndexName()));
		}
		if($that->hasIndexType()){
			$this->setIndexType(replicate($that->getIndexType()));
		}
		$this->copyPermissions($that);
		if($that->hasEngineAttribute()){
			$this->setEngineAttribute(replicate($that->getEngineAttribute()));
		}
		if($that->hasSecondaryEngineAttribute()){
			$this->setSecondaryEngineAttribute(replicate($that->getSecondaryEngineAttribute()));
		}
		if($that instanceof VirtualDatum){
			$column_name = $that->getName();
			if($print){
				Debug::print("{$f} other datum at index \"{$column_name}\" is virtual");
			}
		}elseif($that->hasValue()){
			$value = replicate($that->getValue());
			if($print){
				Debug::print("{$f} setting value \"{$value}\"");
			}
			$this->setValue($value);
		}elseif($print){
			Debug::print("{$f} non-virtual datum does not have a value");
		}
		if($that->hasVisibility()){
			$this->setVisibility(replicate($that->getVisibility()));
		}
		if($that->hasApoptoticSignal()){
			$this->setApoptoticSignal(replicate($that->getApoptoticSignal()));
		}
		if($that->hasColumnAlias()){
			$this->setColumnAlias(replicate($that->getColumnAlias()));
		}
		if($that->hasColumnFormat()){
			$this->setColumnFormat(replicate($that->getColumnFormat()));
		}
		if($that->hasDataStructureClass()){
			$this->setDataStructureClass(replicate($that->getDataStructureClass()));
		}
		if($that->hasDatabaseStorage()){
			$this->setDatabaseStorage(replicate($that->getDatabaseStorage()));
		}
		if($that->hasDecryptionKeyName()){
			$this->setDecryptionKeyName(replicate($that->getDecryptionKeyName()));
		}
		if($that->hasGeneratedAlwaysAsExpression()){
			$this->setGeneratedAlwaysAsExpression(replicate($that->getGeneratedAlwaysAsExpression()));
		}
		if($that->hasMirrorIndices()){
			foreach($that->getMirrorIndices() as $mi){
				$this->mirrorAtIndex($mi);
			}
		}
		if($that->hasOriginalValue()){
			$this->setOriginalValue(replicate($that->getOriginalValue()));
		}
		if($that->hasPersistenceMode()){
			$this->setPersistenceMode(replicate($that->getPersistenceMode()));
		}
		if($that->hasReferenceColumn()){
			$this->setReferenceColumn($that->getReferenceColumn());
		}
		if($that->hasReferenceColumnName()){
			$this->setReferenceColumnName(replicate($that->getReferenceColumnName()));
		}
		if($that->hasAliasExpression()){
			$this->setAliasExpression(replicate($that->getAliasExpression()));
		}
		if($that->hasSubqueryClass()){
			$this->setSubqueryClass(replicate($that->getSubqueryClass()));
		}
		if($that->hasSubqueryColumnName()){
			$this->setSubqueryColumnName(replicate($that->getSubqueryColumnName()));
		}
		if($that->hasSubqueryDatabaseName()){
			$this->setSubqueryDatabaseName(replicate($that->getSubqueryDatabaseName()));
		}
		if($that->hasSubqueryTableName()){
			$this->setSubqueryTableName(replicate($that->getSubqueryTableName()));
		}
		if($that->hasSubqueryTableAlias()){
			$this->setSubqueryTableAlias(replicate($that->getSubqueryTableAlias()));
		}
		if($that->hasSubqueryExpression()){
			$this->setSubqueryExpression(replicate($that->getSubqueryExpression()));
		}
		if($that->hasSubqueryLimit()){
			$this->setSubqueryLimit(replicate($that->getSubqueryLimit()));
		}
		if($that->hasSubqueryOrderBy()){
			$this->setSubqueryOrderBy(replicate($that->getSubqueryOrderBy()));
		}
		if($that->hasSubqueryParameters()){
			$this->setSubqueryParameters(replicate($this->getSubqueryParameters()));
		}
		if($that->hasSubqueryTypeSpecifier()){
			$this->setSubqueryTypeSpecifier(replicate($that->getSubqueryTypeSpecifier()));
		}
		if($that->hasSubqueryWhereCondition()){
			$this->setSubqueryWhereCondition(replicate($that->getSubqueryWhereCondition()));
		}
		if($that->hasTranscryptionKeyName()){
			$this->setTranscryptionKeyName(replicate($that->getTranscryptionKeyName()));
		}
		return $ret;
	}
	
	public function setAlwaysValidFlag(bool $value = true): bool{
		return $this->setFlag(COLUMN_FILTER_ALWAYS_VALID, $value);
	}
	
	public function getAlwaysValidFlag(): bool{
		return $this->getFlag(COLUMN_FILTER_ALWAYS_VALID);
	}
	
	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array{
		return [
			"constraints" => Constraint::class
		];
	}

	public function setDeclaredFlag(bool $value = true): bool{
		return $this->setFlag(COLUMN_FILTER_DECLARED, $value);
	}

	public function getDeclaredFlag(): bool{
		return $this->getFlag(COLUMN_FILTER_DECLARED);
	}

	public function setUpdateFlag(bool $value = true):bool{
		if($value && $this->hasDataStructure()){
			$this->getDataStructure()->setUpdateFlag($value);
		}
		return $this->setFlag(DIRECTIVE_UPDATE, $value);
	}

	public final function getArrayKey(int $count):string{
		return $this->getName();
	}

	public function hasGenerationClosure(): bool{
		return isset($this->generationClosure);
	}

	public function setGenerationClosure(?Closure $closure): ?Closure{
		$f = __METHOD__;
		if(!$closure instanceof Closure){
			Debug::error("{$f} closure must be a closure");
		}
		if($this->hasGenerationClosure()){
			$this->release($this->generationClosure);
		}
		return $this->generationClosure = $this->claim($closure);
	}

	public function getGenerationClosure(): ?Closure{
		$f = __METHOD__;
		if(!$this->hasGenerationClosure()){
			Debug::error("{$f} generation closure is undefined");
		}
		return $this->generationClosure;
	}

	public function hasRegenerationClosure(): bool{
		return isset($this->regenerationClosure) && $this->regenerationClosure instanceof Closure;
	}

	public function setRegenerationClosure(?Closure $closure): ?Closure{
		$f = __METHOD__;
		if(!$closure instanceof Closure){
			Debug::error("{$f} closure must be a closure");
		}elseif($this->hasRegenerationClosure()){
			$this->release($this->regenerationClosure);
		}
		return $this->regenerationClosure = $closure;
	}

	public function getRegenerationClosure(): ?Closure{
		$f = __METHOD__;
		if(!$this->hasRegenerationClosure()){
			Debug::error("{$f} regeneration closure is undefined");
		}
		return $this->regenerationClosure;
	}

	/**
	 * inserts an EventSourceData tracking the status of this column
	 *
	 * @param mysqli $mysqli
	 * @param mixed $input_token
	 * @param mixed $previous_state
	 * @param mixed $next_state
	 * @return int
	 */
	public function eventSource(mysqli $mysqli, $input_token, $previous_state, $next_state): int{
		$f = __METHOD__;
		try{
			$print = false;
			$event_src = new EventSourceData($this, ALLOCATION_MODE_EAGER);
			$event_src->setUserData(user());
			if($previous_state !== null){
				$event_src->setPreviousState($previous_state);
			}
			if($input_token !== null){
				$event_src->setToken($input_token);
			}
			$event_src->setCurrentState($next_state);
			$event_src->setTargetData($this->getDataStructure());
			$status = $event_src->insert($mysqli);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} inserting event source returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($print){
				Debug::print("{$f} successfully inserted event source data");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function generate(): int{
		$f = __METHOD__;
		try{
			$print = false;
			if($this->hasGenerationClosure()){
				if($print){
					Debug::print("{$f} this object has a generation closure");
				}
				$closure = $this->getGenerationClosure();
				$value = $closure($this);
				if($print){
					Debug::print("{$f} generated initial value \"{$value}\"");
				}
				$this->setValue($value);
			}elseif($this->hasDefaultValue()){
				if($print){
					Debug::print("{$f} this column has a default value");
				}
				$value = $this->getDefaultValue();
				if($print){
					Debug::print("{$f} generated initial value \"{$value}\"");
				}
				$this->setValue($value);
			}elseif($print){
				Debug::print("{$f} this column has neither a generation closure nor a default value");
			}
			return SUCCESS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function regenerate(): int{
		$f = __METHOD__;
		$print = false;
		if($this->hasRegenerationClosure()){
			if($print){
				Debug::print("{$f} this object has a generation closure");
			}
			$closure = $this->getRegenerationClosure();
			$value = $closure($this);
			if($print){
				Debug::print("{$f} generated initial value \"{$value}\"");
			}
			$this->setValue($value);
			return SUCCESS;
		}elseif($print){
			Debug::print("{$f} regeneration closure is undefined; falling back to initial generation function");
		}
		return $this->generate();
	}

	private function setApoptoticSignalFlag(bool $value = true):bool{
		return $this->setFlag(COLUMN_FILTER_APOPTOTIC, $value);
	}

	private function getApoptoticSignalFlag():bool{
		return $this->getFlag(COLUMN_FILTER_APOPTOTIC);
	}

	public function setApoptoticSignal($value){
		if($this->hasApoptoticSignal()){
			$this->release($this->apoptoticSignal);
		}
		$this->setApoptoticSignalFlag(true);
		return $this->apoptoticSignal = $this->claim($value);
	}

	public function hasApoptoticSignal():bool{
		$f = __METHOD__;
		$cn = $this->getName();
		$print = false;
		if($print){
			if(isset($this->apoptoticSignal)){
				Debug::print("{$f} apoptotic signal is set");
			}else{
				Debug::print("{$f} apoptotic signal is NOT set");
			}
			if($this->getApoptoticSignalFlag()){
				Debug::print("{$f} apoptotic signal flag is set");
			}else{
				Debug::print("{$f} apoptotic signal flag is NOT set");
			}
		}
		return isset($this->apoptoticSignal) || $this->getApoptoticSignalFlag();
	}

	public function getApoptoticSignal(){
		$f = __METHOD__;
		if(!$this->hasApoptoticSignal()){
			Debug::error("{$f} apoptotic signal is undefined");
		}
		return $this->apoptoticSignal;
	}

	public function clearApoptoticSignal(){
		$this->release($this->apoptoticSignal);
		$this->setFlag(COLUMN_FILTER_APOPTOTIC, false);
	}

	public function setTrimmableFlag(bool $value = true): bool{
		return $this->setFlag(COLUMN_FILTER_TRIMMABLE, $value);
	}

	public function getTrimmableFlag(): bool{
		return $this->getFlag(COLUMN_FILTER_TRIMMABLE);
	}

	protected function beforeEjectValueHook():int{
		if($this->hasAnyEventListener(EVENT_BEFORE_EJECT)){
			$this->dispatchEvent(new BeforeEjectValueEvent());
		}
		return SUCCESS;
	}

	public function ejectValue(){
		$f = __METHOD__;
		$name = $this->getName();
		$print = false;
		if($this->hasValue()){
			$status = $this->beforeEjectValueHook();
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} beforeEjectValueHook for column \"{$name}\" returned error status \"{$err}\"");
				$this->setObjectStatus($status);
				return null;
			}
			$ret = $this->getValue();
			$this->unsetValue(true);
			if($this->hasValue()){
				Debug::error("{$f} immediately after unsetValue, value is still defined for column \"{$name}\"");
			}elseif($print){
				Debug::print("{$f} returning \"{$ret}\" for column \"{$name}\"");
			}
			$this->afterEjectValueHook($ret);
			return $ret;
		}elseif($print){
			Debug::print("{$f} value is already undefined for column \"{$name}\"");
		}
		return null;
	}

	protected function afterEjectValueHook($v):int{
		if($this->hasAnyEventListener(EVENT_AFTER_EJECT)){
			$this->dispatchEvent(new AfterEjectValueEvent($v));
		}
		return SUCCESS;
	}

	public function configureArrayMembership($value){
		$f = __METHOD__;
		if(!is_bool($value)){
			Debug::error("{$f} only foreign key datums can configure array membership");
		}
		return $this->setArrayMembershipFlag($value);
	}

	public function isSearchable():bool{
		return $this->getFlag(COLUMN_FILTER_SEARCHABLE);
	}

	public function setSearchable(bool $value = true):bool{
		return $this->setFlag(COLUMN_FILTER_SEARCHABLE, $value);
	}

	public function setProcessValuelessInputFlag(bool $value = true):bool{
		return $this->setFlag("processValuelessInput", $value);
	}

	public function getProcessValuelessInputFlag():bool{
		return $this->getFlag("processValuelessInput");
	}

	public function setArrayMembershipFlag(bool $value): bool{
		if($this->getNeverLeaveServer()){
			return false;
		}
		return $this->setFlag(COLUMN_FILTER_ARRAY_MEMBER, $value);
	}

	public function getArrayMembershipFlag():bool{
		if($this->getNeverLeaveServer()){ // note: sensitive data can still be converted to array because the backup server needs them
			return false;
		}
		return $this->getFlag(COLUMN_FILTER_ARRAY_MEMBER);
	}

	public function setValueFromSuperglobalArray($value){
		$parsed = $this->parseValueFromSuperglobalArray($value);
		return $this->setValue($parsed);
	}

	public function announceYourself():void{
		$f = __METHOD__;
		Debug::print("{$f} my name is \"" . $this->getName() . "\"");
	}

	public function getDataStructureKey(){
		$f = __METHOD__;
		try{
			if(!$this->hasDataStructure()){
				Debug::error("{$f} row data object is undefined");
			}
			$obj = $this->getDataStructure();
			$key = $obj->getIdentifierValue();
			// Debug::print("{$f} returning \"{$key}\"");
			return $key;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setOriginalValue($value){
		$f = __METHOD__;
		if(!$this->getRetainOriginalValueFlag()){
			Debug::error("{$f} retain original value flag is undefined");
		}elseif($this->hasOriginalValue()){
			$this->release($this->originalValue);
		}
		return $this->originalValue = $value;
	}

	public function getOriginalValue(){
		$f = __METHOD__;
		if(!$this->getRetainOriginalValueFlag()){
			$cn = $this->getName();
			$dsc = $this->getDataStructureClass();
			Debug::error("{$f} column \"{$cn}\" from class \"{$dsc}\" does not retain its original value");
		}elseif(!$this->hasOriginalValue()){
			return null;
		}
		return $this->originalValue;
	}

	public function hasOriginalValue():bool{
		$f = __METHOD__;
		$column_name = $this->getName();
		$print = false;
		if($this->getRetainOriginalValueFlag()){
			if($print){
				Debug::print("{$f} retain original value flag is set");
				if(empty($this->originalValue)){
					Debug::print("{$f} column \"{$column_name}\" does not have a value");
				}else{
					Debug::print("{$f} original value of column \"{$column_name}\" is \"{$this->originalValue}\"");
				}
			}
			return $this->originalValue !== null;
		}elseif($print){
			Debug::print("{$f} retain original value flag of column \"{$column_name}\" is not set, or original value is undefined");
		}
		return false;
	}

	public function setValueFromQueryResult($raw){
		$f = __METHOD__;
		try{
			$vn = $this->getName();
			$print = false;
			if($raw === null){
				if($this->hasValue()){
					$this->setValue(null);
				}
				if($print){
					Debug::print("{$f} received null input parameter");
				}
				return null;
			}
			$value = $this->parseValueFromQueryResult($raw);
			if($print){
				Debug::print("{$f} parsed value \"{$value}\" from raw result \"{$raw}\"");
			}
			if($this->getRetainOriginalValueFlag()){
				if($print){
					Debug::print("{$f} retain original value flag is set");
				}
				$this->setOriginalValue($value);
			}elseif($print){
				Debug::print("{$f} retain original value flag is not set");
			}
			return $this->setValue($value);
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function setRetainOriginalValueFlag(bool $value = true):bool{
		return $this->setFlag(COLUMN_FILTER_RETAIN_ORIGINAL_VALUE, $value);
	}

	public function getRetainOriginalValueFlag():bool{
		return $this->getFlag(COLUMN_FILTER_RETAIN_ORIGINAL_VALUE);
	}

	public function retainOriginalValue(bool $value = true): Datum{
		$this->setRetainOriginalValueFlag($value);
		return $this;
	}

	public function getDataStructureClass(){
		if($this->hasDataStructureClass()){
			return $this->dataStructureClass;
		}
		return $this->getDataStructure()->getClass();
	}

	public function setSensitiveFlag(bool $v=true):bool{
		return $this->setFlag(COLUMN_FILTER_SENSITIVE, $v);
	}

	public function getSensitiveFlag():bool{
		return $this->getFlag(COLUMN_FILTER_SENSITIVE);
	}

	public final function getDatabaseEncodedValue(){
		$f = __METHOD__;
		$name = $this->getName();
		$print = false;
		if($this->hasValue()){
			if($print){
				Debug::print("{$f} value is defined for column \"{$name}\"");
			}
			$value = $this->getValue();
		}elseif($this->hasDefaultValue()){
			if($print){
				Debug::print("{$f} value is undefined for column \"{$name}\", but there is a default value");
			}
			$value = $this->getDefaultValue();
		}else{
			if($print){
				Debug::print("{$f} neither value nor default value are defined for column \"{$name}\"");
			}
			return null;
		}
		if($print){
			Debug::print("{$f} about to return encoding of value \"{$value}\" for column \"{$name}\"");
		}
		return static::getDatabaseEncodedValueStatic($value);
	}

	public function replicate(...$params):?ReplicableInterface{
		$f = __METHOD__;
		$status = $this->beforeReplicateHook();
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} before replicate hook returned error status \"{$err}\"");
			$this->setObjectStatus($status);
			return null;
		}
		$class = $this->getClass();
		$args = $this->getConstructorParams();
		$replica = new $class(...$args);
		$replica->setReplicaFlag(true);
		$replica->copy($this);
		$status = $this->afterReplicateHook($replica);
		if($status !== SUCCESS){
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} after replicate hook returned error status \"{$err}\"");
			$this->setObjectStatus($status);
			return null;
		}
		return $replica;
	}

	public function getSelectOperatorAndIncrement($value){
		$f = __METHOD__;
		try{
			if($value === null){
				if(!$this->isNullable()){
					$name = $this->getName();
					Debug::error("{$f} this datum \"{$name}\" is not nullable");
				}
				$this->operatorCount++;
				return OPERATOR_IS_NULL;
			}
			$this->operatorCount++;
			return OPERATOR_EQUALS;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getUserWritableFlag():bool{
		return $this->getFlag("userWritable");
	}

	public function setUserWritableFlag(bool $v=true):bool{
		return $this->setFlag("userWritable", $v);
	}

	public function getCipherColumn(): ?CipherDatum{
		$f = __METHOD__;
		try{
			if(!$this->hasDataStructure()){
				Debug::error("{$f} data structure is undefined");
			}
			$row = $this->getDataStructure();
			$name = $this->getName();
			$vn = "{$name}Cipher";
			$cipher = $row->getColumn($vn);
			return $cipher;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	private function pushMirrorIndex(string $column_name):string{
		if(!is_array($this->mirrorIndices)){
			$this->mirrorIndices = [];
		}elseif(array_key_exists($column_name, $this->mirrorIndices)){
			return $column_name;
		}
		return $this->mirrorIndices[$column_name] = $column_name;
	}

	public function mirrorAtIndex($column_name){
		$class = static::class;
		$mirror = new $class($column_name);
		$mirror->setPersistenceMode($this->getPersistenceMode());
		$this->pushMirrorIndex($column_name);
		return $mirror;
	}

	public function hasMirrorIndices():bool{
		return is_array($this->mirrorIndices) && !empty($this->mirrorIndices);
	}

	public function setMirrorIndices(?array $mi):?array{
		if($this->hasMirrorIndices()){
			$this->release($this->mirrorIndices);
		}
		return $this->mirrorIndices = $this->claim($mi);
	}
	
	public function getMirrorIndices():array{
		$f = __METHOD__;
		if(!$this->hasMirrorIndices()){
			Debug::error("{$f} mirror indices are undefined");
		}
		return $this->mirrorIndices;
	}
	
	private function setMirroredValues($v){
		$f = __METHOD__;
		$print = false;
		if(!$this->hasMirrorIndices()){
			if($print){
				Debug::print("{$f} mirrorIndices is empty");
			}
			return $v;
		}
		$ds = $this->getDataStructure();
		foreach($this->getMirrorIndices() as $column_name){
			if($print){
				Debug::print("{$f} setting value at mirror index \"{$column_name}\"");
			}
			$datum = $ds->getColumn($column_name);
			$datum->setValue($v);
		}
		return $v;
	}

	public function getIgnoreInequivalenceFlag(){
		return $this->getFlag("ignoreInequivalence");
	}

	public function setIgnoreInqeuivalenceFlag($value){
		return $this->setFlag("ignoreInequivalence", $value);
	}

	public function isConcrete(): bool{
		return $this->getPersistenceMode() === PERSISTENCE_MODE_DATABASE;
	}

	protected function beforeUnsetValueHook(bool $force = false): int{
		if($this->hasAnyEventListener(EVENT_BEFORE_UNSET_VALUE)){
			$this->dispatchEvent(new BeforeUnsetValueEvent($force));
		}
		return SUCCESS;
	}

	public function unsetValue(bool $force = false): int{
		$f = __METHOD__;
		try{
			$column_name = $this->getName();
			$print = false;
			$status = $this->beforeUnsetValueHook($force);
			if($status !== SUCCESS){
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} beforeUnsetValueHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}elseif($force){
				if($print){
					Debug::print("{$f} ignoring nullability");
				}
			}elseif(!$this->isNullable() || ($this->hasValue() && ! $this->isRewritable())){
				if($print){
					Debug::print("{$f} column \"{$column_name}\" is not nullable");
				}
				return FAILURE;
			}elseif($print){
				Debug::print("{$f} this column is nullable");
			}
			if($this->hasValue()){
				if($print){
					Debug::printStackTraceNoExit("{$f} column \"{$column_name}\" has a value, unsetting it now");
				}
				unset($this->value);
				$persist_mode = $this->getPersistenceMode();
				switch($persist_mode){
					case PERSISTENCE_MODE_COOKIE:
						if(headers_sent()){
							Debug::warning("{$f} headers already sent somehow");
						}else{
							setcookie($column_name, '', time() - 3600);
						}
						unset($_COOKIE[$column_name]);
						break;
					case PERSISTENCE_MODE_ENCRYPTED:
						$this->getCipherColumn()->unsetValue($force);
						// XXX TODO don't have a way to determine nonce column name from here, but it really doesn't matter
						break;
					case PERSISTENCE_MODE_SESSION:
						unset($_SESSION[$column_name]);
						if($this->hasValue()){
							$v = $this->getValue();
							Debug::warning("{$f} before even getting through the switch statement, value of column \"{$column_name}\" is still defined as \"{$v}\"");
							Debug::printSessionHash();
							Debug::printStackTrace();
						}elseif($print){
							Debug::print("{$f} unset seems to have worked");
						}
						break;
					default:
						break;
				}
				if($this->hasValue()){
					$v = $this->getValue();
					Debug::error("{$f} before exiting this function, value of column \"{$column_name}\" is still defined as \"{$v}\"");
				}elseif($print){
					Debug::print("{$f} this function seems to have worked");
				}
				$this->setUpdateFlag(true);
				$this->afterUnsetValueHook($force);
				return SUCCESS;
			}elseif($print){
				Debug::print("{$f} column \"{$column_name}\" is already valueless");
			}
			return STATUS_UNCHANGED;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	protected function afterUnsetValueHook($force = false){
		if($this->hasAnyEventListener(EVENT_AFTER_UNSET_VALUE)){
			$this->dispatchEvent(new AfterUnsetValueEvent($force));
		}
		return SUCCESS;
	}

	public function releaseDataStructure(bool $deallocate=false){
		$f = __METHOD__;
		if(!$this->hasDataStructure()){
			Debug::error("{$f} data structure is undefined");
		}
		$ds = $this->getDataStructure();
		unset($this->dataStructure);
		if($this->hasAnyEventListener(EVENT_RELEASE_PARENT)){
			$this->dispatchEvent(new ReleaseParentNodeEvent($ds, $deallocate));
		}
		if(!BACKWARDS_REFERENCES_ENABLED){
			unset($ds);
			return;
		}
		$this->release($ds, $deallocate);
	}
	
	/**
	 *
	 * @param DataStructure $obj
	 * @return DataStructure
	 */
	public function setDataStructure($obj){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if($this->hasDataStructure()){
			if($print){
				Debug::printStackTraceNoExit("{$f} data structure was already assigned, releasing it now for this ".$this->getDebugString());
			}
			$this->releaseDataStructure();
		}elseif($print){
			Debug::print("{$f} assigning data structure for the first time for this ".$this->getDebugString());
		}
		if(!BACKWARDS_REFERENCES_ENABLED){
			if($print){
				Debug::print("{$f} backwards references are not enabled");
			}
			return $this->dataStructure = $obj;
		}elseif($print){
			Debug::print("{$f} backwards references are enabled");
		}
		if($obj instanceof HitPointsInterface){
			if($print){
				Debug::print("{$f} data structure is a HitPointsInterface");
			}
			$this->addDataStructureDeallocateListener($obj);
		}elseif($print){
			Debug::print("{$f} received something that is not a HitPointsInterface");
		}
		return $this->dataStructure = $this->claim($obj);
	}
	
	public function setValue($v){
		$f = __METHOD__;
		try{
			$vn = $this->getName();
			$print = $this->getDebugFlag();
			if($v instanceof ValueReturningCommandInterface){
				while($v instanceof ValueReturningCommandInterface){
					$v = $v->evaluate();
				}
			}
			if($print){
				if($this->hasDataStructure()){
					$ds = $this->getDataStructure();
					$dsc = $ds->getClass();
					$did = $ds->getDebugId();
					$decl = $ds->getDeclarationLine();
					Debug::print("{$f} data structure of class \"{$dsc}\" with debug ID {$did} was instantiated {$decl}");
				}
			}
			if($this->hasAnyEventListener(EVENT_BEFORE_SET_VALUE)){
				$this->dispatchEvent(new BeforeSetValueEvent($v));
			}
			if($this->hasAnyEventListener(EVENT_AFTER_SET_VALUE)){
				$event = new AfterSetValueEvent($v);
			}else{
				$event = null;
			}
			if(!$this->isRewritable() && $this->hasValue()){
				Debug::print("{$f} sorry, this datum is non-rewritable");
				if($this->hasAnyEventListener(EVENT_AFTER_SET_VALUE)){
					$this->dispatchEvent($event);
				}
				return null;
			}
			$receptivity = $this->hasDataStructure() ? $this->getDataStructure()->getReceptivity() : DATA_MODE_DEFAULT;
			if($this->getSealedFlag()){
				Debug::error("{$f} datum is sealed");
				return null;
			}elseif($this->hasApoptoticSignal()){
				$signal = $this->getApoptoticSignal();
				if($signal === $v){
					if($print){
						Debug::print("{$f} value matches apoptotic signal \"{$signal}\"");
					}
					$this->getDataStructure()->apoptose($this);
					if($this->hasValue()){
						$this->unsetValue(true);
					}
					return $v;
				}elseif($print){
					Debug::print("{$f} value does not match apoptotic signal");
				}
			}elseif($print){
				Debug::print("{$f} this column does not have an apoptotic signal");
			}
			if(!$this->hasDataStructure()){
				if($print){
					Debug::print("{$f} data structure is undefined");
				}
			}elseif($receptivity !== DATA_MODE_RECEPTIVE && $this->getDataStructure()->isUninitialized()){
				if($print){
					Debug::print("{$f} data structure is uninitialized");
				}
			}elseif((!$this->hasValue() && $v !== null) || ($this->hasValue() && $this->value !== $v)){
				if($print){
					Debug::print("{$f} new value \"{$v}\" differs from existing one \"{$this->value}\" -- setting update flag. Receptivity is {$receptivity}");
				}
				$this->setUpdateFlag(true);
			}elseif($print){
				Debug::print("{$f} new value \"{$v}\" is the same as the old one \"{$this->value}\"");
			}
			$mode = $this->getPersistenceMode();
			switch($mode){
				case (PERSISTENCE_MODE_COOKIE):
					if($print){
						Debug::print("{$f} datum \"{$vn}\" is stored in cookies");
					}
					$encoded = static::getDatabaseEncodedValueStatic($v);
					if(headers_sent()){
						Debug::error("{$f} cannot set cookie for column {$vn} -- headers already sent somehow");
						$_COOKIE[$vn] = $encoded;
					}else{
						set_secure_cookie($vn, $encoded);
					}
					break;
				case (PERSISTENCE_MODE_SESSION):
					if($print){
						Debug::print("{$f} datum \"{$vn}\" is stored in session memory; setting it to \"{$v}\"");
					}
					$_SESSION[$vn] = $v;
					$this->setMirroredValues($v);
					break;
				case PERSISTENCE_MODE_ENCRYPTED:
					if($print){
						Debug::print("{$f} column \"{$vn}\" is encrypted");
					}
					if($v === null){
						if($print){
							Debug::print("{$f} value is null; not going to encrypt an empty string just to waste space");
						}
						$this->value = $this->setMirroredValues($v);
						if($this->hasAnyEventListener(EVENT_AFTER_SET_VALUE)){
							$this->dispatchEvent($event);
						}
						return $this->value;
					}elseif($receptivity === DATA_MODE_PASSIVE){
						if($print){
							Debug::print("{$f} we are not going to do any encrypting because data mode is passive");
						}
						$this->value = $this->setMirroredValues($v);
						if($this->hasAnyEventListener(EVENT_AFTER_SET_VALUE)){
							$this->dispatchEvent($event);
						}
						return $this->value;
					}elseif($print){
						Debug::print("{$f} value is not null and data mode is not passive ({$receptivity}) -- about to encrypt contained value");
					}
					$cipher = $this->getCipherColumn();
					$scheme_class = $this->getEncryptionScheme();
					$scheme = new $scheme_class();
					switch($receptivity){
						case DATA_MODE_RECEPTIVE:
							if($print){
								$dsc = $this->getDataStructureClass();
								Debug::print("{$f} about to generate encryption key and nonce with scheme \"{$scheme_class}\" for datum \"{$vn}\" in a data structure of class \"{$dsc}\"; line 1166");
							}
							$key = $scheme->generateEncryptionKey($this);
							$nonce = $scheme->generateNonce($this);
							break;
						default:
							if($print){
								Debug::print("{$f} about to extract key and nonce with encryption scheme \"{$scheme_class}\" for datum \"{$vn}\"");
							}
							$key = $scheme->extractEncryptionKey($this);
							$nonce = $scheme->extractNonce($this);
							break;
					}
					$encoded = static::getDatabaseEncodedValueStatic($v);
					$encrypted = $scheme->encrypt($encoded, $key, $nonce);
					deallocate($scheme);
					$cipher->setValue($encrypted);
					break;
				default:
					if($print){
						Debug::print("{$f} datum \"{$vn}\" has storage mode \"{$mode}\"");
					}
					break;
			}
			if($print){
				if(is_bool($v)){
					if($v){
						$vs = "true";
					}else{
						$vs = "false";
					}
				}else{
					$vs = $v;
				}
				Debug::print("{$f} setting value of column \"{$vn}\" to \"{$vs}\"");
			}
			$this->value = $this->setMirroredValues($v);
			if($this->hasAnyEventListener(EVENT_AFTER_SET_VALUE)){
				$this->dispatchEvent($event);
			}
			return $this->value;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function generateComponent($subindex, $compound_name, ...$args){
		$vn = new ConcatenateCommand($compound_name, "[{$subindex}]");
		$class = static::class;
		$args = [];
		foreach($args as $arg){
			array_push($args, $arg);
		}
		return new $class($vn, $vn, ...$args);
	}

	public function getValue(){
		$f = __METHOD__;
		try{
			$column_name = $this->getName();
			$mode = $this->getPersistenceMode();
			$print = false;
			$mode = $this->getPersistenceMode();
			if(isset($this->value)){
				if($print){
					$gottype = gettype($this->value);
					Debug::print("{$f} value \"".base64_encode($this->value)."\" of type {$gottype} is already set for datum \"{$column_name}\"");
					if(is_bool($this->value)){
						if($this->value === true){
							Debug::print("{$f} value is true");
						}else{
							Debug::print("{$f} value is false");
						}
					}
				}
				return $this->value;
			}
			switch($mode){
				case PERSISTENCE_MODE_COOKIE:
					if($print){
						Debug::print("{$f} about to get datum \"{$column_name}\" from cookies");
					}
					if(isset($_COOKIE[$column_name])){
						$value = $this->parseValueFromQueryResult($_COOKIE[$column_name]);
						if($print){
							Debug::print("{$f} returning cookie value \"{$value}\"");
						}
						return $value;
					}
					break;
				case PERSISTENCE_MODE_SESSION:
					if($print){
						Debug::print("{$f} about to get datum \"{$column_name}\" from session");
					}
					if(isset($_SESSION[$column_name])){
						$value = $_SESSION[$column_name];
						if($print){
							Debug::print("{$f} returning session value \"{$value}\"");
						}
						return $value;
					}
					if($print){
						Debug::print("{$f} session datum \"{$column_name}\" is undefined");
					}
					break;
				case PERSISTENCE_MODE_ENCRYPTED:
					if(!$this->hasDataStructure()){
						Debug::error("{$f} data structure for column \"{$column_name}\" is undefined");
					}
					$cipher = $this->getCipherColumn();
					if(!$cipher->hasValue()){
						break;
					}
					$scheme_class = $this->getEncryptionScheme();
					if($print){
						Debug::print("{$f} encryption scheme class is \"{$scheme_class}\"");
					}
					$scheme = new $scheme_class();
					if($print){
						$dsc = $this->getDataStructureClass();
						$dsk = $this->getDataStructureKey();
						$did = $this->getDataStructure()->getDebugId();
						Debug::print("{$f} about to call {$scheme_class}->extractDecryptionKey(this) for column \"{$column_name}\" of data structure of class \"{$dsc}\" with key \"{$dsk}\" and debug Id \"{$did}\"");
					}
					$key = $scheme->extractDecryptionKey($this);
					$ds = $this->getDataStructure();
					if(empty($key)){
						Debug::warning("{$f} decryption key is undefined for datum \"{$column_name}\"");
						if($scheme instanceof SharedEncryptionSchemeInterface && ! $ds->getReplacementKeyRequested()){
							if($print){
								Debug::print("{$f} about to request replacement key");
							}
							$ds->requestReplacementDecryptionKey();
						}elseif($print){
							Debug::print("{$f} scheme \"{$scheme_class}\" is not shared, or data structure has already requested a replacement key");
						}
						break;
					}elseif($scheme instanceof SharedEncryptionSchemeInterface && $ds->getReplacementKeyRequested()){
						if($print){
							Debug::print("{$f} fulfilling request for replacing encryption key");
						}
						$ds->fulfillReplacementKeyRequest();
					}
					$nonce = $scheme->extractNonce($this);
					$raw = $scheme->decrypt($cipher->getValue(), $key, $nonce);
					deallocate($scheme);
					if($print && empty($raw)){
						Debug::error("{$f} raw decrypted value of column \"{$column_name}\" is empty");
					}elseif($print){
						Debug::print("{$f} raw decrypted value of column \"{$column_name}\" is \"".base64_encode($raw)."\"");
						if($this instanceof StringDatum && $this->hasRequiredLength()){
							if(strlen($raw) !== $this->getRequiredLength()){
								Debug::error("{$f} decrypted value \"".base64_encode($raw)."\" of \"{$column_name}\" has incorrect length");
							}else{
								Debug::print("{$f} decrypted value of \"{$column_name}\" has the correct length");
							}
						}
					}
					return $this->value = $this->parseValueFromQueryResult($raw);
				default:
					break;
			}
			if($this->hasDefaultValue()){
				return $this->getDefaultValue();
			}
			return null;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getCipherValue(){
		return $this->getCipherColumn()->getValue();
	}

	public function getNonceValue(){
		$sc = $this->getEncryptionScheme();
		$scheme = new $sc();
		return $scheme->extractNonce($this);
	}

	public function getNeverLeaveServer():bool{
		return $this->getFlag('neverLeaveServer');
	}

	public function setNeverLeaveServer(bool $ever=true):bool{
		return $this->setFlag('neverLeaveServer', $ever);
	}

	public function compareExistingValue($value){
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		$old = $this->getValue();
		if($print){
			$name = $this->getName();
			Debug::print("{$f} old value of datum \"{$name}\" is \"{$old}\"");
		}
		if($old === null){
			if($value === null || is_string($value) && $value === ""){
				if($print){
					Debug::print("{$f} old and new values are both null/empty string");
				}
				return STATUS_UNCHANGED;
			}elseif($print){
				Debug::print("{$f} old value is null, new value ain't");
			}
			return SUCCESS;
		}elseif($value === null){
			if($print){
				Debug:
				print("{$f} old value is \"{$old}\", but new value is null");
			}
			return SUCCESS;
		}
		if(is_string($old) && is_string($value)){
			if(strcmp($old, $value) === 0){
				if($print){
					Debug::print("{$f} strcmp({$old}, {$value}) returned true");
				}
				return STATUS_UNCHANGED;
			}elseif(trim($old) === trim($value)){
				if($print){
					Debug::print("{$f} they match once trimmed");
				}
				return STATUS_UNCHANGED;
			}elseif($print){
				Debug::print("{$f} strcmp returned false");
			}
		}elseif($old === $value){
			if($print){
				Debug::print("{$f} value \"{$value}\" has not changed");
			}
			return STATUS_UNCHANGED;
		}elseif($this instanceof FloatingPointDatum){
			if(close_enough($old, $value)){
				if($print){
					Debug::print("{$f} floating point comparison is close enough");
				}
				return STATUS_UNCHANGED;
			}
		}
		if($print){
			$old_type = gettype($old);
			$new_type = gettype($value);
			Debug::print("{$f} {$old_type} value has changed from \"{$old}\" to {$new_type} \"{$value}\"");
		}
		$old_hash = sha1($old);
		$new_hash = sha1($value);
		if($old_hash === $new_hash){
			Debug::warning("{$f} value has changed, but hash has not somewhow");
			$old_hex = bin2hex($old);
			$new_hex = bin2hex($value);
			Debug::warning("{$f} old value in hex is \"{$old_hex}\"");
			Debug::error("{$f} new value in hex is \"{$new_hex}\"");
		}elseif($print){
			Debug::print("{$f} old hash is \"{$old_hash}\"; new hash is \"{$new_hash}\"");
		}
		return SUCCESS;
	}

	public function getValidators($value){
		return [new DatumValidator($this, $value)];
	}

	public function hasValue():bool{
		$f = __METHOD__;
		$vn = $this->getName();
		$print = $this->getDebugFlag();
		switch($this->getPersistenceMode()){
			case PERSISTENCE_MODE_ENCRYPTED:
				// $value = $this->getValue();
				if($print){
					Debug::print("{$f} this datum is encrypted");
				}
				return $this->getCipherColumn()->hasValue(); // $value !== null && $value !== "" && strlen($value) > 0;
			case PERSISTENCE_MODE_COOKIE:
				if($print){
					Debug::print("{$f} value is stored in cookies");
				}
				return array_key_exists($vn, $_COOKIE) && $_COOKIE[$vn] !== null && $_COOKIE[$vn] !== "";
			case PERSISTENCE_MODE_SESSION:
				if($print){
					Debug::print("{$f} value is stored in session");
					if(isset($_SESSION)){
						Debug::print("{$f} session is defined");
					}else{
						Debug::print("{$f} session is undefined");
					}
					if(is_array($_SESSION)){
						Debug::print("{$f} session is an array");
					}else{
						Debug::print("{$f} session is not an array");
					}
					if(!empty($_SESSION)){
						Debug::print("{$f} session is not empty");
					}else{
						Debug::print("{$f} session is empty");
					}
					if(array_key_exists($vn, $_SESSION)){
						Debug::print("{$f} key \"{$vn}\" exists in session");
					}else{
						Debug::print("{$f} key \"{$vn}\" does not exist in session");
					}
					if($_SESSION[$vn] !== null){
						Debug::print("{$f} key \"{$vn}\" has maps to a non-null value");
					}else{
						Debug::print("{$f} key \"{$vn}\" maps to a null value");
					}
					if($_SESSION[$vn] !== ""){
						Debug::print("{$f} key \"{$vn}\" maps to something other than an empty string");
					}else{
						Debug::print("{$f} key \"{$vn}\" maps to an empty string");
					}
					// Debug::printSession();
				}
				return isset($_SESSION) && is_array($_SESSION) && !empty($_SESSION) && array_key_exists($vn, $_SESSION) && $_SESSION[$vn] !== null && $_SESSION[$vn] !== "";
			case PERSISTENCE_MODE_DATABASE:
			case PERSISTENCE_MODE_VOLATILE:
			default:
				if($print){
					Debug::print("{$f} default case");
					if($this->value !== null && $this->value !== ""){
						Debug::print("{$f} yes, value of \"{$vn}\" is non-null and not empty string");
					}else{
						$decl = $this->hasDataStructure() ? $this->getDataStructure()->getDeclarationLine() : "[unavailable]";
						Debug::print("{$f} no, value of \"{$vn}\" is null or empty string. Data structure was instantiated {$decl}");
					}
				}
				return isset($this->value) && $this->value !== null && $this->value !== "";
		}
	}

	/**
	 *
	 * @param InputElement $input
	 * @return int
	 */
	public function processInput($input){
		$f = __METHOD__;
		try{
			$vn = $this->getName();
			$print = $vn === "price";
			if($print){
				$ic = $input->getShortClass();
				Debug::print("{$f} about to call {$ic}->negotiateValue");
			}
			$negotiated = $input->negotiateValue($this);
			if($print){
				$gottype = gettype($negotiated);
				Debug::print("{$f} negotiated value \"{$negotiated}\" is type {$gottype}");
			}
			$value = $this->cast($negotiated);
			if($print){
				Debug::print("{$f} cast negotiated value \"{$negotiated}\" into \"{$value}\" for datum \"{$vn}\"");
			}
			if($this->hasApoptoticSignal() && $this->getApoptoticSignal() === $value){
				if($print){
					Debug::print("{$f} value is the apoptotic signal, doesn't matter if it's valid");
				}
				$status = $this->setObjectStatus(STATUS_UNCHANGED);
				$this->getDataStructure()->apoptose($this); // must set this here -- needed in processForm to determine which status code to return
			}else{
				if($print){
					if(!$this->hasApoptoticSignal()){
						Debug::print("{$f} there is no apoptotic signal");
					}else{
						$apop = $this->getApoptoticSignal();
						if($apop !== $value){
							Debug::print("{$f} apoptotic signal \"{$apop}\" does not match value \"{$value}\"");
						}else{
							Debug::error("{$f} apoptotic signal matches");
						}
					}
				}
				if(!$this->getAlwaysValidFlag()){
					$status = $this->validate($value);
					if($status !== SUCCESS){
						$err = ErrorMessage::getResultMessage($status);
						Debug::print("{$f} validation of datum \"{$vn}\" returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					}elseif($print){
						Debug::print("{$f} validation successful");
					}
				}elseif($print){
					Debug::print("{$f} skipping validation");
				}
				$status = $this->compareExistingValue($value);
				switch($status){
					case SUCCESS:
						if($print){
							Debug::print("{$f} value \"{$value}\" has changed for datum \"{$vn}\"");
						}
						break;
					case STATUS_UNCHANGED:
						if($print){
							Debug::print("{$f} value \"{$value}\" has not changed for datum \"{$vn}\"");
						}
						return $status;
					case ERROR_FILTER_LOCKED_OUT:
						Debug::warning("{$f} you are about to lock yourself out");
						return $status;
					default:
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} compareExistingValue returned error status \"{$err}\"");
						return $status;
				}
			}
			if($print){
				Debug::print("{$f} about to assign value to field \"{$vn}\"");
			}
			$value = $this->setValue($value);
			if($print){
				Debug::print("{$f} assigned value \"{$value}\" to field \"{$vn}\"");
			}
			return $status;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getAdminInterfaceFlag(){
		return $this->getFlag("adminInterface");
	}

	public function setAdminInterfaceFlag($value){
		return $this->setFlag("adminInterface", $value);
	}

	public function isSortable():bool{
		return $this->getFlag(COLUMN_FILTER_SORTABLE);
	}

	public function setSortable($value = true){
		return $this->setFlag(COLUMN_FILTER_SORTABLE, $value);
	}

	public function canHaveDefaultValue(): bool{
		return !($this instanceof BlobDatum || $this instanceof JsonDatum || $this instanceof TextDatum || $this instanceof GeometryDatum);
	}

	public function isComparable(){
		return true;
	}

	public function isRewritable(){
		return $this->getFlag(COLUMN_FILTER_REWRITABLE);
	}

	public function setRewritableFlag($value = true){
		return $this->setFlag(COLUMN_FILTER_REWRITABLE, $value);
	}

	/**
	 * returns true if this column satisfies the requirements of the given filters, false otherwise
	 *
	 * @param string[] $filters
	 * @return boolean
	 */
	public function applyFilter(...$filters):bool{
		$f = __METHOD__;
		try{
			$column_name = $this->getName();
			// $ds = $this->getDataStructure();
			$pm = $this->getPersistenceMode();
			$print = false && $this->getDebugFlag();
			if(count($filters) === 1 && is_array($filters[0])){
				$filters = $filters[0];
			}
			foreach($filters as $filter){
				if(!is_string($filter)){
					Debug::error("{$f} filter name must be a string");
				}elseif($print){
					Debug::print("{$f} entered for filter \"{$filter}\"");
				}
				$negate = false;
				if(starts_with($filter,'!')){ // XXX TODO trim all '!!' occurences
					if(starts_with($filter, "!!")){
						Debug::error("{$f} please don't double negate filter names you prick");
					}elseif($print){
						Debug::print("{$f} negating filter \"{$filter}\"");
					}
					$negate = ! $negate;
					$filter = substr($filter, 1);
				}
				// flags without filters:
				// adminInterface
				// ignoreInequivalence //I forgot
				// neverLeaveServer
				// paginator
				// processValuelessInput',//true => processInput will get called in DataStructure->processForm even if the input does not have a value attribute
				// userWritable
				switch($filter){
					case COLUMN_FILTER_ADD_TO_RESPONSE:
						$pass = $this instanceof ForeignKeyDatumInterface && $this->getFlag(COLUMN_FILTER_ADD_TO_RESPONSE);
						break;
					case COLUMN_FILTER_AFTER: // foreign columns that are inserted or updated after the host data structure
						$pass = $this->applyFilter(COLUMN_FILTER_FOREIGN, COLUMN_FILTER_DECLARED) && $this->getRelativeSequence() === CONST_AFTER;
						break;
					case COLUMN_FILTER_ALIAS:
						$pass = $pm === PERSISTENCE_MODE_ALIAS;
						break;
					case COLUMN_FILTER_ALPHANUMERIC:
					case "alphanumeric":
						$pass = $this instanceof StringDatum && $this->getFlag(COLUMN_FILTER_ALPHANUMERIC);
						break;
					case COLUMN_FILTER_APOPTOTIC:
						$pass = $this->getFlag(COLUMN_FILTER_APOPTOTIC);
						break;
					case "arrayMember":
					case COLUMN_FILTER_MEMBER:
					case "toArray":
						$pass = $this->getFlag(COLUMN_FILTER_ARRAY_MEMBER);
						break;
					case COLUMN_FILTER_AUTO_INCREMENT:
						$pass = $this instanceof IntegerDatum && $this->getAutoIncrementFlag();
						break;
					case COLUMN_FILTER_AUTOLOAD: // foreign columns that are autoloaded
						$pass = $this->applyFilter(COLUMN_FILTER_FOREIGN) && $this->getFlag(COLUMN_FILTER_AUTOLOAD);
						break;
					case COLUMN_FILTER_BBCODE:
						$pass = $this instanceof StringDatum && $this->getFlag(COLUMN_FILTER_BBCODE);
						break;
					case CONST_BEFORE: // foreign columns that are inserted or updated before the host data structure
						if($print){
							if($this->applyFilter(COLUMN_FILTER_FOREIGN)){
								Debug::print("{$f} column \"{$column_name}\" is a foreign column");
								if($this->applyFilter(COLUMN_FILTER_DECLARED)){
									Debug::print("{$f} column \"{$column_name}\" has the declared flag set, whatever that is");
									if($this->getRelativeSequence() === CONST_BEFORE){
										Debug::print("{$f} the foreign data structure at column \"{$column_name}\" must be inserted/updated before the host. Filter satisfied.");
									}else{
										Debug::print("{$f} Filter failed. The foreign data structure at column \"{$column_name}\" is inserted/updated after the host");
									}
								}elseif($print){
									Debug::print("{$f} Filter failed. Column \"{$column_name}\" does not have the declared flag set");
								}
							}elseif($print){
								Debug::print("{$f} Filter failed. Column \"{$column_name}\" is not a foreign column");
							}
						}
						$pass = $this->applyFilter(COLUMN_FILTER_FOREIGN, COLUMN_FILTER_DECLARED) && $this->getRelativeSequence() === CONST_BEFORE;
						break;
					case COLUMN_FILTER_BOOLEAN:
					case "bool":
					case "boolean":
						$pass = $this instanceof BooleanDatum;
						break;
					case COLUMN_FILTER_COMPARABLE:
					case "comparable": // eligible for comparison in DataStructure::equals
						$pass = $this->getPersistenceMode() !== PERSISTENCE_MODE_ENCRYPTED; //$this->getFlag(COLUMN_FILTER_COMPARABLE);
						break;
					case COLUMN_FILTER_CONSTRAIN:
					case "constraint":
						$pass = $this->applyFilter(COLUMN_FILTER_FOREIGN) && $this->getFlag(COLUMN_FILTER_CONSTRAIN);
						break;
					case COLUMN_FILTER_CONTRACT_VERTEX:
					case "contractVertex":
						$pass = $this->applyFilter(COLUMN_FILTER_FOREIGN) && $this->getFlag(COLUMN_FILTER_CONTRACT_VERTEX);
						break;
					case COLUMN_FILTER_COOKIE: // columns stored in cookies
						$pass = $pm === PERSISTENCE_MODE_COOKIE;
						break;
					case COLUMN_FILTER_CREATE_TABLE:
						if($this->getDataStructure() instanceof EmbeddedData){
							$pass = ! $this instanceof VirtualDatum && ($pm === PERSISTENCE_MODE_EMBEDDED || $pm === PERSISTENCE_MODE_DATABASE);
							if($print){
								Debug::print("{$f} data structure is embedded");
							}
						}else{
							if($print){
								Debug::print("{$f} data structure is NOT embedded");
							}
							$pass = ! $this instanceof VirtualDatum && $pm === PERSISTENCE_MODE_DATABASE;
						}
						if($print){
							if($pass){
								Debug::print("{$f} filter satisfied");
							}elseif($this instanceof VirtualDatum){
								Debug::print("{$f} filter failed because this is a virtual datum");
							}else{
								Debug::print("{$f} filter failed due to ".Debug::getPersistenceModeString($pm)." persistence mode");
							}
						}
						break;
					/*
					 * case COLUMN_FILTER_CRITICAL:
					 * $pass = $this instanceof ForeignKeyDatumInterface
					 * && $this->getCriticalFlag();
					 * break;
					 */
					case "concrete":
					case "database": // columns that are stored in a database
					case COLUMN_FILTER_DATABASE:
						$pass = $pm === PERSISTENCE_MODE_DATABASE;
						break;
					case COLUMN_FILTER_DECLARED:
					case "declare":
					case "declared":
						$pass = $this->getFlag(COLUMN_FILTER_DECLARED);
						break;
					case COLUMN_FILTER_DEFAULT:
						$pass = $this->hasDefaultValue();
						break;
					case COLUMN_FILTER_DIRTY_CACHE:
						$pass = $this->getFlag(COLUMN_FILTER_DIRTY_CACHE) && $pm !== COLUMN_FILTER_VOLATILE;
						break;
					case COLUMN_FILTER_RECURSIVE_DELETE:
						$pass = $this instanceof ForeignKeyDatumInterface && $this->getRecursiveDeleteFlag();
					case COLUMN_FILTER_DISABLED:
					case "disabled":
						$pass = $this->getFlag(COLUMN_FILTER_DISABLED);
						break;
					case COLUMN_FILTER_DOUBLE:
					case "double":
						$pass = $this instanceof DoubleDatum;
						break;
					case COLUMN_FILTER_EAGER:
						$pass = $this->applyFilter(COLUMN_FILTER_FOREIGN) && $this->getFlag(COLUMN_FILTER_EAGER);
						break;
					case COLUMN_FILTER_EMBEDDED:
					case "embedded": // columns stored in separate tables
						$pass = $pm === PERSISTENCE_MODE_EMBEDDED;
						break;
					case COLUMN_FILTER_ENCRYPTED:
					case "encrypt":
					case "encrypted": // columns that automatically transcrypt their values
						$pass = $pm === PERSISTENCE_MODE_ENCRYPTED;
						break;
					case COLUMN_FILTER_EVENT_SOURCE:
					case "event":
					case "eventSrc":
					case "eventSource": // datum is flagged to automatically track changes to itself with event sourcing
						$pass = $this->getFlag(COLUMN_FILTER_EVENT_SOURCE);
						break;
					case COLUMN_FILTER_FLOAT:
					case "float":
						$pass = $this instanceof FloatingPointDatum;
						break;
					case COLUMN_FILTER_FOREIGN: // columns that reference a foreign data structure
						$pass = $this instanceof ForeignKeyDatumInterface;
						break;
					case COLUMN_FILTER_FULLTEXT:
						$pass = $this instanceof FullTextStringDatum && $this->getFlag(COLUMN_FILTER_FULLTEXT);
						break;
					case COLUMN_FILTER_ID:
						if(!$this->hasDataStructure()){
							Debug::error("{$f} cannot apply the ID filter to datums without data structures");
						}
						$pass = $column_name === $this->getDataStructure()->getIdentifierName();
						break;
					case COLUMN_FILTER_INDEX:
						$pass = $this->getFlag(COLUMN_FILTER_INDEX);
						break;
					case COLUMN_FILTER_INSERT:
						$pass = $this->applyFilter(COLUMN_FILTER_CREATE_TABLE, COLUMN_FILTER_VALUED) && ! $this->applyFilter(COLUMN_FILTER_SERIAL);
						break;
					case COLUMN_FILTER_INTEGER:
					case "int":
					case "integer":
						$pass = $this instanceof IntegerDatum;
						break;
					case "intersect":
					case COLUMN_FILTER_INTERSECTION: // foreign key columns whose values are stored in intersections tables
						if(!$this->applyFilter(COLUMN_FILTER_FOREIGN)){
							$pass = false;
							break;
						}
						$pass = $pm === PERSISTENCE_MODE_INTERSECTION;
						break;
						$rt = $this->getRelationshipType();
						$pass = $pm === PERSISTENCE_MODE_INTERSECTION || $pm !== PERSISTENCE_MODE_COOKIE && $pm !== PERSISTENCE_MODE_SESSION && $pm !== PERSISTENCE_MODE_VOLATILE && ($rt === RELATIONSHIP_TYPE_MANY_TO_MANY || ($this instanceof KeyListDatum && ! $this->getOneSidedFlag()));
						break;
					case COLUMN_FILTER_LOADED:
						$pass = $this instanceof KeyListDatum && $this->getLoadedFlag();
						break;
					case COLUMN_FILTER_NL2BR:
						$pass = $this instanceof StringDatum && $this->getFlag(COLUMN_FILTER_NL2BR);
						break;
					case COLUMN_FILTER_NULLABLE:
					case "null":
					case "nullable":
						$pass = $this->getFlag(COLUMN_FILTER_NULLABLE);
						break;
					case COLUMN_FILTER_ONE_SIDED:
						$pass = $this instanceof KeyListDatum && $this->getOneSidedFlag();
						if(true || $print){
							if($pass){
								Debug::print("{$f} column \"{$column_name}\" is flagged as one-sided");
							}
						}
						break;
					case COLUMN_FILTER_ORIGINAL:
					case "original":
						$pass = $this->hasOriginalValue();
						break;
					case COLUMN_FILTER_POTENTIAL:
						$pass = $this->getPersistenceMode() === PERSISTENCE_MODE_INTERSECTION && ($this instanceof KeyListDatum || ($this instanceof ForeignKeyDatum && $this->hasPotentialValue()));
						break;
					case COLUMN_FILTER_PREVENT_CIRCULAR_REF:
						$pass = $this->applyFilter(COLUMN_FILTER_FOREIGN) && $this->getFlag(COLUMN_FILTER_PREVENT_CIRCULAR_REF);
						break;
					case COLUMN_FILTER_PRIMARY_KEY:
					case "primaryKey":
						$pass = $this->getFlag(COLUMN_FILTER_PRIMARY_KEY);
						break;
					case COLUMN_FILTER_REPLICA:
					case "replicated":
						$pass = $this->getFlag(COLUMN_FILTER_REPLICA);
						break;
					case COLUMN_FILTER_RETAIN_ORIGINAL_VALUE:
					case "retainOriginal":
					case "retainOriginalValue":
						$pass = $this->getFlag(COLUMN_FILTER_RETAIN_ORIGINAL_VALUE);
						break;
					case COLUMN_FILTER_REWRITABLE:
					case "rewrite":
					case "rewritable":
						$pass = $this->getFlag(COLUMN_FILTER_REWRITABLE);
						break;
					case COLUMN_FILTER_SEALED:
					case "sealed":
						$pass = $this->getFlag(COLUMN_FILTER_SEALED);
						break;
					case COLUMN_FILTER_SEARCHABLE:
					case "searchable":
						$pass = $this->getFlag(COLUMN_FILTER_SEARCHABLE);
						break;
					case COLUMN_FILTER_SERIAL:
						$pass = $this instanceof SerialNumberDatum || ($this instanceof UnsignedIntegerDatum && $this->getBitCount() === 64 && ! $this->isNullable() && $this->getAutoIncrementFlag() && $this->getUniqueFlag());
						break;
					case COLUMN_FILTER_SENSITIVE:
						$pass = $this->getFlag(COLUMN_FILTER_SENSITIVE);
						break;
					case COLUMN_FILTER_SESSION: // columns stored in session memory
						$pass = $pm === PERSISTENCE_MODE_SESSION;
						break;
					case COLUMN_FILTER_SIGNED:
						$pass = $this instanceof SignedIntegerDatum || $this instanceof FloatingPointDatum || ($this instanceof IntegerDatum && ! $this->isUnsigned());
						break;
					case COLUMN_FILTER_SORTABLE:
					case "sortable": // columns flagged as sortable
						$pass = $this->getFlag(COLUMN_FILTER_SORTABLE);
						break;
					case COLUMN_FILTER_STRING:
					case "str":
					case "string":
						$pass = $this instanceof StringDatum;
						break;
					case COLUMN_FILTER_TEMPLATE:
						$pass = $this->applyFilter(COLUMN_FILTER_FOREIGN) && $this->getFlag(COLUMN_FILTER_TEMPLATE);
						break;
					case COLUMN_FILTER_TIMESTAMP:
						$pass = $this instanceof TimestampDatum;
						break;
					case COLUMN_FILTER_TRIMMABLE:
					case "trimmable":
						$pass = $this->getFlag(COLUMN_FILTER_TRIMMABLE);
						break;
					case COLUMN_FILTER_UNIQUE:
						$pass = $this->getFlag(COLUMN_FILTER_UNIQUE);
						break;
					case COLUMN_FILTER_UNSIGNED:
						$pass = $this instanceof UnsignedIntegerDatum || $this instanceof FloatingPointDatum || ($this instanceof IntegerDatum && $this->isUnsigned());
						break;
					case COLUMN_FILTER_UPDATE:
						$pass = $this->getFlag(DIRECTIVE_UPDATE);
						break;
					case COLUMN_FILTER_NOW:
						$pass = $this instanceof TimestampDatum && $this->getUpdateToCurrentTimeFlag();
						break;
					case COLUMN_FILTER_VALUED:
					case "value":
					case "valued": // datums with defined values
						$pass = $this->hasValue();
						break;
					case "virt":
					case COLUMN_FILTER_VIRTUAL:
						$pass = $this instanceof VirtualDatum;
						break;
					case COLUMN_FILTER_VOLATILE:
						$pass = $pm === PERSISTENCE_MODE_VOLATILE;
						break;
					default:
						if(is_a($filter, Datum::class, true)){
							if($print){
								Debug::print("{$f} filter is a datum class");
								if($this instanceof $filter){
									Debug::print("{$f} yes, column {$column_name} is a {$filter}");
								}else{
									Debug::print("{$f} no, column {$column_name} of class ".$this->getClass()." is not a {$filter}");
								}
							}
							$pass = $this instanceof $filter;
							break;
						}
						Debug::error("{$f} invalid filter \"{$filter}\"");
				}
				if($pass){
					if($print){
						Debug::print("{$f} filter \"{$filter}\" satisfied");
					}
					if($negate){
						if($print){
							Debug::print("{$f} negation in effect, returning false");
						}
						return false;
					}
					continue;
				}else{
					if($print){
						Debug::print("{$f} filter \"{$filter}\" failed for column \"{$column_name}\"");
					}
					if($negate){
						if($print){
							Debug::print("{$f} negation in effect, continuing");
						}
						continue;
					}
					return false;
				}
			}
			if($print){
				Debug::print("{$f} all filters satisfied");
			}
			return true;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function seal(bool $value = true): Datum{
		$this->setSealedFlag($value);
		return $this;
	}

	public function setSealedFlag(bool $value = true): bool{
		return $this->setFlag(COLUMN_FILTER_SEALED, $value);
	}

	public function getSealedFlag(): bool{
		return $this->getFlag(COLUMN_FILTER_SEALED);
	}

	public function setDirtyCacheFlag(bool $value = true): bool{
		return $this->setFlag(COLUMN_FILTER_DIRTY_CACHE, $value);
	}

	public function getDirtyCacheFlag(): bool{
		return $this->getFlag(COLUMN_FILTER_DIRTY_CACHE);
	}

	public function recache(bool $value = true): Datum{
		$this->setDirtyCacheFlag($value);
		return $this;
	}

	public function encrypt(?string $scheme): Datum{
		$this->setEncryptionScheme($scheme);
		return $this;
	}
	
	public function dispose(bool $deallocate=false): void{
		$f = __METHOD__;
		$print = false;
		if($this->hasDataStructure()){
			$this->releaseDataStructure($deallocate);
		}
		parent::dispose($deallocate);
		$this->release($this->aliasExpression, $deallocate);
		$this->release($this->apoptoticSignal, $deallocate);
		$this->release($this->columnAlias, $deallocate);
		$this->release($this->databaseStorageType, $deallocate);
		$this->release($this->dataStructureClass, $deallocate);
		$this->release($this->decryptionKeyName, $deallocate);
		$this->release($this->elementClass, $deallocate);
		$this->release($this->engineAttributeString, $deallocate);
		$this->release($this->generationClosure, $deallocate);
		$this->release($this->humanReadableName, $deallocate);
		$this->release($this->mirrorIndices, $deallocate);
		$this->release($this->permissionGateway, $deallocate);
		if($this->hasPermissions()){
			$this->releasePermissions($deallocate);
		}
		$this->release($this->singlePermissionGateways, $deallocate);
		$this->release($this->propertyTypes, $deallocate);
		if($this->hasReferenceColumn()){
			if($print){
				Debug::print("{$f} about to release reference column ");
			}
			$this->release($this->referenceColumn);
		}
		$this->release($this->referenceColumnName, $deallocate);
		$this->release($this->regenerationClosure, $deallocate);
		$this->release($this->subqueryClass, $deallocate);
		$this->release($this->subqueryColumnName, $deallocate);
		$this->release($this->subqueryDatabaseName, $deallocate);
		$this->release($this->subqueryExpression, $deallocate);
		$this->release($this->subqueryLimit, $deallocate);
		$this->release($this->subqueryOrderBy, $deallocate);
		$this->release($this->subqueryParameters, $deallocate);
		$this->release($this->subqueryTableAlias, $deallocate);
		$this->release($this->subqueryTypeSpecifier, $deallocate);
		$this->release($this->subqueryWhereCondition, $deallocate);
		$this->release($this->transcryptionKeyName, $deallocate);
		$this->release($this->validationClosure, $deallocate);
		$this->release($this->value, $deallocate);
	}
}
