<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\column;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;

class ColumnAliasExpression extends Basic implements SQLInterface
{

	use ColumnNameTrait;

	protected $tableAlias;

	public function __construct($tableAlias, $columnName)
	{
		parent::__construct();
		if (isset($tableAlias) && ! empty($tableAlias)) {
			$this->setTableAlias($tableAlias);
		}
		if (isset($columnName) && ! empty($columnName)) {
			$this->setColumnName($columnName);
		}
	}

	public function hasTableAlias(): bool
	{
		return isset($this->tableAlias);
	}

	public function getTableAlias(): string
	{
		$f = __METHOD__; //ColumnAliasExpression::getShortClass()."(".static::getShortClass().")->getTableAlias()";
		if (! $this->hasTableAlias()) {
			Debug::error("{$f} table alias is undefied");
		}
		return $this->tableAlias;
	}

	public function setTableAlias(?string $tableAlias): ?string
	{
		if ($tableAlias == null) {
			unset($this->tableAlias);
			return null;
		}
		return $this->tableAlias = $tableAlias;
	}

	public function toSQL(): string
	{
		return back_quote($this->getTableAlias()) . "." . back_quote($this->getColumnName());
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->tableAlias);
	}
}