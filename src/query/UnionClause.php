<?php
namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatementInterface;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatementTrait;
use Exception;

class UnionClause extends Basic implements SelectStatementInterface, SQLInterface
{

	use DistinctionTrait;
	use SelectStatementTrait;

	public function __construct($selectStatement)
	{
		parent::__construct();
		$this->setSelectStatement($selectStatement);
	}

	public function setSelectStatement(?SelectStatement $selectStatement): SelectStatement
	{
		$f = __METHOD__; //UnionClause::getShortClass()."(".static::getShortClass().")->setSelectStatement()";
		if ($selectStatement == null) {
			unset($this->selectStatement);
			return null;
		} elseif (! $selectStatement instanceof SelectStatement) {
			Debug::error("{$f} input parameter must be a select statement");
		} elseif ($selectStatement->getHighPriorityFlag()) {
			Debug::error("{$f} you cannot have a high priority flag as a subquery");
		} elseif ($selectStatement->getBufferResultFlag()) {
			Debug::error("{$f} you cannot have a subquery with buffer results flag");
		}
		$selectStatement->setSubqueryFlag(true);
		return $this->selectStatement = $selectStatement;
	}

	public static function all($selectStatement)
	{
		return (new UnionClause($selectStatement))->withDistinction(DISTINCTION_ALL);
	}

	public static function distinct($selectStatement)
	{
		return (new UnionClause($selectStatement))->withDistinction(DISTINCTION_DISTINCT);
	}

	public function toSQL(): string
	{
		$f = __METHOD__; //UnionClause::getShortClass()."(".static::getShortClass().")->toSQL()";
		try {
			// UNION [ALL | DISTINCT] SELECT ...
			$string = "union ";
			if ($this->hasDistinction()) {
				$string .= $this->getDistinction() . " ";
			}
			$string .= "(" . $this->getSelectStatement() . ")";
			return $string;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->distinction);
		unset($this->selectStatement);
	}
}
