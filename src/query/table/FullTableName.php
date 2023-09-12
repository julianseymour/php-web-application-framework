<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\table;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;

class FullTableName extends Basic implements SQLInterface
{

	use FullTableNameTrait;

	public function __construct($db, $table)
	{
		parent::__construct();
		if($db == "*") {
			$this->setAllDatabasesFlag(true);
		}elseif($db !== null) {
			$this->setDatabaseName($db);
		}
		if($table == "*") {
			$this->setAllTablesFlag(true);
		}elseif($table !== null) {
			$this->setTableName($table);
		}
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"anydb",
			"anytable"
		]);
	}

	public function setAllDatabasesFlag(bool $value = true): bool
	{
		return $this->setFlag("anydb", $value);
	}

	public function getAllDatabasesFlag(): bool
	{
		return $this->getFlag("anydb");
	}

	public function setAllTablesFlag(bool $value = true): bool
	{
		return $this->setFlag("anytable", $value);
	}

	public function getAllTablesFlag(): bool
	{
		return $this->getFlag("anytable");
	}

	public function toSQL(): string
	{
		$string = "";
		if($this->getAllDatabasesFlag()) {
			return "*.*";
		}elseif($this->hasDatabaseName()) {
			$string .= back_quote($this->getDatabaseName()) . ".";
		}
		if($this->getAllTablesFlag() || ! $this->hasTableName()) {
			$string .= "*";
		}else{
			$string .= back_quote($this->getTableName());
		}
		return $string;
	}
}
