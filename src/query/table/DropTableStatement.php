<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\TemporaryFlagBearingTrait;

class DropTableStatement extends AbstractDropTableStatement
{

	use TemporaryFlagBearingTrait;

	public function __construct(...$tableNames)
	{
		parent::__construct();
		if(isset($tableNames)) {
			$this->setTableNames($tableNames);
		}
		$this->requirePropertyType("tableNames", FullTableName::class);
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"temporary"
		]);
	}

	public function getQueryStatementString(): string
	{
		$f = __METHOD__; //DropTableStatement::getShortClass()."(".static::getShortClass().")->getQueryStatementString()";
		// DROP [TEMPORARY] TABLE [IF EXISTS] tbl_name [, tbl_name] ... [RESTRICT | CASCADE]
		$string = "drop ";
		if($this->getTemporaryFlag()) {
			$string .= "temporary ";
		}
		$string .= "table ";
		if($this->getIfExistsFlag()) {
			$string .= "if exists ";
		}
		$i = 0;
		foreach($this->getTableNames() as $tn) {
			if($i ++ > 0) {
				$string .= ", ";
			}
			if($tn instanceof SQLInterface) {
				$string .= $tn->toSQL();
			}elseif(is_string($tn)) {
				$string .= back_quote($tn);
			}else{
				Debug::error("{$f} table name is not an SQLInterface or string");
			}
		}
		// $string .= implode(',', $this->getTableNames());
		if($this->hasReferenceOption()) {
			$string .= " " . $this->getReferenceOption();
		}
		return $string;
	}
}
