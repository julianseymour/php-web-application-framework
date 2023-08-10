<?php
namespace JulianSeymour\PHPWebApplicationFramework\datum;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\close_enough;
use function JulianSeymour\PHPWebApplicationFramework\getTypeSpecifier;
use function JulianSeymour\PHPWebApplicationFramework\set_secure_cookie;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\starts_with;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\auth\permit\PermissiveTrait;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\expression\BinaryExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\common\DisabledFlagTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ElementBindableTrait;
use JulianSeymour\PHPWebApplicationFramework\common\HumanReadableNameTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ReplicableTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\UpdateFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ValuedTrait;
use JulianSeymour\PHPWebApplicationFramework\common\arr\ArrayKeyProviderInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\crypt\CipherDatum;
use JulianSeymour\PHPWebApplicationFramework\crypt\schemes\SharedEncryptionSchemeInterface;
use JulianSeymour\PHPWebApplicationFramework\crypt\schemes\SymmetricEncryptionScheme;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructuralTrait;
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
use JulianSeymour\PHPWebApplicationFramework\event\EventListeningTrait;
use JulianSeymour\PHPWebApplicationFramework\image\ImageData;
use JulianSeymour\PHPWebApplicationFramework\input\InputElement;
use JulianSeymour\PHPWebApplicationFramework\json\JsonDatum;
use JulianSeymour\PHPWebApplicationFramework\query\CollatedTrait;
use JulianSeymour\PHPWebApplicationFramework\query\CommentTrait;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\SecondaryEngineAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnAlias;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnAliasExpression;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\column\PrimaryKeyFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\query\column\UniqueFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\query\column\VisibilityTrait;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\CheckConstraint;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\ConstrainableTrait;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\Constraint;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\PrimaryKeyConstraint;
use JulianSeymour\PHPWebApplicationFramework\query\constraint\UniqueConstraint;
use JulianSeymour\PHPWebApplicationFramework\query\index\IndexDefinition;
use JulianSeymour\PHPWebApplicationFramework\query\index\IndexNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\index\IndexTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\query\index\KeyPart;
use JulianSeymour\PHPWebApplicationFramework\query\join\TableFactor;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereConditionalInterface;
use JulianSeymour\PHPWebApplicationFramework\validate\DatumValidator;
use JulianSeymour\PHPWebApplicationFramework\validate\ValidationClosureTrait;
use Closure;
use Exception;
use mysqli;

/**
 * a single variable that is stored under a column in the database, or in a single index of a superglobal array
 *
 * @author j
 */
abstract class Datum extends AbstractDatum implements ArrayKeyProviderInterface, SQLInterface, StaticPropertyTypeInterface
{

	use CollatedTrait;
	use ColumnNameTrait;
	use CommentTrait;
	use ConstrainableTrait;
	use DataStructuralTrait;
	use DisabledFlagTrait;
	use ElementBindableTrait;
	use EmbeddableTrait;
	use EventListeningTrait;
	use HumanReadableNameTrait;
	use IndexNameTrait;
	use IndexTypeTrait;
	use PermissiveTrait;
	use PrimaryKeyFlagBearingTrait;
	use ReplicableTrait;
	use SecondaryEngineAttributeTrait;
	use StaticPropertyTypeTrait;
	use UniqueFlagBearingTrait;
	use UpdateFlagBearingTrait;
	use ValidationClosureTrait;
	use ValuedTrait;
	use VisibilityTrait;

	/**
	 * Attempting to pass this value to setValue will trigger the apoptose() function on the
	 * DataStructure that contains this Datum
	 *
	 * @var mixed
	 */
	protected $apoptoticSignal;

	protected $columnAlias;

	/**
	 * specifies COLUMN_FORMAT part of declaration string
	 *
	 * @var string
	 */
	protected $columnFormatType;

	/**
	 * fallback for loose datums declared without a data structure
	 *
	 * @var string
	 */
	protected $dataStructureClass;

	/**
	 * Used in create table query generation.
	 * Not to be confused with $persistenceMode.
	 * Only applies to datums with $persistenceMode === PERSISTENCE_MODE_DATABASE
	 */
	protected $databaseStorageType;

	/**
	 * Name of a virtual column used to get the key used to decrypt this column's cipher value
	 *
	 * @var string
	 */
	protected $decryptionKeyName;

	/**
	 * expression for generating values of datums with GENERATED ALWAYS AS in their declaration string
	 *
	 * @var ExpressionCommand
	 */
	protected $generatedAlwaysAsExpression;

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
	 * needed to make aliased columns searchable
	 */
	protected $referenceColumn;

	protected $referenceColumnName;

	/**
	 * similar to the generationClosure, but for columns with special logic for generating a new, non-initial value, i.e.
	 * when changing password
	 *
	 * @var Closure
	 */
	protected $regenerationClosure;

	/**
	 * SelectStatement for columns whose values are selected via subquery
	 *
	 * @var SelectStatement
	 */
	protected $aliasExpression;

	// these are for generating the aliasExpression if it is not set explicitly
	protected $subqueryClass;

	protected $subqueryColumnName;

	protected $subqueryDatabaseName;

	protected $subqueryTableName;

	protected $subqueryTableAlias;

	protected $subqueryExpression;

	protected $subqueryLimit;

	protected $subqueryOrderBy;

	protected $subqueryParameters;

	protected $subqueryTypeSpecifier;

	protected $subqueryWhereCondition;

	/**
	 * name of the neighbor column that contains the key for en/decrypting this one
	 *
	 * @var string
	 */
	protected $transcryptionKeyName;

	public abstract function parseValueFromSuperglobalArray($value);

	public abstract function parseValueFromQueryResult($raw);

	public abstract static function validateStatic($value): int;

	public abstract static function getTypeSpecifier();

	public abstract function getUrlEncodedValue();

	public abstract function getHumanReadableValue();

	public abstract function getHumanWritableValue();

	public abstract static function parseString(string $string);

	public abstract function getColumnTypeString(): string;

	protected abstract function getConstructorParams(): ?array;

	public function __construct($name)
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		parent::__construct();
		// $this->requirePropertyType("constraints", Constraint::class); //memprof says this is a hog
		if (! isset($name)) {
			Debug::error("{$f} name is undefined");
		}
		$this->setColumnName($name);
		$this->setRewritableFlag(true);
		$this->setTrimmableFlag(true);
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"adminInterface", // true => automatically generate an input for this datum in a DefaultForm
			COLUMN_FILTER_ALWAYS_VALID,
			COLUMN_FILTER_APOPTOTIC, // true => apoptotic signal has been defined, possibly as null
			COLUMN_FILTER_ARRAY_MEMBER, // true => datum's value will be in the array generated by its DataStructure->toArray()
			COLUMN_FILTER_DECLARED, // true => this datum was declared by the containing structure
			COLUMN_FILTER_DISABLED,
			COLUMN_FILTER_INDEX, // true => this column is an index
			"ignoreInequivalence", // true => I forgot
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

	public function setAlwaysValidFlag(bool $value = true): bool
	{
		return $this->setFlag(COLUMN_FILTER_ALWAYS_VALID, $value);
	}
	
	public function getAlwaysValidFlag(): bool
	{
		return $this->getFlag(COLUMN_FILTER_ALWAYS_VALID);
	}
	
	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array
	{
		return [
			"constraints" => Constraint::class
		];
	}

	public function setDeclaredFlag(bool $value = true): bool
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		$cn = $this->getColumnName();
		if ($cn === "listedIpAddresses") {
			Debug::printStackTraceNoExit("{$f} entered");
		}
		return $this->setFlag(COLUMN_FILTER_DECLARED, $value);
	}

	public function getDeclaredFlag(): bool
	{
		return $this->getFlag(COLUMN_FILTER_DECLARED);
	}

	public function setUpdateFlag($value = true)
	{
		// $f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if ($this->hasDataStructure()) {
			$this->getDataStructure()->setUpdateFlag($value);
		}
		return $this->setFlag(DIRECTIVE_UPDATE, $value);
	}

	public final function getArrayKey(int $count)
	{
		return $this->getColumnName();
	}

	public function hasGenerationClosure(): bool
	{
		return isset($this->generationClosure) && $this->generationClosure instanceof Closure;
	}

	public function setGenerationClosure(?Closure $closure): ?Closure
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if ($closure == null) {
			unset($this->generationClosure);
			return null;
		} elseif (! $closure instanceof Closure) {
			Debug::error("{$f} closure must be a closure");
		}
		return $this->generationClosure = $closure;
	}

	public function getGenerationClosure(): ?Closure
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if (! $this->hasGenerationClosure()) {
			Debug::error("{$f} generation closure is undefined");
		}
		return $this->generationClosure;
	}

	public function hasRegenerationClosure(): bool
	{
		return isset($this->regenerationClosure) && $this->regenerationClosure instanceof Closure;
	}

	public function setRegenerationClosure(?Closure $closure): ?Closure
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if ($closure == null) {
			unset($this->regenerationClosure);
			return null;
		} elseif (! $closure instanceof Closure) {
			Debug::error("{$f} closure must be a closure");
		}
		return $this->regenerationClosure = $closure;
	}

	public function getRegenerationClosure(): ?Closure
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if (! $this->hasRegenerationClosure()) {
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
	public function eventSource(mysqli $mysqli, $input_token, $previous_state, $next_state): int
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		try {
			$print = false;
			$event_src = new EventSourceData($this, ALLOCATION_MODE_EAGER);
			$event_src->setUserData(user());
			if ($previous_state !== null) {
				$event_src->setPreviousState($previous_state);
			}
			if ($input_token !== null) {
				$event_src->setToken($input_token);
			}
			$event_src->setCurrentState($next_state);
			$event_src->setTargetData($this->getDataStructure());

			/*
			 * if(!$event_src->tableExists($mysqli)){
			 * $event_src->createTable($mysqli);
			 * }
			 */

			$status = $event_src->insert($mysqli);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} inserting event source returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			} elseif ($print) {
				Debug::print("{$f} successfully inserted event source data");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function encrypt(?string $scheme): Datum
	{
		$this->setEncryptionScheme($scheme);
		return $this;
	}

	public function generate(): int
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		try {
			$cn = $this->getColumnName();
			$print = false;
			if ($this->hasGenerationClosure()) {
				if ($print) {
					Debug::print("{$f} this object has a generation closure");
				}
				$closure = $this->getGenerationClosure();
				$value = $closure($this);
				if ($print) {
					Debug::print("{$f} generated initial value \"{$value}\"");
				}
				$this->setValue($value);
			} elseif ($this->hasDefaultValue()) {
				if ($print) {
					Debug::print("{$f} this column has a default value");
				}
				$value = $this->getDefaultValue();
				if ($print) {
					Debug::print("{$f} generated initial value \"{$value}\"");
				}
				$this->setValue($value);
			} elseif ($print) {
				Debug::print("{$f} this column has neither a generation closure nor a default value");
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function regenerate(): int
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		$print = false;
		if ($this->hasRegenerationClosure()) {
			if ($print) {
				Debug::print("{$f} this object has a generation closure");
			}
			$closure = $this->getRegenerationClosure();
			$value = $closure($this);
			if ($print) {
				Debug::print("{$f} generated initial value \"{$value}\"");
			}
			$this->setValue($value);
			return SUCCESS;
		} elseif ($print) {
			Debug::print("{$f} regeneration closure is undefined; falling back to initial generation function");
		}
		return $this->generate();
	}

	public function hasDecryptionKeyName()
	{
		return isset($this->decryptionKeyName) && is_string($this->decryptionKeyName) && ! empty($this->decryptionKeyName);
	}

	public function setDecryptionKeyName($n)
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if ($n == null) {
			unset($this->decryptionKeyName);
			return null;
		} elseif (! is_string($n)) {
			Debug::error("{$f }transcryption key name must be a string");
			return null;
		} elseif (empty($n)) {
			Debug::error("{$f} transcryption key name cannot be the empty string");
			return null;
		}
		return $this->decryptionKeyName = $n;
	}

	public function getDecryptionKeyName()
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if (! $this->hasDecryptionKeyName()) {
			$name = $this->getColumnName();
			Debug::error("{$f} transcryption key name is undefined for column \"{$name}\"");
			return null;
		}
		return $this->decryptionKeyName;
	}

	public function hasTranscryptionKeyName()
	{
		return isset($this->transcryptionKeyName) && is_string($this->transcryptionKeyName) && ! empty($this->transcryptionKeyName);
	}

	public function setTranscryptionKeyName($n)
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if ($n == null) {
			unset($this->transcryptionKeyName);
			return null;
		} elseif (! is_string($n)) {
			Debug::error("{$f }transcryption key name must be a string");
			return null;
		} elseif (empty($n)) {
			Debug::error("{$f} transcryption key name cannot be the empty string");
			return null;
		}
		return $this->transcryptionKeyName = $n;
	}

	public function getTranscryptionKeyName()
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if (! $this->hasTranscryptionKeyName()) {
			$name = $this->getColumnName();
			Debug::error("{$f} transcryption key name is undefined for column \"{$name}\"");
			return null;
		}
		return $this->transcryptionKeyName;
	}

	public function setDataStructureClass($class)
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if (! class_exists($class)) {
			Debug::error("{$f} class \"{$class}\" does not exist");
		}
		return $this->dataStructureClass = $class;
	}

	protected function hasDataStructureClass()
	{
		return isset($this->dataStructureClass);
	}

	private function setApoptoticSignalFlag($value = true)
	{
		return $this->setFlag(COLUMN_FILTER_APOPTOTIC, $value);
	}

	private function getApoptoticSignalFlag()
	{
		return $this->getFlag(COLUMN_FILTER_APOPTOTIC);
	}

	public function setApoptoticSignal($value)
	{
		$this->setApoptoticSignalFlag(true);
		return $this->apoptoticSignal = $value;
	}

	public function hasApoptoticSignal()
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->hasApoptoticSignal()";
		$cn = $this->getColumnName();
		$print = false;
		if ($print) {
			if (isset($this->apoptoticSignal)) {
				Debug::print("{$f} apoptotic signal is set");
			} else {
				Debug::print("{$f} apoptotic signal is NOT set");
			}
			if ($this->getApoptoticSignalFlag()) {
				Debug::print("{$f} apoptotic signal flag is set");
			} else {
				Debug::print("{$f} apoptotic signal flag is NOT set");
			}
		}
		return isset($this->apoptoticSignal) || $this->getApoptoticSignalFlag();
	}

	public function getApoptoticSignal()
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if (! $this->hasApoptoticSignal()) {
			Debug::error("{$f} apoptotic signal is undefined");
		}
		return $this->apoptoticSignal;
	}

	public function clearApoptoticSignal()
	{
		unset($this->apoptoticSignal);
		$this->setFlag(COLUMN_FILTER_APOPTOTIC, false);
	}

	public function setTrimmableFlag(bool $value = true): bool
	{
		return $this->setFlag(COLUMN_FILTER_TRIMMABLE, $value);
	}

	public function getTrimmableFlag(): bool
	{
		return $this->getFlag(COLUMN_FILTER_TRIMMABLE);
	}

	public function setIndexFlag(bool $value = true): bool
	{
		return $this->setFlag(COLUMN_FILTER_INDEX, $value);
	}

	public function getIndexFlag(): bool
	{
		return $this->getFlag(COLUMN_FILTER_INDEX);
	}

	public function index(bool $value = true): Datum
	{
		$this->setIndexFlag($value);
		return $this;
	}

	protected function beforeEjectValueHook()
	{
		$this->dispatchEvent(new BeforeEjectValueEvent());
		return SUCCESS;
	}

	public function ejectValue()
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		$name = $this->getColumnName();
		$print = false;
		if ($this->hasValue()) {
			$status = $this->beforeEjectValueHook();
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} beforeEjectValueHook for column \"{$name}\" returned error status \"{$err}\"");
				$this->setObjectStatus($status);
				return null;
			}
			$ret = $this->getValue();
			$this->unsetValue(true);
			if ($this->hasValue()) {
				Debug::error("{$f} immediately after unsetValue, value is still defined for column \"{$name}\"");
			} elseif ($print) {
				Debug::print("{$f} returning \"{$ret}\" for column \"{$name}\"");
			}
			$this->afterEjectValueHook($ret);
			return $ret;
		} elseif ($print) {
			Debug::print("{$f} value is already undefined for column \"{$name}\"");
		}
		return null;
	}

	protected function afterEjectValueHook($v)
	{
		$this->dispatchEvent(new AfterEjectValueEvent($v));
		return SUCCESS;
	}

	public function configureArrayMembership($value)
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if (! is_bool($value)) {
			Debug::error("{$f} only foreign key datums can configure array membership");
		}
		return $this->setArrayMembershipFlag($value);
	}

	public function isSearchable():bool
	{
		return $this->getFlag(COLUMN_FILTER_SEARCHABLE);
	}

	public function setSearchable(bool $value = true):bool
	{
		return $this->setFlag(COLUMN_FILTER_SEARCHABLE, $value);
	}

	public function setProcessValuelessInputFlag(bool $value = true):bool
	{
		return $this->setFlag("processValuelessInput", $value);
	}

	public function getProcessValuelessInputFlag()
	{
		return $this->getFlag("processValuelessInput");
	}

	public function setArrayMembershipFlag(bool $value): bool
	{
		if ($this->getNeverLeaveServer()) {
			return false;
		}
		return $this->setFlag(COLUMN_FILTER_ARRAY_MEMBER, $value);
	}

	public function getArrayMembershipFlag():bool
	{
		if ($this->getNeverLeaveServer()) { // note: sensitive data can still be converted to array because the backup server needs them
			return false;
		}
		return $this->getFlag(COLUMN_FILTER_ARRAY_MEMBER);
	}

	public function copy(Datum $that): int
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		$print = false;
		if ($that->hasFlags()) { // XXX should not be copying flags
			foreach ($that->declareFlags() as $flag) {
				if ($that->getFlag($flag)) {
					$this->setFlag($flag, true);
				}
			}
		} elseif ($print) {
			Debug::print("{$f} other datum does not have flags");
		}
		if ($that instanceof VirtualDatum) {
			$column_name = $that->getColumnName();
			if ($print) {
				Debug::print("{$f} other datum at index \"{$column_name}\" is virtual");
			}
		} elseif ($that->hasValue()) {
			$value = $that->getValue();
			if ($print) {
				Debug::print("{$f} setting value \"{$value}\"");
			}
			$this->setValue($value);
		} elseif ($print) {
			Debug::print("{$f} other datum does not have a value");
		}
		return SUCCESS;
	}

	/*
	 * public function getEncryptionScheme(){
	 * return $this->encryptionScheme;
	 * }
	 */
	public function setValueFromSuperglobalArray($value)
	{
		$parsed = $this->parseValueFromSuperglobalArray($value);
		return $this->setValue($parsed);
	}

	public function announceYourself()
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		Debug::print("{$f} my name is \"" . $this->getColumnName() . "\"");
	}

	public function getDataStructureKey()
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		try {
			if (! $this->hasDataStructure()) {
				Debug::error("{$f} row data object is undefined");
			}
			$obj = $this->getDataStructure();
			$key = $obj->getIdentifierValue();
			// Debug::print("{$f} returning \"{$key}\"");
			return $key;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function validate($v): int
	{
		$f = __METHOD__;
		$print = false;
		if ($this->hasValidationClosure()) {
			if ($print) {
				Debug::print("{$f} this datum has a validation closure");
			}
			$closure = $this->getValidationClosure();
			$status = $closure($v, $this);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} validation closure returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			}
		} elseif ($print) {
			Debug::print("{$f} this datum does not have a validation closure");
		}
		return static::validateStatic($v);
	}

	public function setOriginalValue($value)
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if (! $this->getRetainOriginalValueFlag()) {
			Debug::error("{$f} retain original value flag is undefined");
		}
		return $this->originalValue = $value;
	}

	public function getOriginalValue()
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if (! $this->getRetainOriginalValueFlag()) {
			$cn = $this->getColumnName();
			$dsc = $this->getDataStructureClass();
			Debug::error("{$f} column \"{$cn}\" from class \"{$dsc}\" does not retain its original value");
		} elseif (! $this->hasOriginalValue()) {
			return null;
		}
		return $this->originalValue;
	}

	public function hasOriginalValue()
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		$column_name = $this->getColumnName();
		$print = false;
		if ($this->getRetainOriginalValueFlag()) {
			if ($print) {
				Debug::print("{$f} retain original value flag is set");
				if (empty($this->originalValue)) {
					Debug::print("{$f} column \"{$column_name}\" does not have a value");
				} else {
					Debug::print("{$f} original value of column \"{$column_name}\" is \"{$this->originalValue}\"");
				}
			}
			return $this->originalValue !== null;
		} elseif ($print) {
			Debug::print("{$f} retain original value flag of column \"{$column_name}\" is not set, or original value is undefined");
		}
		return false;
	}

	public final function setValueFromQueryResult($raw)
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		try {
			$vn = $this->getColumnName();
			$print = false;
			if ($this instanceof VirtualDatum) {
				$ds = $this->getDataStructure();
				$dsc = $ds->getClass();
				Debug::error("{$f} data structure of class \"{$dsc}\" is storing a virtual datum at index \"{$vn}\"");
			}
			$value = $this->parseValueFromQueryResult($raw);
			if ($print) {
				Debug::print("{$f} parsed value \"{$value}\" from raw result \"{$raw}\"");
			}
			if ($this->getRetainOriginalValueFlag()) {
				if ($print) {
					Debug::print("{$f} retain original value flag is set");
				}
				$this->setOriginalValue($value);
			} elseif ($print) {
				Debug::print("{$f} retain original value flag is not set");
			}
			return $this->setValue($value);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function setRetainOriginalValueFlag($value = true)
	{
		return $this->setFlag(COLUMN_FILTER_RETAIN_ORIGINAL_VALUE, $value);
	}

	public function getRetainOriginalValueFlag()
	{
		return $this->getFlag(COLUMN_FILTER_RETAIN_ORIGINAL_VALUE);
	}

	public function retainOriginalValue(bool $value = true): Datum
	{
		$this->setRetainOriginalValueFlag($value);
		return $this;
	}

	public function getDataStructureClass()
	{
		if ($this->hasDataStructureClass()) {
			return $this->dataStructureClass;
		}
		return $this->getDataStructure()->getClass();
	}

	public function setSensitiveFlag($v)
	{
		return $this->setFlag(COLUMN_FILTER_SENSITIVE, $v);
	}

	public function getSensitiveFlag()
	{
		return $this->getFlag(COLUMN_FILTER_SENSITIVE);
	}

	/**
	 * if true, throw an error if a DataStructure is loaded without this datum's mandatory info defined
	 *
	 * @param boolean $mandatory
	 * @return boolean \/
	 *         public function setMandatoryOnLoad($mandatory){
	 *         return $this->setFlag('mandatoryOnLoad', $mandatory);
	 *         }
	 */
	public final function getDatabaseEncodedValue()
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		$name = $this->getColumnName();
		$print = false;
		if ($this->hasValue()) {
			if ($print) {
				Debug::print("{$f} value is defined for column \"{$name}\"");
			}
			$value = $this->getValue();
		} elseif ($this->hasDefaultValue()) {
			if ($print) {
				Debug::print("{$f} value is undefined for column \"{$name}\", but there is a default value");
			}
			$value = $this->getDefaultValue();
		} else {
			if ($print) {
				Debug::print("{$f} neither value nor default value are defined for column \"{$name}\"");
			}
			return null;
		}
		if ($print) {
			Debug::print("{$f} about to return encoding of value \"{$value}\" for column \"{$name}\"");
		}
		return static::getDatabaseEncodedValueStatic($value);
	}

	public static function getDatabaseEncodedValueStatic($value)
	{
		return $value;
	}

	public function getIdentifierValue()
	{
		return $this->getValue();
	}

	public function replicate()
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		$status = $this->beforeReplicateHook();
		if ($status !== SUCCESS) {
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
		if ($status !== SUCCESS) {
			$err = ErrorMessage::getResultMessage($status);
			Debug::warning("{$f} after replicate hook returned error status \"{$err}\"");
			$this->setObjectStatus($status);
			return null;
		}
		return $replica;
	}

	public function getSelectOperatorAndIncrement($value)
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		try {
			if ($value === null) {
				if (! $this->isNullable()) {
					$name = $this->getColumnName();
					Debug::error("{$f} this datum \"{$name}\" is not nullable");
				}
				$this->operatorCount ++;
				return OPERATOR_IS_NULL;
			}
			$this->operatorCount ++;
			return OPERATOR_EQUALS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * XXX what is this used for
	 *
	 * @param mixed $v
	 * @return NULL|ValueReturningCommandInterface
	 */
	public function setIdentifierValue($v)
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		Debug::printStackTrace("{$f} what is this used for?");
		return $this->setValue($v);
	}

	public function getUserWritableFlag()
	{
		return $this->getFlag("userWritable");
	}

	public function setUserWritableFlag($v)
	{
		return $this->setFlag("userWritable", $v);
	}

	public function cast($v)
	{
		return $v;
	}

	/**
	 * in the derived class this is used to initialize cryptographic keys and nonces that are randomized
	 *
	 * @return int
	 */
	public function initializeLinkedData()
	{
		return SUCCESS;
	}

	public function getCipherColumn(): ?CipherDatum
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		try {
			if (! $this->hasDataStructure()) {
				Debug::error("{$f} data structure is undefined");
			}
			$row = $this->getDataStructure();
			// $scheme_class = $this->getEncryptionScheme();
			// Debug::print("{$f} about to call {$scheme_class}::getCipherDatumIndex(this)");
			// $vn = $scheme_class::getCipherDatumIndex($this);
			$name = $this->getColumnName();
			$vn = "{$name}_cipher";
			// Debug::print("{$f} about to get datum for cipher \"{$vn}\"");
			// Debug::printStackTraceNoExit();
			$cipher = $row->getColumn($vn);
			return $cipher;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	private function pushMirrorIndex($column_name)
	{
		if (! is_array($this->mirrorIndices)) {
			$this->mirrorIndices = [];
		} elseif (array_key_exists($column_name, $this->mirrorIndices)) {
			return $column_name;
		}
		return $this->mirrorIndices[$column_name] = $column_name;
	}

	public function mirrorAtIndex($column_name)
	{
		$class = static::class;
		$mirror = new $class($column_name);
		$mirror->setPersistenceMode($this->getPersistenceMode());
		$this->pushMirrorIndex($column_name);
		return $mirror;
	}

	private function hasMirrorIndices()
	{
		return is_array($this->mirrorIndices) && ! empty($this->mirrorIndices);
	}

	private function setMirroredValues($v)
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		$print = false;
		if (! $this->hasMirrorIndices()) { // empty($this->mirrorIndices)){
			if ($print) {
				Debug::print("{$f} mirrorIndices is empty");
			}
			return $v;
		}
		$ds = $this->getDataStructure();
		foreach ($this->mirrorIndices as $column_name) {
			if ($print) {
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
		$this->dispatchEvent(new BeforeUnsetValueEvent($force));
		return SUCCESS;
	}

	public function unsetValue(bool $force = false): int{
		$f = __METHOD__;
		try {
			$column_name = $this->getColumnName();
			$print = false;
			$status = $this->beforeUnsetValueHook($force);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} beforeUnsetValueHook returned error status \"{$err}\"");
				return $this->setObjectStatus($status);
			} elseif ($force) {
				if ($print) {
					Debug::print("{$f} ignoring nullability");
				}
			} elseif (! $this->isNullable() || ($this->hasValue() && ! $this->isRewritable())) {
				if ($print) {
					Debug::print("{$f} column \"{$column_name}\" is not nullable");
				}
				return FAILURE;
			} elseif ($print) {
				Debug::print("{$f} this column is nullable");
			}
			if ($this->hasValue()) {
				if ($print) {
					Debug::printStackTraceNoExit("{$f} column \"{$column_name}\" has a value, unsetting it now");
				}
				unset($this->value); // $this->setValue(null);
				$persist_mode = $this->getPersistenceMode();
				switch ($persist_mode) {
					case PERSISTENCE_MODE_COOKIE:
						if (headers_sent()) {
							Debug::warning("{$f} headers already sent somehow");
						} else {
							setcookie($column_name, null, time() - 3600);
						}
						unset($_COOKIE[$column_name]); // array_remove_key($_COOKIE, $column_name);
						if (array_key_exists($column_name, $_COOKIE)) {
							Debug::error("{$f} use array_remove_key");
						}
						break;
					case PERSISTENCE_MODE_ENCRYPTED:
						$this->getCipherColumn()->unsetValue($force);
						// XXX don't have a way to determine nonce column name from here, but it really doesn't matter
						break;
					case PERSISTENCE_MODE_SESSION:
						unset($_SESSION[$column_name]); // array_remove_key($_SESSION, $column_name);
						if (array_key_exists($column_name, $_SESSION)) {
							Debug::error("{$f} use array_remove_key");
						} elseif ($this->hasValue()) {
							$v = $this->getValue();
							Debug::warning("{$f} before even getting through the switch statement, value of column \"{$column_name}\" is still defined as \"{$v}\"");
							Debug::printSessionHash();
							Debug::printStackTrace();
						} elseif ($print) {
							Debug::print("{$f} unset seems to have worked");
						}
						break;
					default:
						break;
				}
				if ($this->hasValue()) {
					$v = $this->getValue();
					Debug::error("{$f} before exiting this function, value of column \"{$column_name}\" is still defined as \"{$v}\"");
				} elseif ($print) {
					Debug::print("{$f} this function seems to have worked");
				}
				$this->setUpdateFlag(true);
				$this->afterUnsetValueHook($force);
				return SUCCESS;
			} elseif ($print) {
				Debug::print("{$f} column \"{$column_name}\" is already valueless");
			}
			return STATUS_UNCHANGED;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function afterUnsetValueHook($force = false){
		$this->dispatchEvent(new AfterUnsetValueEvent($force));
		return SUCCESS;
	}

	public function setValue($v){
		$f = __METHOD__;
		try {
			$vn = $this->getColumnName();
			$print = $this->getDebugFlag();
			if ($v instanceof ValueReturningCommandInterface) {
				while ($v instanceof ValueReturningCommandInterface) {
					$v = $v->evaluate();
				}
			}
			if ($print) {
				if ($this->hasDataStructure()) {
					$ds = $this->getDataStructure();
					$dsc = $ds->getClass();
					Debug::print("{$f} data structure class is \"{$dsc}\"");
				}
			}
			$this->dispatchEvent(new BeforeSetValueEvent($v));
			$event = new AfterSetValueEvent($v);
			if (! $this->isRewritable() && $this->hasValue()) {
				Debug::print("{$f} sorry, this datum is non-rewritable");
				$this->dispatchEvent($event);
				return null;
			}
			$receptivity = $this->hasDataStructure() ? $this->getDataStructure()->getReceptivity() : DATA_MODE_DEFAULT;
			if ($this->getSealedFlag()) {
				Debug::error("{$f} datum is sealed");
				return null;
			} elseif ($this->hasApoptoticSignal()) {
				$signal = $this->getApoptoticSignal();
				if ($signal === $v) {
					if ($print) {
						Debug::print("{$f} value matches apoptotic signal \"{$signal}\"");
					}
					$this->getDataStructure()->apoptose($this);
					if ($this->hasValue()) {
						$this->unsetValue(true);
					}
					return $v;
				} elseif ($print) {
					Debug::print("{$f} value does not match apoptotic signal");
				}
			} elseif ($print) {
				Debug::print("{$f} this column does not have an apoptotic signal");
			}
			if (! $this->hasDataStructure()) {
				if ($print) {
					Debug::print("{$f} data structure is undefined");
				}
			} elseif ($receptivity !== DATA_MODE_RECEPTIVE && $this->getDataStructure()->isUninitialized()) {
				if ($print) {
					Debug::print("{$f} data structure is uninitialized");
				}
			} elseif ((! $this->hasValue() && $v !== null) || ($this->hasValue() && $this->value !== $v)) {
				if ($print) {
					Debug::print("{$f} new value \"{$v}\" differs from existing one \"{$this->value}\" -- setting update flag. Receptivity is {$receptivity}");
				}
				$this->setUpdateFlag(true);
			} elseif ($print) {
				Debug::print("{$f} new value \"{$v}\" is the same as the old one \"{$this->value}\"");
			}
			$mode = $this->getPersistenceMode();
			switch ($mode) {
				case (PERSISTENCE_MODE_COOKIE):
					if ($print) {
						Debug::print("{$f} datum \"{$vn}\" is stored in cookies");
					}
					$encoded = static::getDatabaseEncodedValueStatic($v);
					if (headers_sent()) {
						Debug::error("{$f} cannot set cookie for column {$vn} -- headers already sent somehow");
						$_COOKIE[$vn] = $encoded;
					} else {
						set_secure_cookie($vn, $encoded);
					}
					break;
				case (PERSISTENCE_MODE_SESSION):
					if ($print) {
						Debug::print("{$f} datum \"{$vn}\" is stored in session memory; setting it to \"{$v}\"");
					}
					$_SESSION[$vn] = $v;
					$this->setMirroredValues($v);
					break;
				case PERSISTENCE_MODE_ENCRYPTED:
					if ($print) {
						Debug::print("{$f} column \"{$vn}\" is encrypted");
					}
					if ($v === null) {
						if ($print) {
							Debug::print("{$f} value is null; not going to encrypt an empty string just to waste space");
						}
						$this->value = $this->setMirroredValues($v);
						$this->dispatchEvent($event);
						return $this->value;
					} elseif ($receptivity === DATA_MODE_PASSIVE) {
						if ($print) {
							Debug::print("{$f} we are not going to do any encrypting because data mode is passive");
						}
						$this->value = $this->setMirroredValues($v);
						$this->dispatchEvent($event);
						return $this->value;
					} elseif ($print) {
						Debug::print("{$f} value is not null and data mode is not passive ({$receptivity}) -- about to encrypt this sumbitch");
					}
					$cipher = $this->getCipherColumn();
					$scheme_class = $this->getEncryptionScheme();
					$scheme = new $scheme_class();
					switch ($receptivity) {
						case DATA_MODE_RECEPTIVE:
							if ($print) {
								$dsc = $this->getDataStructureClass();
								Debug::print("{$f} about to generate encryption key and nonce with scheme \"{$scheme_class}\" for datum \"{$vn}\" in a data structure of class \"{$dsc}\"; line 1166");
							}
							$key = $scheme->generateEncryptionKey($this);
							$nonce = $scheme->generateNonce($this);
							break;
						default:
							if ($print) {
								Debug::print("{$f} about to extract key and nonce with encryption scheme \"{$scheme_class}\" for datum \"{$vn}\"");
							}
							$key = $scheme->extractEncryptionKey($this);
							$nonce = $scheme->extractNonce($this);
							break;
					}
					$encoded = static::getDatabaseEncodedValueStatic($v);
					$encrypted = $scheme->encrypt($encoded, $key, $nonce);
					if ($print && $scheme instanceof SymmetricEncryptionScheme) {}
					$cipher->setValue($encrypted);
					break;
				default:
					if ($print) {
						Debug::print("{$f} datum \"{$vn}\" has storage mode \"{$mode}\"");
					}
					break;
			}
			if ($print) {
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
			$this->dispatchEvent($event);
			return $this->value;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function generateComponent($subindex, $compound_name, ...$args){
		$vn = new ConcatenateCommand($compound_name, "[{$subindex}]");
		$class = static::class;
		$args = [];
		foreach ($args as $arg) {
			array_push($args, $arg);
		}
		return new $class($vn, $vn, ...$args);
	}

	public function getValue(){
		$f = __METHOD__;
		try {
			$column_name = $this->getColumnName();
			$mode = $this->getPersistenceMode();
			$print = false;
			$mode = $this->getPersistenceMode();
			if (isset($this->value)) {
				if ($print) {
					$gottype = gettype($this->value);
					Debug::print("{$f} value \"".base64_encode($this->value)."\" of type {$gottype} is already set for datum \"{$column_name}\"");
					if (is_bool($this->value)) {
						if ($this->value === true) {
							Debug::print("{$f} value is true");
						} else {
							Debug::print("{$f} value is false");
						}
					}
				}
				return $this->value;
			}
			switch ($mode) {
				case PERSISTENCE_MODE_COOKIE:
					if ($print) {
						Debug::print("{$f} about to get datum \"{$column_name}\" from cookies");
					}
					if (isset($_COOKIE[$column_name])) {
						$value = $this->parseValueFromQueryResult($_COOKIE[$column_name]);
						if ($print) {
							Debug::print("{$f} returning cookie value \"{$value}\"");
						}
						return $value;
					}
					break;
				case PERSISTENCE_MODE_SESSION:
					if ($print) {
						Debug::print("{$f} about to get datum \"{$column_name}\" from session");
					}
					if (isset($_SESSION[$column_name])) {
						$value = $_SESSION[$column_name];
						if ($print) {
							Debug::print("{$f} returning session value \"{$value}\"");
						}
						return $value;
					}
					if ($print) {
						Debug::print("{$f} session datum \"{$column_name}\" is undefined");
					}
					break;
				case PERSISTENCE_MODE_ENCRYPTED:
					if (! $this->hasDataStructure()) {
						Debug::error("{$f} data structure for column \"{$column_name}\" is undefined");
					}
					$cipher = $this->getCipherColumn();
					if (! $cipher->hasValue()) {
						break;
					}
					$scheme_class = $this->getEncryptionScheme();
					if ($print) {
						Debug::print("{$f} encryption scheme class is \"{$scheme_class}\"");
					}
					$scheme = new $scheme_class();
					if ($print) {
						$dsc = $this->getDataStructureClass();
						$dsk = $this->getDataStructureKey();
						$did = $this->getDataStructure()->getDebugId();
						Debug::print("{$f} about to call {$scheme_class}->extractDecryptionKey(this) for column \"{$column_name}\" of data structure of class \"{$dsc}\" with key \"{$dsk}\" and debug Id \"{$did}\"");
					}
					$key = $scheme->extractDecryptionKey($this);
					$ds = $this->getDataStructure();
					if (empty($key)) {
						Debug::warning("{$f} decryption key is undefined for datum \"{$column_name}\"");
						if ($scheme instanceof SharedEncryptionSchemeInterface && ! $ds->getReplacementKeyRequested()) {
							if ($print) {
								Debug::print("{$f} about to request replacement key");
							}
							$ds->requestReplacementDecryptionKey();
						} elseif ($print) {
							Debug::print("{$f} scheme \"{$scheme_class}\" is not shared, or data structure has already requested a replacement key");
						}
						break;
					} elseif ($scheme instanceof SharedEncryptionSchemeInterface && $ds->getReplacementKeyRequested()) {
						if ($print) {
							Debug::print("{$f} fulfilling request for replacing encryption key");
						}
						$ds->fulfillReplacementKeyRequest();
					}
					$nonce = $scheme->extractNonce($this);
					$raw = $scheme->decrypt($cipher->getValue(), $key, $nonce);
					if ($print && empty($raw)) {
						Debug::error("{$f} raw decrypted value of column \"{$column_name}\" is empty");
					} elseif ($print) {
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
			if ($this->hasDefaultValue()) {
				return $this->getDefaultValue();
			}
			return null;
		} catch (Exception $x) {
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

	public function getNeverLeaveServer(){
		return $this->getFlag('neverLeaveServer');
	}

	public function setNeverLeaveServer($ever){
		return $this->setFlag('neverLeaveServer', $ever);
	}

	public function compareExistingValue($value){
		$f = __METHOD__;
		$print = $this->getDebugFlag();
		$old = $this->getValue();
		if ($print) {
			$name = $this->getColumnName();
			Debug::print("{$f} old value of datum \"{$name}\" is \"{$old}\"");
		}
		if ($old === null) {
			if ($value === null || is_string($value) && $value === "") {
				if ($print) {
					Debug::print("{$f} old and new values are both null/empty string");
				}
				return STATUS_UNCHANGED;
			} elseif ($print) {
				Debug::print("{$f} old value is null, new value ain't");
			}
			return SUCCESS;
		} elseif ($value === null) {
			if ($print) {
				Debug:
				print("{$f} old value is \"{$old}\", but new value is null");
			}
			return SUCCESS;
		}
		if (is_string($old) && is_string($value)) {
			if (strcmp($old, $value) === 0) {
				if ($print) {
					Debug::print("{$f} strcmp({$old}, {$value}) returned true");
				}
				return STATUS_UNCHANGED;
			} elseif (trim($old) === trim($value)) {
				if ($print) {
					Debug::print("{$f} they match once trimmed");
				}
				return STATUS_UNCHANGED;
			} elseif ($print) {
				Debug::print("{$f} strcmp returned false");
			}
		} elseif ($old === $value) {
			if ($print) {
				Debug::print("{$f} value \"{$value}\" has not changed");
			}
			return STATUS_UNCHANGED;
		} elseif ($this instanceof FloatingPointDatum) {
			if (close_enough($old, $value)) {
				if ($print) {
					Debug::print("{$f} floating point comparison is close enough");
				}
				return STATUS_UNCHANGED;
			}
		}
		if ($print) {
			$old_type = gettype($old);
			$new_type = gettype($value);
			Debug::print("{$f} {$old_type} value has changed from \"{$old}\" to {$new_type} \"{$value}\"");
		}
		$old_hash = sha1($old);
		$new_hash = sha1($value);
		if ($old_hash === $new_hash) {
			Debug::warning("{$f} value has changed, but hash has not somewhow");
			$old_hex = bin2hex($old);
			$new_hex = bin2hex($value);
			Debug::warning("{$f} old value in hex is \"{$old_hex}\"");
			Debug::error("{$f} new value in hex is \"{$new_hex}\"");
		} elseif ($print) {
			Debug::print("{$f} old hash is \"{$old_hash}\"; new hash is \"{$new_hash}\"");
		}
		return SUCCESS;
	}

	public function getValidators($value){
		return [
			new DatumValidator($this, $value)
		];
	}

	public function hasValue(){
		$f = __METHOD__;
		$vn = $this->getColumnName();
		$print = false;
		switch ($this->getPersistenceMode()) {
			case PERSISTENCE_MODE_ENCRYPTED:
				// $value = $this->getValue();
				if ($print) {
					Debug::print("{$f} this datum is encrypted");
				}
				return $this->getCipherColumn()->hasValue(); // $value !== null && $value !== "" && strlen($value) > 0;
			case PERSISTENCE_MODE_COOKIE:
				if ($print) {
					Debug::print("{$f} value is stored in cookies");
				}
				return array_key_exists($vn, $_COOKIE) && $_COOKIE[$vn] !== null && $_COOKIE[$vn] !== "";
			case PERSISTENCE_MODE_SESSION:
				if ($print) {
					Debug::print("{$f} value is stored in session");
					if (isset($_SESSION)) {
						Debug::print("{$f} session is defined");
					} else {
						Debug::print("{$f} session is undefined");
					}
					if (is_array($_SESSION)) {
						Debug::print("{$f} session is an array");
					} else {
						Debug::print("{$f} session is not an array");
					}
					if (! empty($_SESSION)) {
						Debug::print("{$f} session is not empty");
					} else {
						Debug::print("{$f} session is empty");
					}
					if (array_key_exists($vn, $_SESSION)) {
						Debug::print("{$f} key \"{$vn}\" exists in session");
					} else {
						Debug::print("{$f} key \"{$vn}\" does not exist in session");
					}
					if ($_SESSION[$vn] !== null) {
						Debug::print("{$f} key \"{$vn}\" has maps to a non-null value");
					} else {
						Debug::print("{$f} key \"{$vn}\" maps to a null value");
					}
					if ($_SESSION[$vn] !== "") {
						Debug::print("{$f} key \"{$vn}\" maps to something other than an empty string");
					} else {
						Debug::print("{$f} key \"{$vn}\" maps to an empty string");
					}
					// Debug::printSession();
				}
				return isset($_SESSION) && is_array($_SESSION) && ! empty($_SESSION) && array_key_exists($vn, $_SESSION) && $_SESSION[$vn] !== null && $_SESSION[$vn] !== "";
			case PERSISTENCE_MODE_DATABASE:
			case PERSISTENCE_MODE_VOLATILE:
			default:
				if ($print) {
					Debug::print("{$f} default case");
					if ($this->value !== null && $this->value !== "") {
						Debug::print("{$f} yes, value of \"{$vn}\" is non-null and not empty string");
					} else {
						Debug::print("{$f} no, value of \"{$vn}\" is null or empty string");
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
		try {
			$vn = $this->getColumnName();
			$print = false;
			if ($print) {
				$ic = $input->getShortClass();
				Debug::print("{$f} about to call {$ic}->negotiateValue");
				$input->debug();
			}
			$negotiated = $input->negotiateValue($this);
			$value = $this->cast($negotiated);
			if ($print) {
				Debug::print("{$f} cast negotiated value \"{$negotiated}\" into \"{$value}\" for datum \"{$vn}\"");
			}
			if ($this->hasApoptoticSignal() && $this->getApoptoticSignal() === $value) {
				if ($print) {
					Debug::print("{$f} value is the apoptotic signal, doesn't matter if it's valid");
				}
				$status = $this->setObjectStatus(STATUS_UNCHANGED);
				$this->getDataStructure()->apoptose($this); // must set this here -- needed in processForm to determine which status code to return
			} else {
				if ($print) {
					if (! $this->hasApoptoticSignal()) {
						Debug::print("{$f} there is no apoptotic signal");
					} else {
						$apop = $this->getApoptoticSignal();
						if ($apop !== $value) {
							Debug::print("{$f} apoptotic signal \"{$apop}\" does not match value \"{$value}\"");
						} else {
							Debug::error("{$f} apoptotic signal matches");
						}
					}
				}
				if(!$this->getAlwaysValidFlag()){
					$status = $this->validate($value);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::print("{$f} validation of datum \"{$vn}\" returned error status \"{$err}\"");
						return $this->setObjectStatus($status);
					} elseif ($print) {
						Debug::print("{$f} validation successful");
					}
				}elseif($print){
					Debug::print("{$f} skipping validation");
				}
				$status = $this->compareExistingValue($value);
				switch ($status) {
					case SUCCESS:
						if ($print) {
							Debug::print("{$f} value \"{$value}\" has changed for datum \"{$vn}\"");
						}
						break;
					case STATUS_UNCHANGED:
						if ($print) {
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
			if ($print) {
				Debug::print("{$f} about to assign value to field \"{$vn}\"");
			}
			$value = $this->setValue($value);
			if ($print) {
				Debug::print("{$f} assigned value \"{$value}\" to field \"{$vn}\"");
			}
			return $status;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getAdminInterfaceFlag(){
		return $this->getFlag("adminInterface");
	}

	public function setAdminInterfaceFlag($value){
		return $this->setFlag("adminInterface", $value);
	}

	public function getSearchIndex(){
		return $this->getColumnName();
	}

	public function isSortable(){
		return $this->getFlag(COLUMN_FILTER_SORTABLE);
	}

	public function setSortable($value = true){
		return $this->setFlag(COLUMN_FILTER_SORTABLE, $value);
	}

	public function setGeneratedAlwaysAsExpression($expression){
		if ($expression == null) {
			unset($this->generatedAlwaysAsExpression);
			return null;
		}
		return $this->generatedAlwaysAsExpression = $expression;
	}

	public function hasGeneratedAlwaysAsExpression()
	{
		return isset($this->generatedAlwaysAsExpression);
		// && $this->generatedAlwaysAsExpression instanceof ExpressionCommand;
	}

	public function getGeneratedAlwaysAsExpression(){
		$f = __METHOD__;
		if (! $this->hasGeneratedAlwaysAsExpression()) {
			Debug::error("{$f} generated always as expression is undefined");
		}
		return $this->generatedAlwaysAsExpression;
	}

	public function generatedAlwaysAs($expression)
	{
		$this->setGeneratedAlwaysAsExpression($expression);
		return $this;
	}

	public function setColumnFormat($type){
		$f = __METHOD__;
		if ($type == null) {
			unset($this->columnFormatType);
			return null;
		} elseif (! is_string($type)) {
			Debug::error("{$f} input parameter must be a string");
		}
		$type = strtolower($type);
		switch ($type) {
			case COLUMN_FORMAT_DEFAULT:
			case COLUMN_FORMAT_DYNAMIC:
			case COLUMN_FORMAT_FIXED:
				break;
			default:
				Debug::error("{$f} invalid column format \"{$type}\"");
		}
		return $this->columnFormatType = $type;
	}

	public function hasColumnFormat(){
		return isset($this->columnFormatType);
	}

	public function getColumnFormat(){
		$f = __METHOD__;
		if (! $this->hasColumnFormat()) {
			Debug::error("{$f} column format is undefined");
		}
		return $this->columnFormatType;
	}

	public function columnFormat($type){
		$this->setColumnFormat($type);
		return $this;
	}

	public function setDatabaseStorage($type){
		$f = __METHOD__;
		if ($type == null) {
			unset($this->databaseStorageType);
			return null;
		} elseif (! is_string($type)) {
			Debug::error("{$f} database storage type must be a string");
		}
		$type = strtolower($type);
		switch ($type) {
			case DATABASE_STORAGE_DISK:
			case DATABASE_STORAGE_MEMORY:
				if ($this->hasGeneratedAlwaysExpression()) {
					Debug::error("{$f} generated columns cannot specify storage to disk or memory");
				}
			case DATABASE_STORAGE_GENERATED_STORED:
			case DATABASE_STORAGE_GENERATED_VIRTUAL:
				// allowed regardless of generated always expression which may be set after this variable
				break;
			default:
				Debug::error("{$f} invalid database storage type \"{$type}\"");
		}
		return $this->databaseStorageType = $type;
	}

	public function hasDatabaseStorage(){
		return isset($this->databaseStorageType);
	}

	public function getDatabaseStorage(){
		$f = __METHOD__;
		if (! $this->hasDatabaseStorage()) {
			Debug::error("{$f} database storage is undefined");
		}
		return $this->databaseStorageType;
	}

	public function databaseStorage($type){
		$this->setDatabaseStorage($type);
		return $type;
	}

	public function withDefaultValue($value){
		$this->setDefaultValue($value);
		return $this;
	}

	/**
	 * Generate an IndexDefinition object for this datum.
	 * XXX IndexDefinitions have engine attributes, key block size, visibility and comments,
	 * but they are not necessarily the same as the ones declared for this column.
	 * They also have a parser name for full text indices.
	 *
	 * @return IndexDefinition
	 */
	public function generateIndexDefinition()
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		try {
			$cn = $this->getColumnName();
			if ($this->hasIndexName()) {
				$name = $this->getIndexName();
			} else {
				$ds = $this->getDataStructure();
				$type = $ds->getTableName();
				$name = "{$type}_{$cn}_index";
			}
			$index = new IndexDefinition($name);
			if ($this->hasIndexType()) {
				$index->setIndexType($this->getIndexType());
			}
			if ($this instanceof StringDatum) {
				$length = $this->getMaximumLength();
			} else {
				$length = null;
			}
			$keypart = new KeyPart($cn, $length);
			$index->setKeyParts([
				$keypart
			]);
			if ($this->isPrimaryKey()) {
				return new PrimaryKeyConstraint($index);
			} elseif ($this->getUniqueFlag()) {
				return new UniqueConstraint($index);
			}
			return $index;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function canHaveDefaultValue(): bool
	{
		return ! ($this instanceof BlobDatum || $this instanceof JsonDatum || $this instanceof TextDatum || $this instanceof GeometryDatum);
	}

	// XXX missing reference definition
	public function toSQL(): string
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		try {
			$print = false;
			$string = back_quote($this->getColumnName()) . " " . $this->getColumnTypeString();
			if ($this->hasGeneratedAlwaysAsExpression()) {
				if ($print) {
					Debug::print("{$f} this column has a generated always as expression");
				}
				// [COLLATE collation_name]
				if ($this->hasCollationName()) {
					$string .= " collate " . $this->getCollationName();
				}
				// [GENERATED ALWAYS] AS (expr)
				$expr = $this->getGeneratedAlwaysAsExpression();
				if ($expr instanceof SQLInterface) {
					$expr = $expr->toSQL();
				}
				$string .= " as ({$expr})";
				// [VIRTUAL | STORED] [NOT NULL | NULL]
				if ($this->hasDatabaseStorage()) {
					$string .= " " . $this->getDatabaseStorage();
					if (! $this->isNullable()) {
						$string .= " not null";
					}
				}
				// [VISIBLE | INVISIBLE]
				if ($this->hasVisibility()) {
					$string .= " " . $this->getVisibility();
				}
				// [UNIQUE [KEY]] [[PRIMARY] KEY]
				if ($this->getUniqueFlag()) {
					$string .= " unique";
				}
				if ($this->isPrimaryKey()) {
					$string .= " primary key";
				}
				// [COMMENT 'string']
				if ($this->hasComment()) {
					$string .= " comment " . single_quote($this->getComment());
				}
			} else {
				if ($print) {
					Debug::print("{$f} generated always as expressions is undefined");
				}
				// data_type [NOT NULL | NULL] [DEFAULT {literal | (expr)} ]
				if (! $this->isNullable()) {
					$string .= " not null";
				}
				if ($this->hasDefaultValue() && $this->canHaveDefaultValue()) {
					$string .= " default " . $this->getDefaultValueString();
				}
				// [VISIBLE | INVISIBLE]
				if ($this->hasVisibility()) {
					$string .= " " . $this->getVisibility();
				}
				// [AUTO_INCREMENT] [UNIQUE [KEY]] [[PRIMARY] KEY]
				if ($this instanceof IntegerDatum && $this->getAutoIncrementFlag()) {
					$string .= " auto_increment";
				}
				if ($this->getUniqueFlag()) {
					$string .= " unique";
				}
				if ($this->isPrimaryKey()) {
					$string .= " primary key";
				}
				// [COMMENT 'string']
				if ($this->hasComment()) {
					$string .= " comment " . single_quote($this->getComment());
				}
				// [COLLATE collation_name]
				if ($this->hasCollationName()) {
					$string .= " collate " . $this->getCollationName();
				}
				// [COLUMN_FORMAT {FIXED | DYNAMIC | DEFAULT}]
				if ($this->hasColumnFormat()) {
					$string .= " column_format " . $this->getColumnFormat();
				}
				// [ENGINE_ATTRIBUTE [=] 'string']
				if ($this->hasEngineAttribute()) {
					$string .= " engine_attribute " . single_quote($this->getEngineAttribute());
					// [SECONDARY_ENGINE_ATTRIBUTE [=] 'string']]
					if ($this->hasSecondaryEngineAttribute()) {
						$string .= " secondary_engine_attribute " . single_quote($this->getSecondaryEngineAttribute());
					}
				}
				// [STORAGE {DISK | MEMORY}]
				if ($this->hasDatabaseStorage()) {
					$string .= " storage " . $this->getDatabaseStorage();
				}
			}
			if (false && $this->hasReferenceDefinition()) {
				// [reference_definition]
				// XXX TODO unimplemented
			}
			if ($this->hasConstraints()) {
				// [check_constraint_definition]
				foreach ($this->getConstraints() as $constraint) {
					if (! $constraint instanceof CheckConstraint) {
						continue;
					}
					$string .= " {$constraint}";
				}
			}
			return $string;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function isComparable()
	{
		return true;
	}

	public function isRewritable()
	{
		return $this->getFlag(COLUMN_FILTER_REWRITABLE);
	}

	public function setRewritableFlag($value = true)
	{
		return $this->setFlag(COLUMN_FILTER_REWRITABLE, $value);
	}

	/**
	 * returns true if this column satisfies the requirements of the given filters, false otherwise
	 *
	 * @param string[] $filters
	 * @return boolean
	 */
	public function applyFilter(...$filters): bool
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		try {
			$column_name = $this->getColumnName();
			// $ds = $this->getDataStructure();
			$pm = $this->getPersistenceMode();
			$print = false;
			if (count($filters) === 1 && is_array($filters[0])) {
				$filters = $filters[0];
			}
			foreach ($filters as $filter) {
				if (! is_string($filter)) {
					Debug::error("{$f} filter name must be a string");
				} elseif ($print) {
					Debug::print("{$f} entered for filter \"{$filter}\"");
				}
				$negate = false;
				if (starts_with($filter, '!')) { // XXX trim all '!!' occurences
					if (starts_with($filter, "!!")) {
						Debug::error("{$f} please don't double negate filter names you prick");
					} elseif ($print) {
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
				switch ($filter) {
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
						if ($print) {
							if ($this->applyFilter(COLUMN_FILTER_FOREIGN)) {
								Debug::print("{$f} column \"{$column_name}\" is a foreign column");
								if ($this->applyFilter(COLUMN_FILTER_DECLARED)) {
									Debug::print("{$f} column \"{$column_name}\" has the declared flag set, whatever that is");
									if ($this->getRelativeSequence() === CONST_BEFORE) {
										Debug::print("{$f} the foreign data structure at column \"{$column_name}\" must be inserted/updated before the host. Filter satisfied.");
									} else {
										Debug::print("{$f} Filter failed. The foreign data structure at column \"{$column_name}\" is inserted/updated after the host");
									}
								} elseif ($print) {
									Debug::print("{$f} Filter failed. Column \"{$column_name}\" does not have the declared flag set");
								}
							} elseif ($print) {
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
						if ($this->getDataStructure() instanceof EmbeddedData) {
							$pass = ! $this instanceof VirtualDatum && ($pm === PERSISTENCE_MODE_EMBEDDED || $pm === PERSISTENCE_MODE_DATABASE);
						} else {
							$pass = ! $this instanceof VirtualDatum && $pm === PERSISTENCE_MODE_DATABASE;
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
						$pass = $this->getFlag(COLUMN_FILTER_DIRTY_CACHE);
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
						if (! $this->hasDataStructure()) {
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
						if (! $this->applyFilter(COLUMN_FILTER_FOREIGN)) {
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
						if (true || $print) {
							if ($pass) {
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
						if (is_a($filter, Datum::class, true)) {
							if ($print) {
								Debug::print("{$f} filter is a datum class");
								if ($this instanceof $filter) {
									Debug::print("{$f} yes, this is a {$filter}");
								}
							}
							$pass = $this instanceof $filter;
							break;
						}
						Debug::error("{$f} invalid filter \"{$filter}\"");
				}
				if ($pass) {
					if ($print) {
						Debug::print("{$f} filter \"{$filter}\" satisfied");
					}
					if ($negate) {
						if ($print) {
							Debug::print("{$f} negation in effect, returning false");
						}
						return false;
					}
					continue;
				} else {
					if ($print) {
						Debug::print("{$f} filter \"{$filter}\" failed for column \"{$column_name}\"");
					}
					if ($negate) {
						if ($print) {
							Debug::print("{$f} negation in effect, continuing");
						}
						continue;
					}
					return false;
				}
			}
			if ($print) {
				Debug::print("{$f} all filters satisfied");
			}
			return true;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function seal(bool $value = true): Datum
	{
		$this->setSealedFlag($value);
		return $this;
	}

	public function setSealedFlag(bool $value = true): bool
	{
		return $this->setFlag(COLUMN_FILTER_SEALED, $value);
	}

	public function getSealedFlag(): bool
	{
		return $this->getFlag(COLUMN_FILTER_SEALED);
	}

	public function hasAliasExpression(): bool
	{
		return isset($this->aliasExpression) && (is_string($this->aliasExpression) || $this->aliasExpression instanceof SelectStatement);
	}

	public function setAliasExpression($st)
	{
		if ($st == null) {
			unset($this->aliasExpression);
			return null;
		}
		$this->setPersistenceMode(PERSISTENCE_MODE_ALIAS);
		return $this->aliasExpression = $st;
	}

	public function alias($st): Datum
	{
		$this->setAliasExpression($st);
		return $this;
	}

	public function hasSubqueryWhereCondition(): bool
	{
		return isset($this->subqueryWhereCondition) && (is_string($this->subqueryWhereCondition) || $this->subqueryWhereCondition instanceof WhereConditionalInterface || $this->subqueryWhereCondition instanceof BinaryExpressionCommand);
	}

	public function getSubqueryWhereCondition()
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if (! $this->hasSubqueryWhereCondition()) {
			$name = $this->getColumnName();
			$decl = $this->getDeclarationLine();
			Debug::error("{$f} subquery where condition is undefined for coulmn \"{$name}\", declared {$decl}");
		}
		return $this->subqueryWhereCondition;
	}

	public function setSubqueryWhereCondition($where)
	{
		if ($where == null) {
			unset($this->subqueryWhereCondition);
			return null;
		}
		$this->setPersistenceMode(PERSISTENCE_MODE_ALIAS);
		return $this->subqueryWhereCondition = $where;
	}

	public function hasSubqueryColumnName(): bool
	{
		return isset($this->subqueryColumnName) && is_string($this->subqueryColumnName) && ! empty($this->subqueryColumnName);
	}

	public function setSubqueryColumnName(?string $sqcn): ?string
	{
		if ($sqcn === null) {
			unset($this->subqueryColumnName);
			return null;
		}
		return $this->subqueryColumnName = $sqcn;
	}

	public function getSubqueryColumnName(): string
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		$print = false;
		if (! $this->hasSubqueryColumnName()) {
			if ($print) {
				Debug::warning("{$f} subquery column name is undefined, assuning it's the same at this column's name");
			}
			return $this->getColumnName();
		}
		return $this->subqueryColumnName;
	}

	public function setSubqueryExpression($expr)
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->setSubqueryExpression()";
		if ($expr == null) {
			unset($this->subqueryExpression);
			return null;
		}
		$this->setPersistenceMode(PERSISTENCE_MODE_ALIAS);
		return $this->subqueryExpression = $expr;
	}

	public function hasSubqueryExpression(): bool
	{
		return isset($this->subqueryExpression) && (is_string($this->subqueryExpression) || $this->subqueryExpression instanceof Command);
	}

	public function getSubqueryExpression()
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		$print = false;
		$name = $this->getColumnName();
		if (! $this->hasSubqueryExpression()) {
			if ($this->hasSubqueryTableName() || $this->hasSubqueryClass()) {
				if ($print) {
					Debug::print("{$f} subquery table name or class is defined");
				}
				return new ColumnAlias(new ColumnAliasExpression($this->getSubqueryTableAlias(), $this->getSubqueryColumnName()), $name);
			}
			Debug::error("{$f} subquery expression and table name are undefined for column \"{$name}\"");
		} elseif ($print) {
			Debug::print("{$f} subquery expression was already assigned");
		}
		return $this->subqueryExpression;
	}

	public function setSubqueryClass(?string $class): ?string
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if ($class == null) {
			unset($this->subqueryClass);
			return null;
		} elseif (! class_exists($class)) {
			Debug::error("{$f} class \"{$class}\" does not exist");
		}
		return $this->subqueryClass = $class;
	}

	public function hasSubqueryClass(): bool
	{
		return isset($this->subqueryClass) && is_string($this->subqueryClass) && ! empty($this->subqueryClass) && class_exists($this->subqueryClass);
	}

	public function getSubqueryClass(): string
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if (! $this->hasSubqueryClass()) {
			Debug::error("{$f} subquery class is undefined");
		}
		return $this->subqueryClass;
	}

	public function hasSubqueryDatabaseName(): bool
	{
		return isset($this->subqueryDatabaseName) && is_string($this->subqueryDatabaseName);
	}

	public function setSubqueryDatabaseName(?string $db): ?string
	{
		if ($db == null) {
			unset($this->subqueryDatabaseName);
			return null;
		}
		return $this->subqueryDatabaseName = $db;
	}

	public function getSubqueryDatabaseName(): string
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if ($this->hasSubqueryDatabaseName()) {
			return $this->subqueryDatabaseName;
		} elseif ($this->hasSubqueryClass()) {
			return $this->getSubqueryClass()::getDatabaseNameStatic();
		}
		Debug::error("{$f} subquery database name and class are undefined");
	}

	public function hasSubqueryTableName(): bool
	{
		return isset($this->subqueryTableName) && is_string($this->subqueryTableName);
	}

	public function setSubqueryTableName(?string $db): ?string
	{
		if ($db == null) {
			unset($this->subqueryTableName);
			return null;
		}
		return $this->subqueryTableName = $db;
	}

	public function getSubqueryTableName(): string
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if ($this->hasSubqueryTableName()) {
			return $this->subqueryTableName;
		} elseif ($this->hasSubqueryClass()) {
			return $this->getSubqueryClass()::getTableNameStatic();
		}
		Debug::error("{$f} subquery table name and class are undefined");
	}

	public function getFullSubqueryTableName(): string
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->getFullSubqueryTableName()";
		ErrorMessage::deprecated($f);
		return $this->getSubqueryDatabaseName() . "." . $this->getSubqueryTableName();
	}

	public function hasSubqueryTableAlias(): bool
	{
		return isset($this->subqueryTableAlias) && is_string($this->subqueryTableAlias) && ! empty($this->subqueryTableAlias);
	}

	public function setSubqueryTableAlias(?string $alias): ?string
	{
		if ($alias == null) {
			unset($this->subqueryTableAlias);
			return null;
		}
		return $this->subqueryTableAlias = $alias;
	}

	public function getSubqueryTableAlias(): string
	{
		if ($this->hasSubqueryTableAlias()) {
			return $this->subqueryTableAlias;
		}
		return $this->getSubqueryTableName() . "_alias";
	}

	public function hasSubqueryOrderBy(): bool
	{
		return isset($this->subqueryOrderBy) && is_array($this->subqueryOrderBy) && ! empty($this->subqueryOrderBy);
	}

	public function setSubqueryOrderBy($ob): ?array
	{
		if ($ob == null) {
			unset($this->subqueryOrderBy);
			return null;
		} elseif (! is_array($ob)) {
			$ob = [
				$ob
			];
		}
		return $this->subqueryOrderBy = $ob;
	}

	public function getSubqueryOrderBy(): array
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if (! $this->hasSubqueryOrderBy()) {
			Debug::error("{$f} subquery order by is undefined");
		}
		return $this->subqueryOrderBy;
	}

	public function hasSubqueryLimit(): bool
	{
		return isset($this->subqueryLimit) && is_int($this->subqueryLimit);
	}

	public function getSubqueryLimit(): int
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if (! $this->hasSubqueryLimit()) {
			Debug::error("{$f} subquery limit is undefined");
		}
		return $this->subqueryLimit;
	}

	public function setSubqueryLimit(?int $limit): ?int
	{
		if ($limit == null) {
			unset($this->subqueryLimit);
			return null;
		}
		return $this->subqueryLimit = $limit;
	}

	public function hasSubqueryParameters(): bool
	{
		return isset($this->subqueryParameters) && is_array($this->subqueryParameters) && ! empty($this->subqueryParameters);
	}

	public function getSubqueryParameters(): array
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if (! $this->hasSubqueryParameters()) {
			Debug::error("{$f} subquery parameters are undefined");
		}
		return $this->subqueryParameters;
	}

	public function setSubqueryParameters($params): ?array
	{
		if ($params == null) {
			unset($this->subqueryParameters);
			return null;
		} elseif (! is_arraY($params)) {
			$params = [
				$params
			];
		}
		return $this->subqueryParameters = $params;
	}

	public function hasSubqueryTypeSpecifier(): bool
	{
		return isset($this->subqueryTypeSpecifier) && is_string($this->subqueryTypeSpecifier) && ! empty($this->subqueryTypeSpecifier);
	}

	public function setSubqueryTypeSpecifier(?string $ts): ?string
	{
		if ($ts == null) {
			unset($this->subqueryTypeSpecifier);
			return null;
		}
		return $this->subqueryTypeSpecifier = $ts;
	}

	public function getSubqueryTypeSpecifier(): string
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		if ($this->hasSubqueryTypeSpecifier()) {
			return $this->subqueryTypeSpecifier;
		} elseif ($this->hasSubqueryClass() && $this->hasSubqueryWhereCondition()) {
			/*
			 * return $this->getDataStructure()->getTypeSpecifier(
			 * $this->getSubqueryWhereCondition()->getConditionalColumnNames()
			 * );
			 */
			return $this->getSubqueryClass()::getTypeSpecifierStatic($this->getSubqueryWhereCondition()->getConditionalColumnNames());
		}
		Debug::warning("{$f} explicit type specifier or subquery class and where condition are undefined -- inferring type specifier from parameters");
		return getTypeSpecifier($this->getSubqueryParameters());
	}

	public function getAliasExpression()
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->".__METHOD__."()";
		$print = false;
		if ($this->hasAliasExpression()) {
			return $this->aliasExpression;
		}
		$select = new SelectStatement($this->getSubqueryExpression());
		$select->withJoinExpressions(TableFactor::create()->withDatabaseName($this->getSubqueryDatabaseName())
			->withTableName($this->getSubqueryTableName())
			->as($this->getSubqueryTableAlias()))
			->where($this->getSubqueryWhereCondition());
		if ($this->hasSubqueryOrderBy()) {
			$select->setOrderBy(...$this->getSubqueryOrderBy());
		}
		if ($this->hasSubqueryLimit()) {
			$select->limit($this->getSubqueryLimit());
		}
		if ($this->hasSubqueryParameters()) {
			$select->withTypeSpecifier($this->getSubqueryTypeSpecifier())
				->withParameters($this->getSubqueryParameters());
		} elseif ($print) {
			$decl = $this->getDeclarationLine();
			Debug::print("{$f} no subquery parameters. Instantiated {$decl}");
		}
		return $select;
	}

	public function hasColumnAlias(): bool
	{
		return isset($this->columnAlias);
	}

	public function setColumnAlias($alias)
	{
		if ($alias == null) {
			unset($this->columnAlias);
			return null;
		}
		return $this->columnAlias = $alias;
	}

	public function getColumnAlias(): ColumnAlias
	{
		if ($this->hasColumnAlias()) {
			return $this->columnAlias;
		}
		return new ColumnAlias($this->getAliasExpression(), $this->getColumnName());
	}

	public function getPrimaryKeyFlag()
	{
		return $this->getFlag(COLUMN_FILTER_PRIMARY_KEY);
	}

	public function setDirtyCacheFlag(bool $value = true): bool
	{
		return $this->setFlag(COLUMN_FILTER_DIRTY_CACHE, $value);
	}

	public function getDirtyCacheFlag(): bool
	{
		return $this->getFlag(COLUMN_FILTER_DIRTY_CACHE);
	}

	public function recache(bool $value = true): Datum
	{
		$this->setDirtyCacheFlag($value);
		return $this;
	}

	public function setReferenceColumn(?ForeignKeyDatum $column): ?ForeignKeyDatum
	{
		if ($column == null) {
			unset($this->referenceColumn);
			return null;
		}
		return $this->referenceColumn = $column;
	}

	public function hasReferenceColumn(): bool
	{
		return isset($this->referenceColumn) && $this->referenceColumn instanceof ForeignKeyDatum;
	}

	public function getReferenceColumn(): ForeignKeyDatum
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->getReferenceColumn()";
		if (! $this->hasReferenceColumn()) {
			Debug::error("{$f} reference column is undefined");
		}
		return $this->referenceColumn;
	}

	public function setReferenceColumnName(?string $rcn): ?string
	{
		if ($rcn == null) {
			unset($this->referenceColumnName);
			return null;
		}
		return $this->referenceColumnName = $rcn;
	}

	public function hasReferenceColumnName(): bool
	{
		return isset($this->referenceColumnName) && is_string($this->referenceColumnName) && ! empty($this->referenceColumnName) || $this->hasReferenceColumn();
	}

	public function getReferenceColumnName(): string
	{
		$f = __METHOD__; //Datum::getShortClass()."(".static::getShortClass().")->getReferenceColumnName()";
		if (! $this->hasReferenceColumnName()) {
			Debug::error("{$f} reference column name is undefined");
		} elseif ($this->hasReferenceColumn()) {
			return $this->getReferenceColumn()->getColumnName();
		}
		return $this->referenceColumnName;
	}

	public function antialias(int $persistence_mode = PERSISTENCE_MODE_DATABASE): Datum
	{
		unset($this->columnAlias);
		unset($this->subqueryClass);
		unset($this->subqueryColumnName);
		unset($this->subqueryDatabaseName);
		unset($this->subqueryExpression);
		unset($this->subqueryLimit);
		unset($this->subqueryOrderBy);
		unset($this->subqueryParameters);
		unset($this->subqueryTableAlias);
		unset($this->subqueryTableName);
		unset($this->subqueryTypeSpecifier);
		unset($this->subqueryWhereCondition);
		$this->setPersistenceMode($persistence_mode);
		return $this;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->apoptoticSignal);
		unset($this->properties);
		unset($this->propertyTypes);
		unset($this->columnAlias);
		unset($this->databaseStorageType);
		unset($this->dataStructure);
		unset($this->dataStructureClass);
		unset($this->decryptionKeyName);
		unset($this->elementClass);
		unset($this->engineAttributeString);
		unset($this->eventListeners);
		unset($this->generationClosure);
		unset($this->humanReadableName);
		unset($this->mirrorIndices);
		unset($this->regenerationClosure);
		unset($this->subqueryClass);
		unset($this->subqueryColumnName);
		unset($this->subqueryDatabaseName);
		unset($this->subqueryExpression);
		unset($this->subqueryLimit);
		unset($this->subqueryOrderBy);
		unset($this->subqueryParameters);
		unset($this->aliasExpression);
		unset($this->subqueryTableAlias);
		unset($this->subqueryTypeSpecifier);
		unset($this->subqueryWhereCondition);
		unset($this->transcryptionKeyName);
		unset($this->validationClosure);
		unset($this->value);
		unset($this->variableName);
	}
}
