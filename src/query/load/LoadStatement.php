<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\load;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\expression\ExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\MultipleExpressionsTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\CharacterSetTrait;
use JulianSeymour\PHPWebApplicationFramework\query\DuplicateKeyHandlerTrait;
use JulianSeymour\PHPWebApplicationFramework\query\LocalFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\query\PrioritizedTrait;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnNamesTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\FullTableNameTrait;

abstract class LoadStatement extends QueryStatement
{

	use CharacterSetTrait;
	use DuplicateKeyHandlerTrait;
	use FullTableNameTrait;
	use LocalFlagBearingTrait;
	use MultipleColumnNamesTrait;
	use MultipleExpressionsTrait;
	use PrioritizedTrait;

	protected $ignoreRowCount;

	protected $infilename;

	public function __construct(?string $infilename = null, ...$dbtable)
	{
		$f = __METHOD__; //LoadStatement::getShortClass()."(".static::getShortClass().")->__construct()";
		parent::__construct();
		$this->requirePropertyType('columnNames', 's');
		$this->requirePropertyType('expressions', ExpressionCommand::class);
		if ($infilename !== null) {
			$this->setInfile($infilename);
		}
		$this->unpackTableName($dbtable);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->properties);
		unset($this->propertyTypes);
		unset($this->characterSet);
		unset($this->duplicateKeyHandler);
		unset($this->ignoreRowCount);
		unset($this->infilename);
		unset($this->priorityLevel);
		unset($this->tableName);
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"local"
		]);
	}

	public function setPriority($p)
	{
		$f = __METHOD__; //LoadStatement::getShortClass()."(".static::getShortClass().")->setPriority()";
		if ($p == null) {
			unset($this->priorityLevel);
			return null;
		} elseif (! is_string($p)) {
			Debug::error("{$f} priority level must be a string");
		}
		$p = strtolower($p);
		switch ($p) {
			case PRIORITY_CONCURRENT:
			case PRIORITY_LOW:
				return $this->priorityLevel = $p;
			default:
				Debug::error("{$f} invalid priority \"{$p}\"");
		}
	}

	public function setInfilename($name)
	{
		$f = __METHOD__; //LoadStatement::getShortClass()."(".static::getShortClass().")->setInfile()";
		if ($name == null) {
			unset($this->infilename);
			return null;
		} elseif (! is_string($name)) {
			Debug::error("{$f} infilename must be a string");
		}
		return $this->infilename = $name;
	}

	public function hasInfilename()
	{
		return isset($this->infilename);
	}

	public function getInfilename()
	{
		$f = __METHOD__; //LoadStatement::getShortClass()."(".static::getShortClass().")->getInfile()";
		if (! $this->infilename) {
			Debug::error("{$f} infilename is undefined");
		}
		return $this->infilename;
	}

	public function infile($name)
	{
		$this->setInfilename($name);
		return $this;
	}

	public function hasDuplicateKeyHandler()
	{
		return isset($this->duplicateKeyHandler) || $this->getLocalFlag();
	}

	public function getDuplicateKeyHandler()
	{
		$f = __METHOD__; //LoadStatement::getShortClass()."(".static::getShortClass().")->getDuplicateKeyHandler()";
		if (! $this->hasDuplicateKeyHandler()) {
			Debug::error("{$f} duplicate key handler is undefined");
		} elseif ($this->getLocalFlag()) {
			return DIRECTIVE_IGNORE;
		}
		return $this->duplicateKeyHandler;
	}

	public function setIgnoreRows($count)
	{
		$f = __METHOD__; //LoadStatement::getShortClass()."(".static::getShortClass().")->setIgnoreRows()";
		if ($count == null) {
			unset($this->ignoreRowCount);
			return null;
		} elseif (! is_int($count)) {
			Debug::error("{$f} ignored row count must be a positive integer");
		} elseif ($count < 0) {
			Debug::error("{$f} ignored row count must be non-negative");
		}
		return $this->ignoreRowCount = $count;
	}

	public function hasIgnoreRows()
	{
		return isset($this->ignoreRowCount);
	}

	public function getIgnoreRows()
	{
		if (! $this->hasIgnoreRows()) {
			return 0;
		}
		return $this->ignoreRowCount;
	}

	public function ignoreRows($count)
	{
		$this->setIgnoreRows($count);
		return $this;
	}

	public function intoTable(...$dbtable): LoadStatement
	{
		$f = __METHOD__; //LoadStatement::getShortClass()."(".static::getShortClass().")->intoTable()";
		$this->unpackTableName($dbtable);
		return $this;
	}

	public function getQueryStatementString()
	{
		$string = "";
		// [LOW_PRIORITY | CONCURRENT]
		if ($this->hasPriority()) {
			$string .= $this->getPriority() . " ";
		}
		// [LOCAL]
		if ($this->getLocalFlag()) {
			$string .= "local ";
		}
		// INFILE 'file_name'
		$string .= "infile " . single_quote($this->getInfilename()) . " ";
		// [REPLACE | IGNORE]
		if ($this->hasDuplicateKeyHandler()) {
			$string .= $this->getDuplicateKeyHandler() . " ";
		}
		// INTO TABLE [db_name.]tbl_name
		$string .= "into table ";
		if ($this->hasDatabaseName()) {
			$string .= back_quote($this->getDatabaseName()) . ".";
		}
		$string .= back_quote($this->getTableName());
		return $string;
	}
}
