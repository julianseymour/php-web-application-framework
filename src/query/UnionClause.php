<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatementInterface;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatementTrait;
use Exception;

class UnionClause extends Basic implements SelectStatementInterface, SQLInterface{

	use DistinctionTrait;
	use SelectStatementTrait;

	public function __construct($selectStatement){
		parent::__construct();
		$this->setSelectStatement($selectStatement);
	}

	public function setSelectStatement(?SelectStatement $selectStatement): SelectStatement{
		$f = __METHOD__;
		if(!$selectStatement instanceof SelectStatement){
			Debug::error("{$f} input parameter must be a select statement");
		}elseif($selectStatement->getHighPriorityFlag()){
			Debug::error("{$f} you cannot have a high priority flag as a subquery");
		}elseif($selectStatement->getBufferResultFlag()){
			Debug::error("{$f} you cannot have a subquery with buffer results flag");
		}
		$selectStatement->setSubqueryFlag(true);
		if($this->hasSelectStatement()){
			$this->release($this->selectStatement);
		}
		return $this->selectStatement = $this->claim($selectStatement);
	}

	public static function all($selectStatement):UnionClause{
		$union = new UnionClause($selectStatement);
		return $union->withDistinction(DISTINCTION_ALL);
	}

	public static function distinct($selectStatement):UnionClause{
		$union = new UnionClause($selectStatement);
		return $union->withDistinction(DISTINCTION_DISTINCT);
	}

	public function toSQL(): string{
		$f = __METHOD__;
		try{
			// UNION [ALL | DISTINCT] SELECT ...
			$string = "union ";
			if($this->hasDistinction()){
				$string .= $this->getDistinction() . " ";
			}
			$string .= "(" . $this->getSelectStatement() . ")";
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->distinction, $deallocate);
		$this->release($this->selectStatement, $deallocate);
	}
}
