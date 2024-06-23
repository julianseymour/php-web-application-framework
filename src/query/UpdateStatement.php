<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnExpressionsTrait;
use JulianSeymour\PHPWebApplicationFramework\query\join\JoinExpression;
use JulianSeymour\PHPWebApplicationFramework\query\join\JoinExpressionsTrait;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereConditionalStatement;
use Exception;

class UpdateStatement extends WhereConditionalStatement implements StaticPropertyTypeInterface{

	use ColumnExpressionsTrait;
	use IgnoreFlagBearingTrait;
	use JoinExpressionsTrait;
	use LowPriorityFlagBearingTrait;
	use OrderableTrait;
	use StaticPropertyTypeTrait;

	public function __construct(...$dbtable){
		$f = __METHOD__;
		parent::__construct();
		// $this->requirePropertyType('columnExpressions', ExpressionCommand::class);
		// $this->requirePropertyType('joinExpressions', JoinExpression::class);
		if(isset($dbtable) && count($dbtable) > 0){
			$this->unpackTableName($dbtable);
		}
	}

	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null):array{
		return [
			'joinExpressions' => JoinExpression::class
		];
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"ignore",
			PRIORITY_LOW
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"ignore",
			PRIORITY_LOW
		]);
	}
	
	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasColumnExpressions()){
			$this->setColumnExpressions(replicate($that->getColumnExpressions()));
		}
		if($that->hasJoinExpressions()){
			$this->setJoinExpressions(replicate($that->getJoinExpressions()));
		}
		if($that->hasOrderBy()){
			$this->setOrderBy(...replicate($that->getOrderBy()));
		}
		return $ret;
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		if($this->hasOrderBy()){
			$this->release($this->orderByExpression, $deallocate);
		}
	}
	
	public function set(...$assignments):UpdateStatement{
		if(isset($assignments) && count($assignments) === 1 && is_array($assignments[0]) && !empty($assignments[0])){
			return $this->set(...array_values($assignments[0]));
		}
		$this->setColumnExpressions($assignments);
		return $this;
	}

	public function getTableReferenceCount():int{
		if($this->hasJoinExpressions()){
			return $this->getJoinExpressionCount();
		}
		return 1;
	}

	public function getQueryStatementString(): string{
		$f = __METHOD__;
		try{
			// UPDATE
			$string = "update ";
			// [LOW_PRIORITY]
			if($this->getLowPriorityFlag()){
				$string .= "low_priority ";
			}
			// [IGNORE]
			if($this->getIgnoreFlag()){
				$string .= "ignore ";
			}
			// table_reference
			if($this->hasJoinExpressions() || $this->hasTableName()){
				if($this->hasJoinExpressions()){
					$joins = [];
					foreach($this->getJoinExpressions() as $j){
						if($j instanceof SQLInterface){
							$j = $j->toSQL();
						}
						array_push($joins, $j);
					}
					$string .= implode(',', $joins);
				}elseif($this->hasTableName()){
					if($this->hasDatabaseName()){
						$string .= back_quote($this->getDatabaseName()) . ".";
					}
					$string .= back_quote($this->getTableName());
					$string .= $this->getTableName();
				}else{
					Debug::error("{$f} missing join expressions and table name");
				}
			}
			// SET assignment_list
			$string .= " set " . $this->getAssignmentListString($this->getColumnExpressions());
			// [WHERE where_condition]
			if($this->hasWhereCondition()){
				$where = $this->getWhereCondition();
				if($where instanceof SQLInterface){
					$where = $where->toSQL();
				}
				$string .= " where {$where}";
			}
			if($this->getTableReferenceCount() === 1){
				// [ORDER BY ...]
				if($this->hasOrderBy()){
					$string .= " order by " . $this->getOrderByString();
				}
				// [LIMIT row_count]
				if($this->hasLimit()){
					$string .= " limit " . $this->getLimit();
				}
			}
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function getColumnNames(): array{
		return array_keys($this->getColumnExpressions());
	}
}
