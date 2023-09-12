<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;

class TruncateTableStatement extends QueryStatement
{

	use FullTableNameTrait;

	public function __construct(...$dbtable)
	{
		$f = __METHOD__; //TruncateTableStatement::getShortClass()."(".static::getShortClass().")->__construct()";
		parent::__construct();
		$this->unpackTableName($dbtable);
	}

	public function getQueryStatementString()
	{
		// TRUNCATE [TABLE] tbl_name
		$string = "truncate "; // .$this->getTableName();
		if($this->hasDatabaseName()) {
			$string .= back_quote($this->getDatabaseName()) . ".";
		}
		$string .= back_quote($this->getTableName());
		return $string;
	}
}
