<?php
namespace JulianSeymour\PHPWebApplicationFramework\query\select;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\implode_back_quotes;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\expression\BinaryExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\MultipleExpressionsTrait;
use JulianSeymour\PHPWebApplicationFramework\command\expression\OrCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\VariadicExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\query\CharacterSetTrait;
use JulianSeymour\PHPWebApplicationFramework\query\DistinctionTrait;
use JulianSeymour\PHPWebApplicationFramework\query\LockOptionTrait;
use JulianSeymour\PHPWebApplicationFramework\query\OrderableTrait;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use JulianSeymour\PHPWebApplicationFramework\query\SQLInterface;
use JulianSeymour\PHPWebApplicationFramework\query\UnionClause;
use JulianSeymour\PHPWebApplicationFramework\query\WindowSpecification;
use JulianSeymour\PHPWebApplicationFramework\query\WithClause;
use JulianSeymour\PHPWebApplicationFramework\query\WithClauseTrait;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnAlias;
use JulianSeymour\PHPWebApplicationFramework\query\join\JoinExpression;
use JulianSeymour\PHPWebApplicationFramework\query\join\JoinExpressionsTrait;
use JulianSeymour\PHPWebApplicationFramework\query\join\JoinedTable;
use JulianSeymour\PHPWebApplicationFramework\query\join\TableFactor;
use JulianSeymour\PHPWebApplicationFramework\query\load\ExportOptionsTrait;
use JulianSeymour\PHPWebApplicationFramework\query\partition\MultiplePartitionNamesTrait;
use JulianSeymour\PHPWebApplicationFramework\query\table\MultipleTableNamesTrait;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereConditionalStatement;
use JulianSeymour\PHPWebApplicationFramework\search\MatchFunction;
use Exception;
use mysqli;
use mysqli_stmt;

class SelectStatement extends WhereConditionalStatement 
implements /*CacheableInterface,*/ StaticPropertyTypeInterface
{

	// use CacheableTrait;
	use CharacterSetTrait;
	use DistinctionTrait;
	use ExportOptionsTrait;
	use JoinExpressionsTrait;
	use LockOptionTrait;
	use MultipleExpressionsTrait;
	// use MultipleColumnNamesTrait;
	use MultiplePartitionNamesTrait;
	use MultipleTableNamesTrait;
	use OrderableTrait;
	// use ParameterCountingTrait; //for debug purposes
	use RetainResultFlagBearingTrait;
	use StaticPropertyTypeTrait;
	use WithClauseTrait;

	protected $dumpfilename;

	protected $groupByClause;

	protected $havingCondition;

	protected $lockMode;

	protected $outfilename;

	protected $loadEntryPoint;

	public function __construct(...$expressions)
	{
		parent::__construct();
		// $this->requirePropertyType("expressions", "s");
		// $this->requirePropertyType("exportVariableNames", "s");
		// $this->requirePropertyType("joinExpressions", JoinExpression::class);
		// $this->requirePropertyType("tableNames", "table");
		// $this->requirePropertyType("partitionNames", "s");
		// $this->requirePropertyType("unionClauses", UnionClause::class);
		// $this->requirePropertyType("windowList", WindowSpecification::class);
		if (isset($expressions) && count($expressions) > 0) {
			$this->setExpressions($expressions);
		}
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"unassigned",
			"bigResult",
			"bufferResult",
			"calculateFoundRows",
			"groupWithRollup",
			PRIORITY_HIGH,
			"noCache",
			"ok",
			"orderWithRollup",
			"retainResult",
			"straightJoin",
			"smallResult",
			"subquery",
			"typeSpecified" // if true, the select statement will skip prefixing type specifier in for ColumnAliases in prepareBindExecuteGetStatement
		]);
	}

	public function setLoadEntryPoint($point)
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->setLoadEntryPoint()";
		if ($point == null) {
			unset($this->loadEntryPoint);
			return null;
		} elseif (! is_int($point)) {
			Debug::error("{$f} load entry point must be an integer");
		}
		switch ($point) {
			case LOAD_ENTRY_POINT_UNDEFINED:
			case LOAD_ENTRY_POINT_SELF:
			case LOAD_ENTRY_POINT_INTERSECTION:
				break;
			default:
				Debug::error("{$f} invalid load entry point \"{$point}\"");
		}
		return $this->loadEntryPoint = $point;
	}

	public function hasLoadEntryPoint()
	{
		return isset($this->loadEntryPoint) && is_int($this->loadEntryPoint);
	}

	public function hasMatchFunction()
	{
		if (! $this->hasWhereCondition()) {
			return false;
		}
		$wc = $this->getWhereCondition();
		if ($wc instanceof MatchFunction) {
			return true;
		}
		return $wc->hasMatchFunction();
	}

	public function getLoadEntryPoint()
	{
		if (! $this->hasLoadEntryPoint()) {
			return LOAD_ENTRY_POINT_DEFAULT;
		}
		return $this->loadEntryPoint;
	}

	public function withLoadEntryPoint($point): SelectStatement
	{
		$this->setLoadEntryPoint($point);
		return $this;
	}

	public function setDumpfilename($name)
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->setDumpfilename()";
		if ($name == null) {
			unset($this->dumpfilename);
			return null;
		} elseif (! is_string($name)) {
			Debug::error("{$f} filename must be a string");
		} elseif ($this->getSubqueryFlag()) {
			Debug::error("{$f} dumpfile for subqueries is not allowed");
		}
		return $this->dumpfilename = $name;
	}

	public function hasDumpfilename()
	{
		return isset($this->dumpfilename) && is_string($this->dumpfilename) && ! empty($this->dumpfilename) && ! $this->getSubqueryFlag();
	}

	public function getDumpfilename()
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->getDumpfilename()";
		if (! $this->hasDumpfilename()) {
			Debug::error("{$f} dumpfilename is undefined");
		}
		return $this->dumpfilename;
	}

	public function intoDumpfile($name): SelectStatement
	{
		$this->setDumpfilename($name);
		return $this;
	}

	public function setExportVariableNames($exportVariableNames)
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->setExportVariableNames()";
		if ($exportVariableNames != null && $this->getSubqueryFlag()) {
			Debug::error("{$f} export variable names for subqueries is not allowed");
		}
		return $this->setArrayProperty("exportVariableNames", $exportVariableNames);
	}

	public function hasExportVariableNames()
	{
		return $this->hasArrayProperty("exportVariableNames") && ! $this->getSubqueryFlag();
	}

	public function getExportVariableNames()
	{
		return $this->getProperty("exportVariableNames");
	}

	public function into(...$exportVariableNames): SelectStatement
	{
		$this->setExportVariableNames($exportVariableNames);
		return $this;
	}

	public function setGroupWithRollupFlag($value = true)
	{
		return $this->setFlag("groupWithRollup", $value);
	}

	public function getGroupWithRollupFlag()
	{
		return $this->getFlag("groupWithRollup");
	}

	public function setGroupByClause($columnNames)
	{
		if ($columnNames == null) {
			unset($this->groupByClause);
			return null;
		}
		return $this->groupByClause = $columnNames;
	}

	public function hasGroupBy()
	{
		return isset($this->groupByClause);
	}

	public function getGroupBy()
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->getGroupBy()";
		if (! $this->hasGroupBy()) {
			Debug::error("{$f} group by is undefined");
		}
		return $this->groupByClause;
	}

	public function groupBy($groupBy, $withRollup = null): SelectStatement
	{
		// [GROUP BY {col_name | expr | position}, ... [WITH ROLLUP]]
		$this->setGroupByClause($groupBy);
		if ($withRollup !== null) {
			$this->setGroupWithRollupFlag($withRollup);
		}
		return $this;
	}

	public function setHavingCondition($having)
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->setHavingCondition()";
		if ($having == null) {
			unset($this->havingCondition);
			return null;
		} /*
		   * elseif(!$having instanceof WhereConditionalInterface){
		   * Debug::error("{$f} having condition must implement WhereConditionalInterface");
		   * }
		   */
		return $this->havingCondition = $having;
	}

	public function hasHavingCondition()
	{
		return isset($this->havingCondition);
	}

	public function getHavingCondition()
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->getHavingCondition()";
		if (! $this->hasHavingCondition()) {
			Debug::error("{$f} having condition is undefined");
		}
		return $this->havingCondition;
	}

	public function having($having): SelectStatement
	{
		$this->setHavingCondition($having);
		return $this;
	}

	public function setLockOption($option)
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->setLockOption()";
		if ($option == null) {
			unset($this->lockOption);
			return null;
		} elseif (! is_string($option)) {
			Debug::error("{$f} lock option must be a string");
		}
		$option = strtolower($option);
		switch ($option) {
			case LOCK_OPTION_NOWAIT:
			case LOCK_OPTION_SKIP_LOCKED:
				break;
			default:
				Debug::error("{$f} invalid lock option \"{$option}\"");
		}
		return $this->lockOption = $option;
	}

	public function setOutfilename($name)
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->setOutfilename()";
		if ($name == null) {
			unset($this->characterSet);
			unset($this->exportOptions);
			unset($this->outfilename);
			return null;
		} elseif ($this->getSubqueryFlag()) {
			Debug::error("{$f} outfile for subqueries is not allowed");
		}
		return $this->outfilename = $name;
	}

	public function hasOutfilename()
	{
		return isset($this->outfilename) && is_string($this->outfilename) && ! empty($this->outfilename) && ! $this->getSubqueryFlag();
	}

	public function getOutfilename()
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->getOutfilename()";
		if (! $this->hasOutfilename()) {
			Debug::error("{$f} outfilename is undefined");
		}
		return $this->outfilename;
	}

	public function intoOutfile($name, $charset = null, $exportOptions = null): SelectStatement
	{
		$this->setOutfilename($name);
		if ($charset !== null) {
			$this->setCharacterSet($charset);
		}
		if ($exportOptions !== null) {
			$this->setExportOptions($exportOptions);
		}
		return $this;
	}

	public function setWindowList($windowList)
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->setWindowList()";
		if ($windowList == null) {
			return $this->setArrayProperty("windowList", $windowList);
		}
		foreach ($windowList as $window_name => $window) {
			if (! is_string($window_name)) {
				Debug::error("{$f} array keys must be window name strings");
			} elseif (! $window instanceof WindowSpecification) {
				Debug::error("{$f} array values must be instances of WindowSpecification");
			}
		}
		return $this->setArrayProperty("windowList", $windowList);
	}

	public function hasWindowList()
	{
		return $this->hasArrayProperty("windowList");
	}

	public function getWindowList()
	{
		return $this->getProperty("windowList");
	}

	public function getWindowCount()
	{
		return $this->getArrayPropertyCount("windowList");
	}

	public static function getStatementTypeString(): string
	{
		return "select";
	}

	public function select(...$select): SelectStatement
	{
		/*
		 * $arr = [];
		 * foreach($select as $s){
		 * array_push($arr, $s);
		 * }
		 */
		$this->setExpressions($select);
		return $this;
	}

	public function from(...$dbtable): SelectStatement
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->from()";
		$count = count($dbtable);
		switch ($count) {
			case 1:
				if (! is_string($dbtable[0])) {
					return $this->withJoinExpressions($dbtable);
				}
				$this->setTableName($dbtable[0]);
				break;
			case 2:
				$this->setDatabaseName($dbtable[0]);
				$this->setTableName($dbtable[1]);
				break;
			default:
				Debug::error("{$f} temporarily disabled");
				return $this->withJoinExpressions($dbtable);
		}
		return $this;
	}

	/*
	 * public function getTableName():string{
	 * if($this->hasJoinExpressions()){
	 * return $this->getJoinExpressionString();
	 * }
	 * return parent::getTableName();
	 * }
	 *
	 * public function hasTableName():bool{
	 * return parent::hasTableName() || $this->hasJoinExpressions();
	 * }
	 */

	/**
	 * Builds a SelectStatement with recursive CTE that retrieves all descendants of a node in a table.
	 *
	 * @param string $dbtable
	 *        	| full name of the table where the hierarchical nodes are stored
	 * @param string $foreignKeyName
	 *        	| column name of the foreign key in the child table referencing the parent node
	 * @param string $parentKeyName
	 *        	| column name of the host key in the parent table referenced by the foreign key in the child table
	 * @param VariadicExpressionCommand $expression
	 *        	| optional and/or expression for additional arguments
	 * @return SelectStatement
	 */
	public static function withRecursive($dbtable, $foreignKeyName, $parentKeyName = 'uniqueKey', $expression = null)
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")::withRecursive()";
		// $dbtable = "data.comments"; //name of table containing infinitely recursive hierarchical nodes
		// $foreignKeyName = "parentKey"; //name of parent key linking those nodes together
		// $parentKeyName = 'uniqueKey'; //name of key referenced by parentKey in the parent table
		$cteName = "commonTableExpression"; // name of common table expression, doesn't really matter
		                                    // with recursive commonTableExpression as (
		                                    // select * from data.comments where foreignKeyName='0283ab5d0368c91c3f48400d5a97b856740f193a'
		                                    // union all
		                                    // select childComment.* from
		                                    // data.comments as childComment
		                                    // join
		                                    // commonTableExpression as parentComment
		                                    // on childComment.foreignKeyName = parentComment.uniqueKey
		                                    // ) select * from commonTableExpression;
		$where = new BinaryExpressionCommand("child." . back_quote($foreignKeyName) . OPERATOR_EQUALS, "parent." . back_quote($parentKeyName));
		if ($expression === null) {
			$expression = $where;
		} elseif ($expression instanceof VariadicExpressionCommand) {
			$expression->pushParameters($where);
		} else {
			Debug::error("{$f} neither of the above");
		}
		return QueryBuilder::select()->from($cteName)->with(WithClause::recursive($cteName, QueryBuilder::select()->from($dbtable)
			->where(new WhereCondition($foreignKeyName, OPERATOR_EQUALS))
			->unionAll(QueryBuilder::select("child.*")->from(JoinedTable::join(TableFactor::create()->withTableName($dbtable)
			->as("child"), TableFactor::create()->withTableName($cteName)
			->as("parent"), $expression)))));
	}

	public function setSubqueryFlag($value = true)
	{
		return $this->setFlag("subquery", $value);
	}

	public function getSubqueryFlag()
	{
		return $this->getFlag("subquery");
	}

	public function setBigResultFlag($value = true)
	{
		return $this->setFlag("bigResult", $value);
	}

	public function getBigResultFlag()
	{
		return $this->getFlag("bigResult");
	}

	public function bigResult($value = true): SelectStatement
	{
		$this->setBigResultFlag($value);
		return $this;
	}

	public function setBufferResultFlag($value = true)
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->setBufferResultFlag()";
		if ($value && $this->getSubqueryFlag()) {
			Debug::error("{$f} buffer results flag cannot be set on subqueries");
		}
		return $this->setFlag("bufferResult", $value);
	}

	public function getBufferResultFlag()
	{
		return $this->getFlag("bufferResult");
	}

	public function bufferResult($value = true): SelectStatement
	{
		$this->setBufferResultFlag($value);
		return $this;
	}

	public function setCalculateFoundRowsFlag($value = true)
	{
		return $this->setFlag("calculateFoundRows", $value);
	}

	public function getCalculateFoundRowsFlag()
	{
		return $this->getFlag("calculateFoundRows");
	}

	public function calculateFoundRows($value = true): SelectStatement
	{
		$this->setCalculateFoundRowsFlag($value);
		return $this;
	}

	public function setHighPriorityFlag($value = true)
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->setHighPriorityFlag()";
		if ($value && ($this->getSubqueryFlag() || $this->hasUnionClauses())) {
			Debug::error("{$f} high priority cannot be used in selet statements that are part of unions");
		}
		return $this->setFlag(PRIORITY_HIGH, $value);
	}

	public function getHighPriorityFlag()
	{
		return $this->getFlag(PRIORITY_HIGH);
	}

	public function highPriority($value = true): SelectStatement
	{
		$this->setHighPriorityFlag($value);
		return $this;
	}

	public function setNoCacheFlag($value = true)
	{
		return $this->setFlag("noCache", $value);
	}

	public function getNoCacheFlag()
	{
		return $this->getFlag("noCache");
	}

	public function noCache($value = true): SelectStatement
	{
		$this->setNoCacheFlag($value);
		return $this;
	}

	public function setOrderWithRollupFlag($value = true)
	{
		return $this->setFlag("orderWithRollup", $value);
	}

	public function getOrderWithRollupFlag()
	{
		return $this->getFlag("orderWithRollup");
	}

	public function setStraightJoinFlag($value = true)
	{
		return $this->setFlag("straightJoin", $value);
	}

	public function getStraightJoinFlag()
	{
		return $this->getFlag("straightJoin");
	}

	/*
	 * public function straightJoin($value=true):SelectStatement{
	 * $this->setStraightJoinFlag($value);
	 * return $this;
	 * }
	 */
	public function setSmallResultFlag($value = true)
	{
		return $this->setFlag("smallResult", $value);
	}

	public function getSmallResultFlag()
	{
		return $this->getFlag("smallResult");
	}

	public function smallResult($value = true): SelectStatement
	{
		$this->setSmallResultFlag($value);
		return $this;
	}

	public function setLockMode($mode)
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->setLockMode()";
		if ($mode == null) {
			unset($this->lockMode);
			unset($this->lockOption);
			$this->setArrayProperty("lockTableNames", null);
			return null;
		} elseif (! is_string($mode)) {
			Debug::error("{$f} lock mode must be a string");
		} elseif ($this->getSubqueryFlag()) {
			Debug::error("{$f} not 100% sure but I believe locking tables in a subquery is not legal so fuck off");
		}
		$mode = strtolower($mode);
		switch ($mode) {
			case LOCK_IN_SHARE_MODE:
			case LOCK_FOR_SHARE:
			case LOCK_FOR_UPDATE:
				break;
			default:
				Debug::error("{$f} invalid lock mode \"{$mode}\"");
		}
		return $this->lockMode = $mode;
	}

	public function hasLockMode()
	{
		return isset($this->lockMode);
	}

	public function getLockMode()
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->getLockMode()";
		if (! $this->hasLockMode()) {
			Debug::error("{$f} lock mode is undefined");
		}
		return $this->lockMode;
	}

	public function setUnionClauses($unionClauses)
	{
		return $this->setArrayProperty("unionClauses", $unionClauses);
	}

	public function hasUnionClauses()
	{
		return $this->hasArrayProperty("unionClauses");
	}

	public function pushUnionClause(...$unionClauses)
	{
		return $this->pushArrayProperty("unionClauses", ...$unionClauses);
	}

	public function mergeUnionClauses($unionClauses)
	{
		return $this->mergeArrayProperty("unionClauses", $unionClauses);
	}

	public function getUnionClauses()
	{
		return $this->getProperty("unionClauses");
	}

	public function union(...$selectStatements): SelectStatement
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->union()";
		if (! isset($selectStatements)) {
			Debug::error("{$f} select statements undefined");
		}
		foreach ($selectStatements as $selectStatement) {
			$union = new UnionClause($selectStatement);
			$this->pushUnionClause($union);
		}
		return $this;
	}

	public function unionAll(...$selectStatements): SelectStatement
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->unionAll()";
		if (! isset($selectStatements)) {
			Debug::error("{$f} select statements undefined");
		}
		foreach ($selectStatements as $selectStatement) {
			$this->pushUnionClause(UnionClause::all($selectStatement));
		}
		return $this;
	}

	public function unionDistinct(...$selectStatements): SelectStatement
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->unionDistinct()";
		if (! isset($selectStatements)) {
			Debug::error("{$f} select statements undefined");
		}
		foreach ($selectStatements as $selectStatement) {
			$this->pushUnionClause(UnionClause::distinct($selectStatement));
		}
		return $this;
	}

	public function pushWindows(...$values)
	{
		return $this->pushArrayProperty("windowList", ...$values);
	}

	public function window($windows)
	{
		$this->setWindowList($windows);
		return $this;
	}

	public function of(...$tableNames)
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->of()";
		if (! $this->hasLockMode()) {
			Debug::error("{$f} don't call this function if lock mode is undefined");
		}
		$this->setTableNames($tableNames);
		return $this;
	}

	public function lock($mode)
	{
		$this->setLockMode($mode);
		return $this;
	}

	public function getQueryStatementString()
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->getQueryStatementString()";
		try {
			$print = false;
			if ($this->hasWithClause()) {
				$with = $this->getWithClause();
				if ($with instanceof SQLInterface) {
					$with = $with->toSQL();
				}
				$string = "{$with} ";
			}
			$string = "";
			if ($this->hasUnionClauses()) {
				$string .= "(";
			}
			$string .= "select ";
			// [ALL | DISTINCT | DISTINCTROW ]
			if ($this->hasDistinction()) {
				$string .= $this->getDistinction() . " ";
			}
			// [HIGH_PRIORITY]
			if ($this->getHighPriorityFlag()) {
				$string .= PRIORITY_HIGH . " ";
			}
			// [STRAIGHT_JOIN]
			if ($this->getStraightJoinFlag()) {
				$string .= "straight_join ";
			}
			// [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT]
			if ($this->getSmallResultFlag()) {
				$string .= "sql_small_result ";
			}
			if ($this->getBigResultFlag()) {
				$string .= "sql_big_result ";
			}
			if ($this->getBufferResultFlag()) {
				$string .= "sql_buffer_result ";
			}
			// [SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS]
			if ($this->getNoCacheFlag()) {
				$string .= "sql_no_cache ";
			}
			if ($this->getCalculateFoundRowsFlag()) {
				$string .= "sql_calc_found_rows ";
			}
			// select_expr [, select_expr] ...
			if ($this->hasExpressions()) {
				$expressions = [];
				foreach ($this->getExpressions() as $e) {
					if ($e instanceof SQLInterface) {
						$e = $e->toSQL();
					} else {
						$e = back_quote($e);
					}
					array_push($expressions, $e);
				}
				$string .= implode(',', $expressions);
			} else {
				$string .= "*";
			}
			// [FROM table_references [PARTITION partition_list]]
			if ($this->hasJoinExpressions() || $this->hasTableName()) {
				$string .= " from ";
				if ($this->hasJoinExpressions()) {
					$je = [];
					foreach ($this->getJoinExpressions() as $j) {
						if ($j instanceof SQLInterface) {
							$j = $j->toSQL();
						}
						array_push($je, $j);
					}
					$string .= implode(' ', $je);
				} elseif ($this->hasTableName()) {
					if ($this->hasDatabaseName()) {
						$string .= back_quote($this->getDatabaseName()) . ".";
					}
					$string .= back_quote($this->getTableName());
				}
				if ($this->hasPartitionNames()) {
					$string .= " " . implode(',', $this->getPartitionNames());
				}
			}
			// [WHERE where_condition]
			if ($this->hasWhereCondition()) {
				/*
				 * $where = $this->getWhereCondition();
				 * if($where->getOperator() === OPERATOR_LIKE){
				 * $string .= " like ";
				 * }else{
				 * $string .= " where ";
				 * }
				 */
				$where = $this->getWhereCondition();
				if ($where instanceof SQLInterface) {
					$where = $where->toSQL();
				}
				$string .= " where {$where}";
			}
			// [GROUP BY {col_name | expr | position}, ... [WITH ROLLUP]]
			if ($this->hasGroupBy()) {
				$string .= " group by ";
				$group_by = $this->getGroupBy();
				if (is_array($group_by)) {
					$string .= implode(',', $group_by);
				} else {
					$string .= $group_by;
				}
				if ($this->getGroupWithRollupFlag()) {
					$string .= " with rollup";
				}
			}
			// [HAVING where_condition]
			if ($this->hasHavingCondition()) {
				$having = $this->getHavingCondition();
				if ($having instanceof SQLInterface) {
					$having = $having->toSQL();
				}
				$string .= " having {$having}";
			}
			// [WINDOW window_name AS (window_spec) [, window_name AS (window_spec)] ...]
			if ($this->hasWindowList()) {
				$string .= " window ";
				$i = 0;
				foreach ($this->getWindowList() as $window_name => $window) {
					if ($i ++ > 0) {
						$string .= ",";
					}
					if ($window instanceof SQLInterface) {
						$window = $window->toSQL();
					}
					$string .= "{$window_name} as ({$window})";
				}
			}
			// [ORDER BY {col_name | expr | position} [ASC | DESC], ... [WITH ROLLUP]]
			if ($this->hasOrderBy()) {
				$string .= " order by " . $this->getOrderByString();
				if ($this->getOrderWithRollupFlag()) {
					$string .= " with rollup";
				}
			}
			// [LIMIT {
			// [offset,] row_count | row_count OFFSET offset
			// }]
			if ($this->hasLimit()) {
				$string .= " limit " . $this->getLimit();
				if ($this->hasOffset()) {
					$string .= " offset " . $this->getOffset();
				}
			}
			// union clauses go here I guess?
			if ($this->hasUnionClauses()) {
				$union = [];
				foreach ($this->getUnionClauses() as $u) {
					if ($u instanceof SQLInterface) {
						$u = $u->toSQL();
					}
					array_push($union, $u);
				}
				$string .= ") " . implode(' ', $union);
			}
			// [FOR {UPDATE | SHARE} [OF tbl_name [, tbl_name] ...] [NOWAIT | SKIP LOCKED] | LOCK IN SHARE MODE]
			if ($this->hasLockMode()) {
				$lock = $this->getLockMode();
				if ($lock === LOCK_IN_SHARE_MODE) {
					$string .= " lock in share mode";
				} else {
					$string .= " for ";
					switch ($lock) {
						case LOCK_FOR_UPDATE:
							$string .= "update";
							break;
						case LOCK_FOR_SHARE:
							$string .= "share";
							break;
						default:
							Debug::error("{$f} invalid lock mode \"{$lock}\"");
					}
					if ($this->hasTableNames()) {
						$string .= " of " . implode_back_quotes(',', $this->getTableNames()); // XXX needs to be escaped
					}
					if ($this->hasLockOption()) {
						$string .= " " . $this->getLockOption();
					}
				}
			}
			// [{
			// INTO OUTFILE 'file_name' [CHARACTER SET charset_name] export_options
			// | INTO DUMPFILE 'file_name'
			// | INTO var_name [, var_name] ...
			// }]
			if ($this->hasOutfilename()) {
				$string .= " into outfile " . single_quote($this->getOutfilename());
				if ($this->hasCharacterSet()) {
					$string .= " character set " . $this->getCharacterSet();
				}
				if ($this->hasExportOptions()) {
					$string .= " " . $this->getExportOptions(); // The syntax for the export_options part of the statement consists of the same FIELDS and LINES clauses that are used with the LOAD DATA statement
				}
			} elseif ($this->hasDumpfilename()) {
				$string .= " into dumpfile " . single_quote($this->getDumpfilename());
			} elseif ($this->hasExportVariableNames()) {
				$string .= " into " . implode(',', $this->getExportVariableNames());
			}
			if ($print) {
				Debug::print("{$f} returning \"{$string}\"");
			}
			return $string;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	protected function trimConditionalTypeSpecifier($typedef)
	{
		$count = $this->getUnconditionalParameterCount();
		if ($count > 0) {
			return substr($typedef, 0, $count);
		}
		return parent::trimConditionalTypeSpecifier($typedef);
	}

	protected function trimConditionalParameters($params)
	{
		return array_slice($params, 0, $this->getUnconditionalParameterCount());
	}

	protected function getUnconditionalParameterCount()
	{
		$count = 0;
		if ($this->hasExpressions()) {
			foreach ($this->getExpressions() as $expr) {
				if ($expr instanceof ColumnAlias) {
					$cn = $expr->getExpression();
					if ($cn instanceof SelectStatement && $cn->hasParameters()) {
						$count += $cn->getParameterCount();
					}
				}
			}
		}
		return $count;
	}

	public function prepareBindExecuteGetStatement(mysqli $mysqli, string $typedef, ...$params): ?mysqli_stmt
	{
		$f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->prepareBindExdcuteGetStatement()";
		try {
			$print = $this->getDebugFlag();
			if (isset($params) && ! empty($params)) {
				$new_params = array(
					...$params
				);
			} else {
				$new_params = [];
			}
			$new_typedef = $typedef;
			if (! $this->getFlag("typeSpecified") && $this->hasExpressions()) {
				$prefix = "";
				$temp = [];
				foreach ($this->getExpressions() as $expr) {
					if ($expr instanceof ColumnAlias) {
						$cn = $expr->getExpression();
						if ($cn instanceof SelectStatement && $cn->hasTypeSpecifier()) {
							$count = $cn->getParameterCount();
							if ($print) {
								Debug::print("{$f} prepending {$count} parameters from a subquery expression");
							}
							$prefix .= $cn->getTypeSpecifier();
							array_push($temp, ...$cn->getParameters());
						} elseif ($print) {
							Debug::print("{$f} column expression is not a select statement, or it does not have a typespecifier");
						}
					} elseif ($print) {
						Debug::print("{$f} column expressions is not an instanceof ColumnAlias");
					}
				}
				if ($typedef !== "") {
					$new_typedef = "{$prefix}{$typedef}";
					$new_params = array_merge($temp, $params);
				} else {
					if ($print) {
						Debug::print("{$f} initial type definition string is empty");
					}
					$new_typedef = $prefix;
					$new_params = $temp;
				}
			} elseif ($print) {
				Debug::print("{$f} this statement does not have any expressions");
			}
			if ($print) {
				Debug::print("{$f} type definition string is \"{$new_typedef}\"; about to print parameters");
				Debug::printArray($new_params);
			}
			return parent::prepareBindExecuteGetStatement($mysqli, $new_typedef, ...$new_params);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function dispose(): void
	{
		unset($this->properties);
		unset($this->propertyTypes);
		unset($this->characterSet);
		unset($this->distinction);
		unset($this->dumpfilename);
		unset($this->exportOptions);
		unset($this->groupByClause);
		unset($this->havingCondition);
		unset($this->lockOption);
		unset($this->outfilename);
		parent::dispose();
	}

	public function replicate(): SelectStatement
	{
		$replica = new static();
		if ($this->hasCharacterSet()) {
			$replica->setCharacterSet($this->getCharacterSet());
		}
		if ($this->hasDistinction()) {
			$replica->setDistinction($this->getDistinction());
		}
		if ($this->hasDumpfilename()) {
			$replica->setDumpfilename($this->getDumpfilename());
		}
		if ($this->hasColumnTerminator()) {
			$replica->setColumnTerminator($this->getColumnTerminator());
		}
		if ($this->hasEnclosureCharacter()) {
			$replica->setEnclosureCharacter($this->getEnclosureCharacter());
			if ($this->getOptionallyEnclosedFlag()) {
				$replica->setOptionallyEnclosedFlag(true);
			}
		}
		if ($this->hasColumnTerminator()) {
			$replica->setColumnTerminator($this->getColumnTerminator());
		}
		if ($this->hasEscapeCharacter()) {
			$replica->setEscapeCharacter($this->getEscapeCharacter());
		}
		if ($this->hasGroupBy()) {
			$replica->setGroupByClause($this->getGroupBy());
		}
		if ($this->hasHavingCondition()) {
			$replica->setHavingCondition($this->getHavingCondition());
		}
		if ($this->hasLockOption()) {
			$replica->setLockOption($this->getLockOption());
		}
		if ($this->hasOutfilename()) {
			$replica->setOutfilename($this->getOutfilename());
		}
		if ($this->hasExpressions()) {
			$replica->setExpressions($this->getExpressions());
		}
		if ($this->hasExportVariableNames()) {
			$replica->setExportVariableNames($this->getExportVariableNames());
		}
		if ($this->hasJoinExpressions()) {
			$replica->setJoinExpressions($this->getJoinExpressions());
		}
		if ($this->hasTableNames()) {
			$replica->setTableNames($this->getTableNames());
		}
		if ($this->hasPartitionNames()) {
			$replica->setPartitionNames($this->getPartitionNames());
		}
		if ($this->hasUnionClauses()) {
			$replica->setUnionClauses($this->getUnionClauses());
		}
		if ($this->hasWindowList()) {
			$replica->setWindowList($this->getWindowList());
		}
		if ($this->hasTypeSpecifier()) {
			$replica->setTypeSpecifier($this->getTypeSpecifier());
		}
		if ($this->hasParameters()) {
			$replica->setParameters($this->getParameters());
		}

		if ($this->hasLimit()) {
			$replica->setLimit($this->getLimit());
			if ($this->hasOffset()) {
				$replica->setOffset($this->getOffset());
			}
		}
		if ($this->hasTableName()) {
			$replica->setTableName($this->getTableName());
		}
		if ($this->hasWhereCondition()) {
			$replica->setWhereCondition($this->getWhereCondition());
		}
		return $replica;
	}

	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array
	{
		return [
			"expressions" => new OrCommand("s", Command::class, ColumnAlias::class),
			"exportVariableNames" => "s",
			"joinExpressions" => JoinExpression::class,
			"tableNames" => "table",
			"partitionNames" => "s",
			"unionClauses" => UnionClause::class,
			"windowList" => WindowSpecification::class
		];
	}

	/*
	 * public function setTypeSpecifier(?string $typedef):?string{
	 * $f = __METHOD__; //SelectStatement::getShortClass()."(".static::getShortClass().")->setTypeSpecifier()";
	 * if($this->getDebugFlag()){
	 * Debug::printStackTraceNoExit("{$f} entered");
	 * }
	 * return parent::setTypeSpecifier($typedef);
	 * }
	 */

	/*
	 * public function getSuperflatWhereConditionArray():?array{
	 * $wheres = [];
	 * if($this->hasExpressions()){
	 * foreach($this->getExpressions() as $expr){
	 * if($expr instanceof GetDeclaredVariableCommand){
	 * continue;
	 * }elseif($expr instanceof CountCommand){
	 * continue;
	 * }elseif(is_string($expr)){
	 * continue;
	 * }elseif($expr instanceof SumCommand){
	 * continue;
	 * }
	 * $subquery_conditions = $expr->getSuperflatWhereConditionArray();
	 * if(!empty($subquery_conditions)){
	 * array_push($wheres, ...$subquery_conditions);
	 * }
	 * }
	 * }
	 * if(!$this->hasWhereCondition()){
	 * return $wheres;
	 * }
	 * $flat = $this->getWhereCondition()->getSuperflatWhereConditionArray();
	 * if(!empty($flat)){
	 * array_push($wheres, ...$flat);
	 * }
	 * return $wheres;
	 * }
	 */
}
