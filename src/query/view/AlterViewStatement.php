<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\view;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\implode_back_quotes;

class AlterViewStatement extends ViewStatement
{

	public function getQueryStatementString()
	{
		// ALTER
		$string = "alter ";
		// [ALGORITHM = {UNDEFINED | MERGE | TEMPTABLE}]
		if($this->hasAlgorithm()) {
			$string .= "algoritm = " . $this->getAlgorithm() . " ";
		}
		// [DEFINER = user]
		if($this->hasDefiner()) {
			$string .= "definer " . $this->getDefiner() . " ";
		}
		// [SQL SECURITY { DEFINER | INVOKER }]
		if($this->hasSQLSecurity()) {
			$string .= "SQL security " . $this->getSQLSecurity() . " ";
		}
		// VIEW view_name
		$string .= "view ";
		if($this->hasDatabaseName()) {
			$string .= back_quote($this->getDatabaseName()) . ".";
		}
		$string .= back_quote($this->getName()) . " ";
		// [(column_list)]
		if($this->hasColumnNames()) {
			$string .= "(" . implode_back_quotes(',', $this->getColumnNames()) . ") ";
		}
		// AS select_statement
		$string .= "as " . $this->getSelectStatement();
		// [WITH [CASCADED | LOCAL] CHECK OPTION]
		if($this->hasCheckOption()) {
			$check = $this->getCheckOption();
			$string .= " with {$check} ";
			if($check !== CHECK_OPTION_CHECK) {
				$string .= "check ";
			}
			$string .= "option";
		}
		return $string;
	}
}