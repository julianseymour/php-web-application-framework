<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\select;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\AliasTrait;
use JulianSeymour\PHPWebApplicationFramework\query\index\IndexNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereConditionalStatement;
ErrorMessage::unimplemented(__FILE__);

class HandlerStatement extends WhereConditionalStatement
{

	use AliasTrait;
	use IndexNameTrait;
	use OperatorTrait;
	use RetainResultFlagBearingTrait;

	public function getQueryStatementString()
	{
		// HANDLER tbl_name OPEN [ [AS] alias]

		// HANDLER tbl_name READ index_name
		$string = "handler ";
		if($this->hasDatabaseName()) {
			$string .= back_quote($this->getDatabaseName()) . ".";
		}
		$string .= back_quote($this->getTableName()) . " read ";
		if($this->hasIndexName()) {
			$string .= $this->getIndexName() . " ";
			// { = | <= | >= | < | > } (value1,value2,...)
			if($this->hasOperator()) {
				$string .= $this->getOperator() . " (";
				for ($i = 0; $i < $this->getParameterCount(); $i ++) {
					if($i > 0) {
						$string .= ",";
					}
					$string .= "?";
				}
				$string .= ")";
			}elseif($this->hasIndexPosition()) { // { FIRST | NEXT | PREV | LAST }
				$string .= " " . $this->getIndexPosition();
			}
		}elseif($this->hasIndexPosition()) { // HANDLER tbl_name READ { FIRST | NEXT }
			$string .= " " . $this->getIndexPosition();
		}
		// [ WHERE where_condition ]
		if($this->hasWhereCondition()) {
			$string .= " where " . $this->getWhereCondition();
		}
		// [LIMIT ... ]
		if($this->hasLimit()) {
			$string .= " limit " . $this->getLimit();
			if($this->hasOffset()) {
				$string .= " offset " . $this->getOffset();
			}
		}
		return $string;
		// HANDLER tbl_name CLOSE
	}
}