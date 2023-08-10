<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\view;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\table\AbstractDropTableStatement;

class DropViewStatement extends AbstractDropTableStatement
{

	public function getQueryStatementString()
	{
		$f = __METHOD__; //DropViewStatement::getShortClass()."(".static::getShortClass().")->getQueryStatementString()";
		// DROP VIEW
		$string = "drop view ";
		// [IF EXISTS]
		if ($this->getIfExistsFlag()) {
			$string .= "if exsits ";
		}
		// view_name [, view_name] ...
		$i = 0;
		foreach ($this->getTableNames() as $tn) {
			if ($i ++ > 0) {
				$string .= ", ";
			}
			if ($tn instanceof SQLInterface) {
				$string .= $tn->toSQL();
			} elseif (is_string($tn)) {
				$string .= back_quote($tn);
			} else {
				Debug::error("{$f} table name is not an SQLInterface or string");
			}
		}
		// $string .= implode(',', $this->getTableNames());
		// [RESTRICT | CASCADE]
		if ($this->hasReferenceOption()) {
			$string .= " " . $this->getReferenceOption();
		}
		return $string;
	}
}
