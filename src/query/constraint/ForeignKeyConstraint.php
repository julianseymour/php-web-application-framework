<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\constraint;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\implode_back_quotes;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayKeyProviderInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnNamesTrait;
use JulianSeymour\PHPWebApplicationFramework\query\database\DatabaseNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\index\IndexNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\index\KeyPart;
use JulianSeymour\PHPWebApplicationFramework\query\index\KeyPartsTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\TableNameTrait;
use Exception;

class ForeignKeyConstraint extends Constraint implements ArrayKeyProviderInterface
{

	use DatabaseNameTrait;
	use ForeignKeyConstraintTrait;
	use IndexNameTrait;
	use KeyPartsTrait;
	use MultipleColumnNamesTrait;
	use TableNameTrait;

	protected $matchType;

	public function __construct(?string $symbol, string $indexName, array $columnNames, string $databaseName, string $tableName, array $keyparts)
	{
		parent::__construct($symbol);
		$this->requirePropertyType("columnNames", "s");
		$this->requirePropertyType("keyParts", KeyPart::class);
		$this->setIndexName($indexName);
		$this->setColumnNames($columnNames);
		$this->setDatabaseName($databaseName);
		$this->setTableName($tableName);
		$this->setKeyParts($keyparts);
		// symbol, index name, columnNames, tableName, keyparts, matchType, onDelete, onUpdate
	}

	public function getArrayKey(int $count)
	{
		return $this->getIndexName();
	}

	public function setMatch($type)
	{
		$f = __METHOD__; //ForeignKeyConstraint::getShortClass()."(".static::getShortClass().")->setMatch()";
		if($type == null) {
			unset($this->matchType);
			return null;
		}elseif(!is_string($type)) {
			Debug::error("{$f} match type must be a string");
		}
		$type = strtolower($type);
		switch ($type) {
			case MATCH_FULL:
			case MATCH_PARTIAL:
			case MATCH_SIMPLE:
				break;
			default:
				Debug::error("{$f} invalid match type \"{$type}\"");
		}
		return $this->matchType = $type;
	}

	public function hasMatch()
	{
		return isset($this->matchType);
	}

	public function getMatch()
	{
		$f = __METHOD__; //ForeignKeyConstraint::getShortClass()."(".static::getShortClass().")->getMatch()";
		if(!$this->hasMatch()) {
			Debug::error("{$f} match type is undefined");
		}
		return $this->matchType;
	}

	public function match($type)
	{
		$this->setMatch($type);
		return $this;
	}

	public function toSQL(): string
	{
		$f = __METHOD__; //ForeignKeyConstraint::getShortClass()."(".static::getShortClass().")->toSQL()";
		try{

			// reference_definition:
			// [CONSTRAINT [symbol]] FOREIGN KEY [index_name] (col_name,...) REFERENCES tbl_name (key_part,...) [MATCH FULL | MATCH PARTIAL | MATCH SIMPLE] [ON DELETE ref_opt] [ON UPDATE ref_opt]
			// reference_option:
			// RESTRICT | CASCADE | SET NULL | NO ACTION | SET DEFAULT

			$string = parent::toSQL() . "foreign key ";
			if($this->hasIndexName()) {
				$string .= $this->getIndexName() . " ";
			}
			$columnNames = implode_back_quotes(',', $this->getColumnNames());
			$dbtable = "";
			if($this->hasDatabaseName()) {
				$dbtable .= back_quote($this->getDatabaseName()) . ".";
			}
			$dbtable .= back_quote($this->getTableName());
			$keyparts = [];
			foreach($this->getKeyParts() as $kp) {
				if($kp instanceof SQLInterface) {
					$kp = $kp->toSQL();
				}
				array_push($keyparts, $kp);
			}
			$keyparts = implode_back_quotes(',', $keyparts); // XXX reassigning an string to something initially declared as an array rubs me the wrong way
			$string .= "({$columnNames}) references {$dbtable} ({$keyparts})";
			if($this->hasMatch()) {
				$string .= " match " . $this->getMatch();
			}
			if($this->hasOnDelete()) {
				$onDelete = $this->getOnDelete();
				$string .= " on delete {$onDelete}";
			}
			if($this->hasOnUpdate()) {
				$onUpdate = $this->getOnUpdate();
				$string .= " on update {$onUpdate}";
			}
			return $string;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->properties);
		unset($this->indexName);
		unset($this->onDelete);
		unset($this->onUpdate);
		unset($this->parentTableName);
	}
}