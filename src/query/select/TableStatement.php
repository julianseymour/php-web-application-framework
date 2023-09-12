<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\select;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use JulianSeymour\PHPWebApplicationFramework\query\LimitOffsetTrait;
use JulianSeymour\PHPWebApplicationFramework\query\OrderableTrait;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;
use JulianSeymour\PHPWebApplicationFramework\query\table\FullTableNameTrait;

class TableStatement extends QueryStatement
{

	use FullTableNameTrait;
	use LimitOffsetTrait;
	use OrderableTrait;
	use RetainResultFlagBearingTrait;

	public function __construct(...$dbtable)
	{
		parent::__construct();
		$this->unpackTableName($dbtable);
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"retainResult"
		]);
	}

	public function getQueryStatementString()
	{
		// TABLE table_name [ORDER BY column_name] [LIMIT number [OFFSET number]]
		$string = "table ";
		if($this->hasDatabaseName()) {
			$string .= back_quote($this->getDatabaseName()) . ".";
		}
		$string .= back_quote($this->getTableName());
		if($this->hasOrderBy()) {
			$string .= $this->getOrderByString();
		}
		if($this->hasLimit()) {
			$string .= " limit " . $this->getLimit();
			if($this->hasOffset()) {
				$string .= " offset " . $this->getOffset();
			}
		}
		return $string;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->limitCount);
		unset($this->orderByExpression);
		unset($this->tableName);
	}
}
