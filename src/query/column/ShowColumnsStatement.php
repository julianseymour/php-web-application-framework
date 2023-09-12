<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\column;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereConditionalStatement;
use Exception;

class ShowColumnsStatement extends WhereConditionalStatement
{

	public function from($dbtable)
	{
		$this->setTableName($dbtable);
		return $this;
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"extended",
			"full"
		]);
	}

	public function setExtendedFlag($value = true)
	{
		return $this->setFlag("extended", $value);
	}

	public function getExtendedFlag()
	{
		return $this->getFlag("extended");
	}

	public function setFullFlag($value = true)
	{
		return $this->setFlag("full", $value);
	}

	public function getFullFlag()
	{
		return $this->getFlag("full");
	}

	public function getQueryStatementString()
	{
		$f = __METHOD__; //ShowColumnsStatement::getShortClass()."(".static::getShortClass().")->getQueryStatementString()";
		try{
			// SHOW [EXTENDED] [FULL] {COLUMNS | FIELDS} FROM tbl_name [LIKE 'pattern' | WHERE expr]
			$string = "show ";
			if($this->getExtendedFlag()) {
				$string .= "extended ";
			}
			if($this->getFullFlag()) {
				$string .= "full ";
			}
			$string .= " columns from ";
			if($this->hasDatabaseName()) {
				$string .= back_quote($this->getDatabaseName()) . ".";
			}
			$string .= back_quote($this->getTableName());
			$where = $this->getWhereCondition();
			if($where->getOperator() === OPERATOR_LIKE) {
				ErrorMessage::unimplemented($f);
				$string .= " like '{$where}'";
			}else{
				$string .= " where {$where}";
			}
			return $string;
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}