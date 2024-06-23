<?php

namespace JulianSeymour\PHPWebApplicationFramework\query;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\join\JoinExpressionsTrait;
use JulianSeymour\PHPWebApplicationFramework\query\partition\MultiplePartitionNamesTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\MultipleTableNamesTrait;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereConditionalStatement;
use Exception;

class DeleteStatement extends WhereConditionalStatement{

	use AliasTrait;
	use IgnoreFlagBearingTrait;
	use JoinExpressionsTrait;
	use LowPriorityFlagBearingTrait;
	use MultiplePartitionNamesTrait;
	use MultipleTableNamesTrait;
	use OrderableTrait;

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasAlias()){
			$this->setAlias(replicate($that->getAlias()));
		}
		if($that->hasJoinExpressions()){
			$this->setJoinExpressions(replicate($that->getJoinExpressions()));
		}
		if($that->hasOrderBy()){
			$this->setOrderBy(...replicate($that->getOrderBy()));
		}
		if($that->hasPartitionNames()){
			$this->setPartitionNames(replicate($that->getPartitionNames()));
		}
		if($that->hasTableNames()){
			$this->setTableNames(replicate($that->getTableNames()));
		}
		return $ret;
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			".*",
			"ignore",
			PRIORITY_LOW,
			"quick"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			".*",
			"ignore",
			PRIORITY_LOW,
			"quick"
		]);
	}
	
	public function dispose(bool $deallocate=false):void{
		parent::dispose($deallocate);
		if($this->hasAlias()){
			$this->release($this->alias, $deallocate);
		}
		if($this->hasOrderBy()){
			$this->release($this->orderByExpression, $deallocate);
		}
	}
	
	public function from(...$dbtable): DeleteStatement{
		$this->unpackTableName($dbtable);
		return $this;
	}

	public function setAccessCompatibilityFlag(bool $value = true):bool{
		return $this->setFlag(".*", $value);
	}

	public function getAccessCompatibilityFlag():bool{
		return $this->getFlag(".*");
	}

	public function setQuickFlag(bool $value = true):bool{
		return $this->setFlag("quick", $value);
	}

	public function getQuickFlag():bool{
		return $this->getFlag("quick");
	}

	public function quick():DeleteStatement{
		return $this->withFlag("quick", true);
	}

	public function getQueryStatementString():string{
		$f = __METHOD__;
		try{
			// DELETE
			$string = "delete ";
			// [LOW_PRIORITY]
			if($this->getLowPriorityFlag()){
				$string .= PRIORITY_LOW . " ";
			}
			// [QUICK]
			if($this->getQuickFlag()){
				$string .= "quick ";
			}
			// [IGNORE]
			if($this->getIgnoreFlag()){
				$string .= "ignore ";
			}
			if($this->hasJoinExpressions()){
				// tbl_name[.*] [, tbl_name[.*]] ...
				$i = 0;
				foreach($this->getTableNames() as $tableName){
					if($i ++ > 0){
						$string .= ",";
					}
					if($tableName instanceof SQLInterface){
						$string .= $tableName->toSQL();
					}elseif(is_string($tableName)){
						$string .= $tableName;
					}else{
						Debug::error("{$f} table name is neither string nor SQLInterface");
					}
					if($this->getAccessCompatibilityFlag()){
						$string .= ".*";
					}
				}
			}
			// FROM tbl_name
			if($this->hasJoinExpressions() || $this->hasTableName()){
				$string .= "from ";
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
					$string .= $this->getTableName();
				}
			}
			if(!$this->hasJoinExpressions()){
				// [[AS] tbl_alias]
				if($this->hasAlias()){
					$string .= " as " . $this->getAlias();
				}

				// [PARTITION (partition_name [, partition_name] ...)]
				if($this->hasPartitionNames()){
					$string .= " partition " . implode(',', $this->getPartitionNames());
				}
			}
			// [WHERE where_condition]
			if($this->hasWhereCondition()){
				$where = $this->getWhereCondition();
				if($where instanceof SQLInterface){
					$where = $where->toSQL();
				}
				$string .= " where {$where}";
			}
			if(!$this->hasJoinExpressions()){
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
}
