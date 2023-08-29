<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\join;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\implode_back_quotes;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\query\AliasTrait;
use JulianSeymour\PHPWebApplicationFramework\query\column\MultipleColumnNamesTrait;
use JulianSeymour\PHPWebApplicationFramework\query\database\DatabaseNameTrait;
use JulianSeymour\PHPWebApplicationFramework\query\partition\MultiplePartitionNamesTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\TableNameTrait;
use Exception;

class TableFactor extends JoinExpression implements StaticPropertyTypeInterface, StringifiableInterface{

	use AliasTrait;
	use DatabaseNameTrait;
	use JoinExpressionsTrait;
	use MultipleColumnNamesTrait;
	use MultiplePartitionNamesTrait;
	use StaticPropertyTypeTrait;
	use TableNameTrait;

	protected $tableSubquery;

	public function __construct($db=null, $table=null, $alias=null){
		parent::__construct();
		if($db !== null){
			$this->setDatabaseName($db);
			if($table !== null){
				$this->setTableName($table);
				if($alias !== null){
					$this->setAlias($alias);
				}
			}
		}
	}
	
	public static function create(): TableFactor{
		return new TableFactor();
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"lateral"
		]);
	}

	public function setIndexHintList($indexHintList){
		return $this->setArrayProperty("indexHintList", $indexHintList);
	}

	public function hasIndexHintList(){
		return $this->hasArrayProperty("indexHintList");
	}

	public function getIndexHintList(){
		return $this->getProperty("indexHintList");
	}

	public function partition($partitionNames){
		$this->setPartitionNames($partitionNames);
		return $this;
	}

	public function setLateralFlag($value = true){
		return $this->setFlag("lateral", true);
	}

	public function getLateralFlag(){
		return $this->getFlag("lateral");
	}

	public function setTableSubquery($subquery){
		$f = __METHOD__;
		if ($subquery == null) {
			unset($this->tableSubquery);
			return null;
		}
		ErrorMessage::unimplemented($f); // XXX TODO
		return $this->tableSubquery = $subquery;
	}

	public function hasTableSubquery(){
		return isset($this->tableSubquery);
	}

	public function getTableSubquery(){
		$f = __METHOD__;
		if (! $this->hasTableSubquery()) {
			Debug::error("{$f} table subquery is undefined");
		}
		return $this->tableSubquery;
	}

	public function withTableSubquery($subquery){
		$this->setTableSubquery($subquery);
		return $this;
	}

	public function getTableReferenceString(){
		$f = __METHOD__;
		try {
			if ($this->hasTableName()) {
				// tbl_name [PARTITION (partition_names)] [[AS] alias] [index_hint_list]
				$string = "";
				if ($this->hasDatabaseName()) {
					$string .= back_quote($this->getDatabaseName()) . ".";
				}
				$string .= back_quote($this->getTableName());
				if ($this->hasPartitionNames()) {
					$string .= " partition (" . implode(',', $this->getPartitionNames()) . ")";
				}
				if ($this->hasAlias()) {
					$string .= " " . $this->getAlias();
				}
				if ($this->hasIndexHintList()) {
					$string .= " " . implode(',', $this->getIndexHintList());
				}
			} elseif ($this->hasTableSubquery()) {
				// [LATERAL] table_subquery [AS] alias [(col_list)]
				$string = "";
				if ($this->getLateralFlag()) {
					$string .= "lateral ";
				}
				$string .= $this->getTableSubquery() . " " . back_quote($this->getAlias());
				if ($this->hasColumnNames()) {
					$string .= " (" . implode_back_quotes(',', $this->getColumnNames()) . ")";
				}
			} elseif ($this->hasJoinExpressions()) {
				// ( table_references )
				return "( " . implode(',', $this->getJoinExpressions()) . " )";
			} else {
				Debug::error("{$f} none of the above");
			}
			return $string;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array{
		return [
			"columnNames" => 's',
			"indexHintList" => IndexHint::class,
			"joinExpressions" => JoinExpression::class,
			"partitionNames" => "s"
		];
	}

	public function dispose(): void{
		parent::dispose();
		unset($this->alias);
		unset($this->properties);
		unset($this->propertyTypes);
		unset($this->tableName);
		unset($this->tableSubquery);
	}

	public function __toString(): string{
		return $this->toSQL();
	}
}
