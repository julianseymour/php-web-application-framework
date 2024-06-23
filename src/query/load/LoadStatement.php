<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\load;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
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

abstract class LoadStatement extends QueryStatement{

	use CharacterSetTrait;
	use DuplicateKeyHandlerTrait;
	use FullTableNameTrait;
	use LocalFlagBearingTrait;
	use MultipleColumnNamesTrait;
	use MultipleExpressionsTrait;
	use PrioritizedTrait;

	protected $ignoreRowCount;

	protected $infilename;

	public function __construct(?string $infilename = null, ...$dbtable){
		$f = __METHOD__;
		parent::__construct();
		$this->requirePropertyType('columnNames', 's');
		$this->requirePropertyType('expressions', ExpressionCommand::class);
		if($infilename !== null){
			$this->setInfile($infilename);
		}
		if(isset($dbtable) && count($dbtable) > 0){
			$this->unpackTableName($dbtable);
		}
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->characterSet, $deallocate);
		$this->release($this->databaseName, $deallocate);
		$this->release($this->duplicateKeyHandler, $deallocate);
		$this->release($this->ignoreRowCount, $deallocate);
		$this->release($this->infilename, $deallocate);
		$this->release($this->priorityLevel, $deallocate);
		$this->release($this->tableName, $deallocate);
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"local"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"local"
		]);
	}
	
	public function setPriority(?string $p):?string{
		$f = __METHOD__;
		if(!is_string($p)){
			Debug::error("{$f} priority level must be a string");
		}
		$p = strtolower($p);
		switch($p){
			case PRIORITY_CONCURRENT:
			case PRIORITY_LOW:
				return $this->priorityLevel = $p;
			default:
				Debug::error("{$f} invalid priority \"{$p}\"");
		}
	}

	public function setInfilename(?string $name):?string{
		$f = __METHOD__;
		if(!is_string($name)){
			Debug::error("{$f} infilename must be a string");
		}elseif($this->hasInfilename()){
			$this->release($this->infilename);
		}
		return $this->infilename = $this->claim($name);
	}

	public function hasInfilename():bool{
		return isset($this->infilename);
	}

	public function getInfilename():string{
		$f = __METHOD__;if(!$this->infilename){
			Debug::error("{$f} infilename is undefined");
		}
		return $this->infilename;
	}

	public function infile(?string $name):LoadDataStatement{
		$this->setInfilename($name);
		return $this;
	}

	public function hasDuplicateKeyHandler():bool{
		return isset($this->duplicateKeyHandler) || $this->getLocalFlag();
	}

	public function getDuplicateKeyHandler():string{
		$f = __METHOD__;
		if(!$this->hasDuplicateKeyHandler()){
			Debug::error("{$f} duplicate key handler is undefined");
		}elseif($this->getLocalFlag()){
			return DIRECTIVE_IGNORE;
		}
		return $this->duplicateKeyHandler;
	}
	
	public function setIgnoreRows(?int $count):int{
		$f = __METHOD__;
		if(!is_int($count)){
			Debug::error("{$f} ignored row count must be a positive integer");
		}elseif($count < 0){
			Debug::error("{$f} ignored row count must be non-negative");
		}elseif($this->hasIgnoreRows()){
			$this->release($this->ignoreRowCount);
		}
		return $this->ignoreRowCount = $this->claim($count);
	}

	public function hasIgnoreRows():bool{
		return isset($this->ignoreRowCount);
	}

	public function getIgnoreRows():int{
		if(!$this->hasIgnoreRows()){
			return 0;
		}
		return $this->ignoreRowCount;
	}

	public function ignoreRows(?int $count):LoadStatement{
		$this->setIgnoreRows($count);
		return $this;
	}

	public function intoTable(...$dbtable): LoadStatement{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if(!isset($dbtable)){
			Debug::error("{$f} null input parameter");
		}elseif($print){
			Debug::print("{$f} input parameter : ".json_encode($dbtable));
		}
		$this->unpackTableName($dbtable);
		return $this;
	}

	public function getQueryStatementString():string{
		$string = "";
		// [LOW_PRIORITY | CONCURRENT]
		if($this->hasPriority()){
			$string .= $this->getPriority() . " ";
		}
		// [LOCAL]
		if($this->getLocalFlag()){
			$string .= "local ";
		}
		// INFILE 'file_name'
		$string .= "infile " . single_quote($this->getInfilename()) . " ";
		// [REPLACE | IGNORE]
		if($this->hasDuplicateKeyHandler()){
			$string .= $this->getDuplicateKeyHandler() . " ";
		}
		// INTO TABLE [db_name.]tbl_name
		$string .= "into table ";
		if($this->hasDatabaseName()){
			$string .= back_quote($this->getDatabaseName()) . ".";
		}
		$string .= back_quote($this->getTableName());
		return $string;
	}
}
