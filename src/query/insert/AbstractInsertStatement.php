<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\insert;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\claim;
use function JulianSeymour\PHPWebApplicationFramework\implode_back_quotes;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\validateTableName;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\IgnoreFlagBearingTrait;
use JulianSeymour\PHPWebApplicationFramework\query\PrioritizedTrait;
use JulianSeymour\PHPWebApplicationFramework\query\QueryStatement;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnExpressionsTrait;
use JulianSeymour\PHPWebApplicationFramework\query\partition\MultiplePartitionNamesTrait;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatementInterface;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatementTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\FullTableNameTrait;

abstract class AbstractInsertStatement extends QueryStatement implements SelectStatementInterface{

	use ColumnExpressionsTrait;
	use FullTableNameTrait;
	use IgnoreFlagBearingTrait;
	use MultiplePartitionNamesTrait;
	use PrioritizedTrait;
	use SelectStatementTrait;

	protected $insertStatementForm;

	protected $rowCount;

	protected $tableFormDatabaseName;

	protected $tableFormTableName;

	public function __construct(){
		parent::__construct();
		$this->requirePropertyType('partitionNames', 's');
	}

	public function dispose(bool $deallocate=false): void{
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		parent::dispose($deallocate);
		$this->release($this->propertyTypes, $deallocate);
		$this->release($this->columnExpressionLists, $deallocate);
		$this->release($this->insertStatementForm, $deallocate);
		$this->release($this->priorityLevel, $deallocate);
		$this->release($this->rowCount, $deallocate);
		$this->release($this->selectStatement, $deallocate);
		$this->release($this->tableFormDatabaseName, $deallocate);
		$this->release($this->tableFormTableName, $deallocate);
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"ignore"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"ignore"
		]);
	}
	
	public function hasTableFormDatabaseName(): bool{
		return isset($this->tableFormDatabaseName);
	}

	public function getTableFormDatabaseName(): string{
		$f = __METHOD__;
		if(!$this->hasTableFormDatabaseName()){
			Debug::error("{$f} table form database name is undefined");
		}
		return $this->tableFormDatabaseName;
	}

	public function setTableFormDatabaseName(?string $db): ?string{
		if($this->hasTableFormDatabaseName()){
			$this->release($this->tableFormDatabaseName);
		}
		return $this->tableFormDatabaseName = $this->claim($db);
	}

	public function into(...$dbtable): InsertStatement{
		$f = __METHOD__;
		$this->unpackTableName($dbtable);
		return $this;
	}

	public function setInsertStatementForm($form){
		$f = __METHOD__;
		if(!is_int($form)){
			Debug::error("{$f} insert statement form must be an integer");
		}
		switch($form){
			case INSERT_STATEMENT_FORM_SELECT:
			case INSERT_STATEMENT_FORM_SET:
			case INSERT_STATEMENT_FORM_TABLE:
			case INSERT_STATEMENT_FORM_VALUES:
			case INSERT_STATEMENT_FORM_VALUES_ROW:
				break;
			case INSERT_STATEMENT_FORM_UNDEFINED:
			default:
				Debug::error("{$f} invalid insert statement form \"{$form}\"");
				return null;
		}
		if($this->hasInsertStatementForm()){
			$this->release($this->insertStatementForm);
		}
		return $this->insertStatementForm = $this->claim($form);
	}

	public function hasInsertStatementForm():bool{
		return isset($this->insertStatementForm) && is_int($this->insertStatementForm) && $this->insertStatementForm > 0 && $this->insertStatementForm <= 5;
	}

	/**
	 * INSERT_STATEMENT_FORM_VALUES_ROW and INSERT_STATEMENT_FORM_SET must be manually assigned
	 *
	 * @return int
	 */
	public function getInsertStatementForm(){
		if($this->hasInsertStatementForm()){
			return $this->insertStatementForm;
		}elseif($this->hasSelectStatement()){
			return INSERT_STATEMENT_FORM_SELECT;
		}elseif($this->hasTableFormTableName()){
			return INSERT_STATEMENT_FORM_TABLE;
		}elseif($this->hasColumnExpressions()){
			return INSERT_STATEMENT_FORM_VALUES;
		}
		return INSERT_STATEMENT_FORM_UNDEFINED;
	}

	public function withInsertStatementForm($form){
		$this->setInsertStatementForm($form);
		return $this;
	}

	public function setPriority($p){
		$f = __METHOD__;
		
		if(!is_string($p)){
			Debug::error("{$f} priority level must be a string");
		}
		$p = strtolower($p);
		switch($p){
			case PRIORITY_DELAYED:
				$form = $this->getInsertStatementForm();
				if($form === INSERT_STATEMENT_FORM_SELECT || $form === INSERT_STATEMENT_FORM_TABLE){
					Debug::error("{$f} select and table form insert statements cannot have delayed priority");
				}
			case PRIORITY_HIGH:
			case PRIORITY_LOW:
				break;
			default:
				Debug::error("{$f} invalid priority \"{$p}\"");
		}
		if($this->hasPriority()){
			$this->release($this->priorityLevel);
		}
		return $this->priorityLevel = $this->claim($p);
	}

	public function setRowCount($count){
		$f = __METHOD__;
		if(!is_int($count)){
			Debug::error("{$f} row count must be a positive integer");
		}elseif($count < 1){
			Debug::error("{$f} row count must be positive");
		}elseif($this->hasRowCount()){
			$this->release($this->rowCount);
		}
		return $this->rowCount = $this->claim($count);
	}

	public function hasRowCount():bool{
		return isset($this->rowCount) && is_int($this->rowCount) && $this->rowCount >= 1;
	}

	public function getRowCount(){
		if($this->hasRowCount()){
			return $this->rowCount;
		}
		return 1;
	}

	public function withRowCount($count){
		$this->setRowCount($count);
		return $this;
	}

	public function setSelectStatement(?SelectStatement $select): ?SelectStatement{
		$f = __METHOD__;
		if($select == null){
			if($this->hasSelectStatement()){
				$this->release($this->insertStatementForm);
			}
		}elseif(!$select instanceof SelectStatement){
			Debug::error("{$f} input parameter must be SelectStatement or null");
		}
		if($this->hasSelectStatement()){
			$this->release($this->selectStatement);
		}
		$form = $this->getInsertStatementForm();
		switch($form){
			case INSERT_STATEMENT_FORM_UNDEFINED:
			// $this->setInsertStatementForm(INSERT_STATEMENT_FORM_SELECT);
			case INSERT_STATEMENT_FORM_SELECT:
				break;
			default:
				Debug::error("{$f} select statement is incompatible with insert statement form \"{$form}\"");
		}
		return $this->selectStatement = $this->claim($select);
	}

	public function select($select){
		$this->setSelectStatement($select);
		return $this;
	}

	public function setTableFormTableName($name){
		$f = __METHOD__;
		if($name == null){
			if($this->hasTableFormTableName()){
				$this->release($this->insertStatementForm);
			}
		}elseif(!is_string($name)){
			Debug::error("{$f} table form table name must be a string");
		}elseif(!validateTableName($name)){
			Debug::error("{$f} invalid table name \"{$name}\"");
		}
		$form = $this->getInsertStatementForm();
		switch($form){
			case INSERT_STATEMENT_FORM_UNDEFINED:
			// $this->setInsertStatementForm(INSERT_STATEMENT_FORM_TABLE);
			case INSERT_STATEMENT_FORM_TABLE:
				break;
			default:
				Debug::error("{$f} table name is incompatible with insert statement form \"{$form}\"");
		}
		if($this->hasTableFormTableName()){
			$this->release($this->tableFormTableName);
		}
		return $this->tableFormTableName = $this->claim($name);
	}

	public function hasTableFormTableName():bool{
		return isset($this->tableFormTableName) && is_string($this->tableFormTableName) && !empty($this->tableFormTableName);
	}

	public function getTableFormTableName(){
		$f = __METHOD__;
		if(!$this->hasTableFormTableName()){
			Debug::error("{$f} table form table name is undefined");
		}
		return $this->tableFormTableName;
	}

	public function table($name){
		$this->setTableFormTableName($name);
		return $this;
	}

	protected function getValueAssignmentString(){
		// SET assignment_list
		$string = "";
		$form = $this->getInsertStatementForm();
		if($form === INSERT_STATEMENT_FORM_SET){
			$alias = $this->hasAlias() ? $this->getAlias() : null;
			$string .= " set " . $this->getAssignmentListString($this->getColumnExpressions(), $alias);
		}elseif($form === INSERT_STATEMENT_FORM_SELECT){
			// {SELECT ... | TABLE table_name}
			$string .= " select " . $this->getSelectStatement();
		}elseif($form === INSERT_STATEMENT_FORM_TABLE){
			$string .= " table ";
			if($this->hasTableFormDatabaseName()){
				$string .= back_quote($this->getTableFormDatabaseName()) . ".";
			}
			$string .= back_quote($this->getTableFormTableName());
		}
		return $string;
	}

	public function values($values): AbstractInsertStatement{
		return $this->withColumnExpressions($values);
	}

	public function set($expressions): AbstractInsertStatement{
		$this->setInsertStatementForm(INSERT_STATEMENT_FORM_SET);
		return $this->withColumnExpressions($expressions);
	}

	protected function getInsertQueryStatementString(){
		$f = __METHOD__;
		$string = "";
		$form = $this->getInsertStatementForm();
		if($form === INSERT_STATEMENT_FORM_UNDEFINED){
			Debug::error("{$f} unable to determine insert statement form. Please assign column expressions, assignment list, select statement or table form table name");
		}
		// [LOW_PRIORITY | DELAYED | HIGH_PRIORITY]
		if($this->hasPriority()){
			$string .= $this->getPriority() . " ";
		}
		// [IGNORE]
		if($this->getIgnoreFlag()){
			$string .= "ignore ";
		}
		// [INTO] tbl_name
		$string .= "into "; // .$this->getTableName();
		if($this->hasDatabaseName()){
			$string .= back_quote($this->getDatabaseName()) . ".";
		}
		$string .= back_quote($this->getTableName());
		// [PARTITION (partition_name [, partition_name] ...)]
		if($this->hasPartitionNames()){
			$string .= "partition (" . implode(',', $this->getPartitionNames()) . ") ";
		}
		// [(col_name [, col_name] ...)]
		if($form !== INSERT_STATEMENT_FORM_SET && $this->hasColumnNames()){
			$string .= "(" . implode_back_quotes(',', $this->getColumnNames()) . ") ";
		}elseif($form === INSERT_STATEMENT_FORM_VALUES || $form === INSERT_STATEMENT_FORM_VALUES_ROW){ // {VALUES | VALUE} (value_list) [, (value_list)] ...
			$string .= "values ";
			for ($i = 0; $i < $this->getRowCount(); $i ++){
				if($i > 0){
					$string .= ",";
				}
				if($form === INSERT_STATEMENT_FORM_VALUES_ROW){
					// VALUES row_constructor_list
					$string .= "row";
				}
				$string .= "(";
				$j = 0;
				foreach($this->getColumnExpressions() as $expr){
					if($j ++ > 0){
						$string .= ",";
					}
					$string .= $expr;
				}
				$string .= ")";
			}
		}
		return $string;
	}
}
