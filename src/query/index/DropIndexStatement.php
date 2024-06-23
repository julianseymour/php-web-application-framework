<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\index;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\AlgorithmOptionTrait;
use JulianSeymour\PHPWebApplicationFramework\query\LockOptionTrait;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;
use JulianSeymour\PHPWebApplicationFramework\query\table\TableNameTrait;

class DropIndexStatement extends QueryStatement
{

	use AlgorithmOptionTrait;
	use IndexNameTrait;
	use LockOptionTrait;
	use TableNameTrait;

	public function __construct($indexName = null)
	{
		parent::__construct();
		if($indexName !== null){
			$this->setIndexName($indexName);
		}
	}

	public function on($tableName)
	{
		$this->setTableName($tableName);
		return $this;
	}

	public function setLockOption($lock)
	{
		$f = __METHOD__; //DropIndexStatement::getShortClass()."(".static::getShortClass().")->setLockOption()";
		if($lock == null){
			unset($this->lockOption);
			return null;
		}elseif(!is_string($lock)){
			Debug::error("{$f} lock option must be a string");
		}
		$lock = strtolower($lock);
		switch($lock){
			case LOCK_OPTION_DEFAULT:
			case LOCK_OPTION_NONE:
			case LOCK_OPTION_SHARED:
			case LOCK_OPTION_EXCLUSIVE:
				return $this->lockOption = $lock;
			default:
				Debug::error("{$f} invalid lock option \"{$lock}\"");
		}
	}

	public function getQueryStatementString()
	{
		// DROP INDEX index_name ON tbl_name
		$string = "drop index " . $this->getIndexName() . " on ";
		if($this->hasDatabaseName()){
			$string .= back_quote($this->getDatabaseName()) . ".";
		}
		$string .= back_quote($this->getTableName());
		// [ALGORITHM [=] {DEFAULT | INPLACE | COPY}]
		if($this->hasAlgorithm()){
			$string .= " algorithm " . $this->getAlgorithm();
		}
		// [LOCK [=] {DEFAULT | NONE | SHARED | EXCLUSIVE}]
		if($this->hasLockOption()){
			$string .= " lock " . $this->getLockOption();
		}
		return $string;
	}
}
