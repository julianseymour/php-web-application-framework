<?php

namespace JulianSeymour\PHPWebApplicationFramework\query\select;

use function JulianSeymour\PHPWebApplicationFramework\back_quote;
use function JulianSeymour\PHPWebApplicationFramework\deallocate;
use function JulianSeymour\PHPWebApplicationFramework\replicate;
use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\expression\AndCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\BinaryExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\MultipleExpressionsTrait;
use JulianSeymour\PHPWebApplicationFramework\command\expression\OrCommand;
use JulianSeymour\PHPWebApplicationFramework\command\expression\VariadicExpressionCommand;
use JulianSeymour\PHPWebApplicationFramework\command\variable\GetDeclaredVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\IntersectionData;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
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
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnAliasExpression;
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
implements /*CacheableInterface,*/ StaticPropertyTypeInterface{

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

	protected $loadEntryPoint;

	protected $lockMode;

	protected $lockOption;
	
	protected $outfilename;

	protected $parentKeyName;
	
	protected static function getExcludedConstructorFunctionNames():?array{
		return array_merge(parent::getExcludedConstructorFunctionNames(), ["select", "selectStatic"]);
	}
	
	public function __construct(...$expressions){
		parent::__construct();
		if(isset($expressions) && count($expressions) > 0){
			$this->setExpressions($expressions);
		}
	}

	public function copy($that):int{
		$ret = parent::copy($that);
		if($that->hasCharacterSet()){
			$this->setCharacterSet(replicate($that->getCharacterSet()));
		}
		if($that->hasColumnTerminator()){
			$this->setColumnTerminator(replicate($that->getColumnTerminator()));
		}
		if($that->hasDistinction()){
			$this->setDistinction(replicate($that->getDistinction()));
		}
		if($that->hasDumpfilename()){
			$this->setDumpfilename(replicate($that->getDumpfilename()));
		}
		if($that->hasEnclosureCharacter()){
			$this->setEnclosureCharacter(replicate($that->getEnclosureCharacter()));
			if($that->getOptionallyEnclosedFlag()){
				$this->setOptionallyEnclosedFlag(true);
			}
		}
		if($that->hasEscapeCharacter()){
			$this->setEscapeCharacter(replicate($that->getEscapeCharacter()));
		}
		if($that->hasExportVariableNames()){
			$this->setExportVariableNames(replicate($that->getExportVariableNames()));
		}
		if($that->hasExpressions()){
			$this->setExpressions(replicate($that->getExpressions()));
		}
		if($that->hasGroupBy()){
			$this->setGroupByClause(replicate($that->getGroupBy()));
		}
		if($this->hasHavingCondition()){
			$this->setHavingCondition(replicate($that->getHavingCondition()));
		}
		if($that->hasJoinExpressions()){
			$this->setJoinExpressions(replicate($that->getJoinExpressions()));
		}
		if($that->hasLockOption()){
			$this->setLockOption(replicate($that->getLockOption()));
		}
		if($that->hasOrderBy()){
			$this->setOrderBy(...replicate($that->getOrderBy()));
		}
		if($that->hasOutfilename()){
			$this->setOutfilename(replicate($that->getOutfilename()));
		}
		if($that->hasPartitionNames()){
			$this->setPartitionNames(replicate($that->getPartitionNames()));
		}
		if($that->hasTableNames()){
			$this->setTableNames(replicate($that->getTableNames()));
		}
		if($that->hasUnionClauses()){
			$this->setUnionClauses(replicate($that->getUnionClauses()));
		}
		if($that->hasWindowList()){
			$this->setWindowList(replicate($that->getWindowList()));
		}
		return $ret;
	}
	
	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"bigResult",
			"bufferResult",
			"calculateFoundRows",
			"groupWithRollup",
			PRIORITY_HIGH,
			"noCache",
			"orderWithRollup",
			"retainResult",
			"straightJoin",
			"smallResult",
			"subquery",
			"typeSpecified", // if true, the select statement will skip prefixing type specifier in for ColumnAliases in prepareBindExecuteGetStatement
			"unassigned"
		]);
	}

	public static function getCopyableFlags():?array{
		return array_merge(parent::getCopyableFlags(), [
			"bigResult",
			"bufferResult",
			"calculateFoundRows",
			"groupWithRollup",
			PRIORITY_HIGH,
			"noCache",
			"orderWithRollup",
			"straightJoin",
			"smallResult",
			"subquery"
		]);
	}
	
	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array{
		$or = new OrCommand("s", Command::class, ColumnAlias::class, SQLInterface::class);
		return [
			"expressions" => $or,
			"exportVariableNames" => "s",
			"joinExpressions" => JoinExpression::class,
			"tableNames" => "table",
			"partitionNames" => "s",
			"unionClauses" => UnionClause::class,
			"windowList" => WindowSpecification::class
		];
	}
	
	public final function dispose(bool $deallocate=false): void{
		$f = __METHOD__;
		$print = false && $this->getDebugFlag();
		if($print){
			$decl = $this->getDeclarationLine();
			Debug::checkMemoryUsage("Before disposing of select statement declared {$decl}", 112000000, true);
		}
		parent::dispose($deallocate);
		$ds = $this->getDebugString();
		$this->release($this->characterSet, $deallocate);
		$this->release($this->columnTerminatorString, $deallocate);
		$this->release($this->distinction, $deallocate);
		$this->release($this->dumpfilename, $deallocate);
		$this->release($this->enclosureCharacter, $deallocate);
		$this->release($this->escapeCharacter, $deallocate);
		$this->release($this->groupByClause, $deallocate);
		$this->release($this->havingCondition, $deallocate);
		$this->release($this->loadEntryPoint, $deallocate);
		$this->release($this->lockMode, $deallocate);
		$this->release($this->lockOption, $deallocate);
		$this->release($this->orderByExpression, $deallocate);
		$this->release($this->outfilename, $deallocate);
		$this->release($this->parentKeyName, $deallocate);
		$this->release($this->withClause, $deallocate);
	}
	
	public function getParentKeyName():string{
		$f = __METHOD__;
		if(!$this->hasParentKeyName()){
			Debug::error("{$f} parent key name is undefined for this ".$this->getDebugString());
		}
		return $this->parentKeyName;
	}
	
	public function setParentKeyName(?string $pkn):?string{
		if($this->hasParentKeyName()){
			$this->release($this->parentKeyName);
		}
		return $this->parentKeyName = $this->claim($pkn);
	}
	
	public function hasParentKeyName():bool{
		return isset($this->parentKeyName);
	}
	
	public function withParentKeyName(string $pkn):SelectStatement{
		$this->setParentKeyName($pkn);
		return $this;
	}
	
	public function setExpressions($values){
		return $this->setArrayProperty('expressions', $values);
	}
	
	public function setLoadEntryPoint($point){
		$f = __METHOD__;
		if(!is_int($point)){
			Debug::error("{$f} load entry point must be an integer");
		}
		switch($point){
			case LOAD_ENTRY_POINT_UNDEFINED:
			case LOAD_ENTRY_POINT_SELF:
			case LOAD_ENTRY_POINT_INTERSECTION:
				break;
			default:
				Debug::error("{$f} invalid load entry point \"{$point}\"");
		}
		if($this->hasLoadEntryPoint()){
			$this->release($this->loadEntryPoint);
		}
		return $this->loadEntryPoint = $this->claim($point);
	}

	public function hasLoadEntryPoint():bool{
		return isset($this->loadEntryPoint) && is_int($this->loadEntryPoint);
	}

	public function hasMatchFunction():bool{
		if(!$this->hasWhereCondition()){
			return false;
		}
		$wc = $this->getWhereCondition();
		if($wc instanceof MatchFunction){
			return true;
		}
		return $wc->hasMatchFunction();
	}

	public function getLoadEntryPoint(){
		if(!$this->hasLoadEntryPoint()){
			return LOAD_ENTRY_POINT_DEFAULT;
		}
		return $this->loadEntryPoint;
	}

	public function withLoadEntryPoint($point): SelectStatement{
		$this->setLoadEntryPoint($point);
		return $this;
	}

	public function setDumpfilename($name){
		$f = __METHOD__;
		if(!is_string($name)){
			Debug::error("{$f} filename must be a string");
		}elseif($this->getSubqueryFlag()){
			Debug::error("{$f} dumpfile for subqueries is not allowed");
		}elseif($this->hasDumpfilename()){
			$this->release($this->dumpfilename);
		}
		return $this->dumpfilename = $this->claim($name);
	}

	public function hasDumpfilename():bool{
		return isset($this->dumpfilename) && is_string($this->dumpfilename) && !empty($this->dumpfilename) && ! $this->getSubqueryFlag();
	}

	public function getDumpfilename(){
		$f = __METHOD__;
		if(!$this->hasDumpfilename()){
			Debug::error("{$f} dumpfilename is undefined");
		}
		return $this->dumpfilename;
	}

	public function intoDumpfile($name): SelectStatement{
		$this->setDumpfilename($name);
		return $this;
	}

	public function setExportVariableNames($exportVariableNames){
		$f = __METHOD__;
		if($exportVariableNames != null && $this->getSubqueryFlag()){
			Debug::error("{$f} export variable names for subqueries is not allowed");
		}
		return $this->setArrayProperty("exportVariableNames", $exportVariableNames);
	}

	public function hasExportVariableNames():bool{
		return $this->hasArrayProperty("exportVariableNames") && ! $this->getSubqueryFlag();
	}

	public function getExportVariableNames(){
		return $this->getProperty("exportVariableNames");
	}

	public function into(...$exportVariableNames): SelectStatement{
		$this->setExportVariableNames($exportVariableNames);
		return $this;
	}

	public function setGroupWithRollupFlag(bool $value = true):bool{
		return $this->setFlag("groupWithRollup", $value);
	}

	public function getGroupWithRollupFlag():bool{
		return $this->getFlag("groupWithRollup");
	}

	public function setGroupByClause($columnNames){
		if($this->hasGroupBy()){
			$this->release($this->groupByClause);
		}
		return $this->groupByClause = $this->claim($columnNames);
	}

	public function hasGroupBy():bool{
		return isset($this->groupByClause);
	}

	public function getGroupBy(){
		$f = __METHOD__;if(!$this->hasGroupBy()){
			Debug::error("{$f} group by is undefined");
		}
		return $this->groupByClause;
	}

	public function groupBy($groupBy, $withRollup = null): SelectStatement{
		// [GROUP BY {col_name | expr | position}, ... [WITH ROLLUP]]
		$this->setGroupByClause($groupBy);
		if($withRollup !== null){
			$this->setGroupWithRollupFlag($withRollup);
		}
		return $this;
	}

	public function setHavingCondition($having){
		if($this->hasHavingCondition()){
			$this->release($this->havingCondition);
		}
		return $this->havingCondition = $this->claim($having);
	}

	public function hasHavingCondition():bool{
		return isset($this->havingCondition);
	}

	public function getHavingCondition(){
		$f = __METHOD__;
		if(!$this->hasHavingCondition()){
			Debug::error("{$f} having condition is undefined");
		}
		return $this->havingCondition;
	}

	public function having($having): SelectStatement{
		$this->setHavingCondition($having);
		return $this;
	}

	public function setLockOption(?string $option):?string{
		$f = __METHOD__;
		if(!is_string($option)){
			Debug::error("{$f} lock option must be a string");
		}
		$option = strtolower($option);
		switch($option){
			case LOCK_OPTION_NOWAIT:
			case LOCK_OPTION_SKIP_LOCKED:
				break;
			default:
				Debug::error("{$f} invalid lock option \"{$option}\"");
		}
		if($this->hasLockOption()){
			$this->release($this->lockOption);
		}
		return $this->lockOption = $this->claim($option);
	}

	public function setOutfilename($name){
		$f = __METHOD__;
		if($this->getSubqueryFlag()){
			Debug::error("{$f} outfile for subqueries is not allowed");
		}elseif($this->hasOutfilename()){
			$this->release($this->outfilename);
		}
		if($this->hasCharacterSet()){
			$this->release($this->characterSet);
		}
		if($this->hasExportOptions()){
			$this->release($this->columnTerminatorString);
			$this->release($this->enclosureCharacter);
			$this->release($this->escapeCharacter);
		}
		return $this->outfilename = $this->claim($name);
	}

	public function hasOutfilename():bool{
		return isset($this->outfilename) && is_string($this->outfilename) && !empty($this->outfilename) && ! $this->getSubqueryFlag();
	}

	public function getOutfilename():string{
		$f = __METHOD__;
		if(!$this->hasOutfilename()){
			Debug::error("{$f} outfilename is undefined");
		}
		return $this->outfilename;
	}

	public function intoOutfile($name, $charset = null, $exportOptions = null): SelectStatement{
		$this->setOutfilename($name);
		if($charset !== null){
			$this->setCharacterSet($charset);
		}
		if($exportOptions !== null){
			$this->setExportOptions($exportOptions);
		}
		return $this;
	}

	public function setWindowList($windowList){
		$f = __METHOD__;
		if($windowList == null){
			return $this->setArrayProperty("windowList", $windowList);
		}
		foreach($windowList as $window_name => $window){
			if(!is_string($window_name)){
				Debug::error("{$f} array keys must be window name strings");
			}elseif(!$window instanceof WindowSpecification){
				Debug::error("{$f} array values must be instances of WindowSpecification");
			}
		}
		return $this->setArrayProperty("windowList", $windowList);
	}

	public function hasWindowList():bool{
		return $this->hasArrayProperty("windowList");
	}

	public function getWindowList(){
		return $this->getProperty("windowList");
	}

	public function getWindowCount():int{
		return $this->getArrayPropertyCount("windowList");
	}

	public static function getStatementTypeString(): string{
		return "select";
	}

	public function select(...$select): SelectStatement{
		$this->setExpressions($select);
		return $this;
	}

	public function from(...$dbtable): SelectStatement{
		$f = __METHOD__;
		$count = count($dbtable);
		switch($count){
			case 1:
				if(!is_string($dbtable[0])){
					return $this->withJoinExpressions($dbtable[0]);
				}
				$this->setTableName($dbtable[0]);
				break;
			case 2:
				$this->setDatabaseName($dbtable[0]);
				$this->setTableName($dbtable[1]);
				break;
			default:
				Debug::error("{$f} temporarily disabled");
				return $this->withJoinExpressions(...$dbtable);
		}
		return $this;
	}

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
	public static function withRecursive($dbtable, $foreignKeyName, $parentKeyName = 'uniqueKey', $expression = null){
		$f = __METHOD__;
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
		if($expression === null){
			$expression = $where;
		}elseif($expression instanceof VariadicExpressionCommand){
			$expression->pushParameters($where);
		}else{
			Debug::error("{$f} neither of the above");
		}
		return QueryBuilder::select()->from($cteName)->with(WithClause::recursive($cteName, QueryBuilder::select()->from($dbtable)->where(new WhereCondition($foreignKeyName, OPERATOR_EQUALS))->unionAll(QueryBuilder::select("child.*")->from(JoinedTable::join(TableFactor::create()->withTableName($dbtable)->as("child"), TableFactor::create()->withTableName($cteName)->as("parent"), $expression)))));
	}

	public function setSubqueryFlag(bool $value = true):bool{
		return $this->setFlag("subquery", $value);
	}

	public function getSubqueryFlag():bool{
		return $this->getFlag("subquery");
	}

	public function setBigResultFlag(bool $value = true):bool{
		return $this->setFlag("bigResult", $value);
	}

	public function getBigResultFlag():bool{
		return $this->getFlag("bigResult");
	}

	public function bigResult(bool $value = true): SelectStatement{
		$this->setBigResultFlag($value);
		return $this;
	}

	public function setBufferResultFlag(bool $value = true):bool{
		$f = __METHOD__;
		if($value && $this->getSubqueryFlag()){
			Debug::error("{$f} buffer results flag cannot be set on subqueries");
		}
		return $this->setFlag("bufferResult", $value);
	}

	public function getBufferResultFlag():bool{
		return $this->getFlag("bufferResult");
	}

	public function bufferResult(bool $value = true): SelectStatement{
		$this->setBufferResultFlag($value);
		return $this;
	}

	public function setCalculateFoundRowsFlag(bool $value = true):bool{
		return $this->setFlag("calculateFoundRows", $value);
	}

	public function getCalculateFoundRowsFlag():bool{
		return $this->getFlag("calculateFoundRows");
	}

	public function calculateFoundRows(bool $value = true): SelectStatement{
		$this->setCalculateFoundRowsFlag($value);
		return $this;
	}

	public function setHighPriorityFlag(bool $value = true):bool{
		$f = __METHOD__;
		if($value && ($this->getSubqueryFlag() || $this->hasUnionClauses())){
			Debug::error("{$f} high priority cannot be used in selet statements that are part of unions");
		}
		return $this->setFlag(PRIORITY_HIGH, $value);
	}

	public function getHighPriorityFlag():bool{
		return $this->getFlag(PRIORITY_HIGH);
	}

	public function highPriority(bool $value = true): SelectStatement{
		$this->setHighPriorityFlag($value);
		return $this;
	}

	public function setNoCacheFlag($value = true):bool{
		return $this->setFlag("noCache", $value);
	}

	public function getNoCacheFlag():bool{
		return $this->getFlag("noCache");
	}

	public function noCache(bool $value = true): SelectStatement{
		$this->setNoCacheFlag($value);
		return $this;
	}

	public function setOrderWithRollupFlag(bool $value = true):bool{
		return $this->setFlag("orderWithRollup", $value);
	}

	public function getOrderWithRollupFlag():bool{
		return $this->getFlag("orderWithRollup");
	}

	public function setStraightJoinFlag(bool $value = true):bool{
		return $this->setFlag("straightJoin", $value);
	}

	public function getStraightJoinFlag():bool{
		return $this->getFlag("straightJoin");
	}

	public function setSmallResultFlag(bool $value = true):bool{
		return $this->setFlag("smallResult", $value);
	}

	public function getSmallResultFlag():bool{
		return $this->getFlag("smallResult");
	}

	public function smallResult(bool $value = true): SelectStatement{
		$this->setSmallResultFlag($value);
		return $this;
	}

	public function setLockMode(?string $mode):?string{
		$f = __METHOD__;
		if(!is_string($mode)){
			Debug::error("{$f} lock mode must be a string");
		}elseif($this->getSubqueryFlag()){
			Debug::error("{$f} locking tables in a subquery is unsupported");
		}
		$mode = strtolower($mode);
		switch($mode){
			case LOCK_IN_SHARE_MODE:
			case LOCK_FOR_SHARE:
			case LOCK_FOR_UPDATE:
				break;
			default:
				Debug::error("{$f} invalid lock mode \"{$mode}\"");
		}
		if($this->hasLockMode()){
			$this->release($this->lockMode);
		}
		if($this->hasLockOption()){
			$this->release($this->lockOption);
		}
		/*if($this->hasLockTableNames()){
			$this->ejectLockTableNames();
		}*/
		return $this->lockMode = $this->claim($mode);
	}

	public function hasLockMode():bool{
		return isset($this->lockMode);
	}

	public function getLockMode():string{
		$f = __METHOD__;
		if(!$this->hasLockMode()){
			Debug::error("{$f} lock mode is undefined");
		}
		return $this->lockMode;
	}

	public function setUnionClauses($unionClauses){
		return $this->setArrayProperty("unionClauses", $unionClauses);
	}

	public function hasUnionClauses():bool{
		return $this->hasArrayProperty("unionClauses");
	}

	public function pushUnionClause(...$unionClauses):int{
		return $this->pushArrayProperty("unionClauses", ...$unionClauses);
	}

	public function mergeUnionClauses($unionClauses){
		return $this->mergeArrayProperty("unionClauses", $unionClauses);
	}

	public function getUnionClauses(){
		return $this->getProperty("unionClauses");
	}

	public function union(...$selectStatements): SelectStatement{
		$f = __METHOD__;
		if(!isset($selectStatements)){
			Debug::error("{$f} select statements undefined");
		}
		foreach($selectStatements as $selectStatement){
			$union = new UnionClause($selectStatement);
			$this->pushUnionClause($union);
		}
		return $this;
	}

	public function unionAll(...$selectStatements): SelectStatement{
		$f = __METHOD__;
		if(!isset($selectStatements)){
			Debug::error("{$f} select statements undefined");
		}
		foreach($selectStatements as $selectStatement){
			$this->pushUnionClause(UnionClause::all($selectStatement));
		}
		return $this;
	}

	public function unionDistinct(...$selectStatements): SelectStatement{
		$f = __METHOD__;
		if(!isset($selectStatements)){
			Debug::error("{$f} select statements undefined");
		}
		foreach($selectStatements as $selectStatement){
			$this->pushUnionClause(UnionClause::distinct($selectStatement));
		}
		return $this;
	}

	public function pushWindows(...$values){
		return $this->pushArrayProperty("windowList", ...$values);
	}

	public function window($windows){
		$this->setWindowList($windows);
		return $this;
	}

	public function of(...$tableNames){
		$f = __METHOD__;
		if(!$this->hasLockMode()){
			Debug::error("{$f} don't call this function if lock mode is undefined");
		}
		$this->setTableNames($tableNames);
		return $this;
	}

	public function lock($mode){
		$this->setLockMode($mode);
		return $this;
	}

	public function getTypeSpecifiedFlag():bool{
		return $this->getFlag("typeSpecified");
	}
	
	public function setTypeSpecifiedFlag(bool $value=true):bool{
		return $this->setFlag("typeSpecified", $value);
	}
	
	public function getQueryStatementString(){
		$f = __METHOD__;
		try{
			$print = false;
			if($this->hasWithClause()){
				$with = $this->getWithClause();
				if($with instanceof SQLInterface){
					$with = $with->toSQL();
				}
				$string = "{$with} ";
			}
			$string = "";
			if($this->hasUnionClauses()){
				$string .= "(";
			}
			$string .= "select ";
			// [ALL | DISTINCT | DISTINCTROW ]
			if($this->hasDistinction()){
				$string .= $this->getDistinction() . " ";
			}
			// [HIGH_PRIORITY]
			if($this->getHighPriorityFlag()){
				$string .= PRIORITY_HIGH . " ";
			}
			// [STRAIGHT_JOIN]
			if($this->getStraightJoinFlag()){
				$string .= "straight_join ";
			}
			// [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT]
			if($this->getSmallResultFlag()){
				$string .= "sql_small_result ";
			}
			if($this->getBigResultFlag()){
				$string .= "sql_big_result ";
			}
			if($this->getBufferResultFlag()){
				$string .= "sql_buffer_result ";
			}
			// [SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS]
			if($this->getNoCacheFlag()){
				$string .= "sql_no_cache ";
			}
			if($this->getCalculateFoundRowsFlag()){
				$string .= "sql_calc_found_rows ";
			}
			// select_expr [, select_expr] ...
			if($this->hasExpressions()){
				$expressions = [];
				foreach($this->getExpressions() as $e){
					if($e instanceof SQLInterface){
						$e = $e->toSQL();
					}else{
						$e = back_quote($e);
					}
					array_push($expressions, $e);
				}
				$string .= implode(',', $expressions);
			}else{
				$string .= "*";
			}
			// [FROM table_references [PARTITION partition_list]]
			if($this->hasJoinExpressions() || $this->hasTableName()){
				$string .= " from ";
				if($this->hasJoinExpressions()){
					$je = [];
					foreach($this->getJoinExpressions() as $j){
						if($j instanceof SQLInterface){
							$j = $j->toSQL();
						}
						array_push($je, $j);
					}
					$string .= implode(' ', $je);
				}elseif($this->hasTableName()){
					if($this->hasDatabaseName()){
						$string .= back_quote($this->getDatabaseName()) . ".";
					}
					$string .= back_quote($this->getTableName());
				}
				if($this->hasPartitionNames()){
					$string .= " " . implode(',', $this->getPartitionNames());
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
			// [GROUP BY {col_name | expr | position}, ... [WITH ROLLUP]]
			if($this->hasGroupBy()){
				$string .= " group by ";
				$group_by = $this->getGroupBy();
				if(is_array($group_by)){
					$string .= implode(',', $group_by);
				}else{
					$string .= $group_by;
				}
				if($this->getGroupWithRollupFlag()){
					$string .= " with rollup";
				}
			}
			// [HAVING where_condition]
			if($this->hasHavingCondition()){
				$having = $this->getHavingCondition();
				if($having instanceof SQLInterface){
					$having = $having->toSQL();
				}
				$string .= " having {$having}";
			}
			// [WINDOW window_name AS (window_spec) [, window_name AS (window_spec)] ...]
			if($this->hasWindowList()){
				$string .= " window ";
				$i = 0;
				foreach($this->getWindowList() as $window_name => $window){
					if($i ++ > 0){
						$string .= ",";
					}
					if($window instanceof SQLInterface){
						$window = $window->toSQL();
					}
					$string .= "{$window_name} as ({$window})";
				}
			}
			// [ORDER BY {col_name | expr | position} [ASC | DESC], ... [WITH ROLLUP]]
			if($this->hasOrderBy()){
				$string .= " order by " . $this->getOrderByString();
				if($this->getOrderWithRollupFlag()){
					$string .= " with rollup";
				}
			}
			// [LIMIT {
			// [offset,] row_count | row_count OFFSET offset
			// }]
			if($this->hasLimit()){
				$string .= " limit " . $this->getLimit();
				if($this->hasOffset()){
					$string .= " offset " . $this->getOffset();
				}
			}
			// union clauses go here I guess?
			if($this->hasUnionClauses()){
				$union = [];
				foreach($this->getUnionClauses() as $u){
					if($u instanceof SQLInterface){
						$u = $u->toSQL();
					}
					array_push($union, $u);
				}
				$string .= ") " . implode(' ', $union);
			}
			// [FOR {UPDATE | SHARE} [OF tbl_name [, tbl_name] ...] [NOWAIT | SKIP LOCKED] | LOCK IN SHARE MODE]
			if($this->hasLockMode()){
				$lock = $this->getLockMode();
				if($lock === LOCK_IN_SHARE_MODE){
					$string .= " lock in share mode";
				}else{
					$string .= " for ";
					switch($lock){
						case LOCK_FOR_UPDATE:
							$string .= "update";
							break;
						case LOCK_FOR_SHARE:
							$string .= "share";
							break;
						default:
							Debug::error("{$f} invalid lock mode \"{$lock}\"");
					}
					if($this->hasTableNames()){
						$string .= " of " ;
						$count = 0;
						foreach($this->getTableNames() as $tn){
							if($count++ > 0){
								$string .= ",";
							}
							if($tn instanceof SQLInterface){
								$tn = $tn->toSQL();
							}elseif(is_string($tn)){
								//XXX TODO: if it has the format X.Y, split by . and backquote, unless Y is *. Need  regex for full table names.
							}
							$string .= $tn;
						}
					}
					if($this->hasLockOption()){
						$string .= " " . $this->getLockOption();
					}
				}
			}
			// [{
			// INTO OUTFILE 'file_name' [CHARACTER SET charset_name] export_options
			// | INTO DUMPFILE 'file_name'
			// | INTO var_name [, var_name] ...
			// }]
			if($this->hasOutfilename()){
				$string .= " into outfile " . single_quote($this->getOutfilename());
				if($this->hasCharacterSet()){
					$string .= " character set " . $this->getCharacterSet();
				}
				if($this->hasExportOptions()){
					$string .= " " . $this->getExportOptions(); // The syntax for the export_options part of the statement consists of the same FIELDS and LINES clauses that are used with the LOAD DATA statement
				}
			}elseif($this->hasDumpfilename()){
				$string .= " into dumpfile " . single_quote($this->getDumpfilename());
			}elseif($this->hasExportVariableNames()){
				$string .= " into " . implode(',', $this->getExportVariableNames());
			}
			if($print){
				Debug::print("{$f} returning \"{$string}\"");
			}
			return $string;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function prepareBindExecuteGetStatement(mysqli $mysqli, string $typedef, ...$params): ?mysqli_stmt{
		$f = __METHOD__;
		try{
			$print = false && $this->getDebugFlag();
			if(isset($params) && !empty($params)){
				$new_params = array(
					...$params
				);
			}else{
				$new_params = [];
			}
			$new_typedef = $typedef;
			if(!$this->getTypeSpecifiedFlag() && $this->hasExpressions()){
				$prefix = "";
				$temp = [];
				foreach($this->getExpressions() as $expr){
					if($expr instanceof ColumnAlias){
						$cn = $expr->getExpression();
						if($cn instanceof SelectStatement && $cn->hasTypeSpecifier()){
							$count = $cn->getParameterCount();
							if($print){
								Debug::print("{$f} prepending {$count} parameters from a subquery expression");
							}
							$prefix .= $cn->getTypeSpecifier();
							array_push($temp, ...$cn->getParameters());
						}elseif($print){
							Debug::print("{$f} column expression is not a select statement, or it does not have a typespecifier");
						}
					}elseif($print){
						Debug::print("{$f} column expressions is not an instanceof ColumnAlias");
					}
				}
				if($typedef !== ""){
					$new_typedef = "{$prefix}{$typedef}";
					$new_params = array_merge($temp, $params);
				}else{
					if($print){
						Debug::print("{$f} initial type definition string is empty");
					}
					$new_typedef = $prefix;
					$new_params = $temp;
				}
			}elseif($print){
				Debug::print("{$f} this statement does not have any expressions");
			}
			if($print){
				Debug::print("{$f} type definition string is \"{$new_typedef}\"; about to print parameters");
				Debug::printArray($new_params);
			}
			return parent::prepareBindExecuteGetStatement($mysqli, $new_typedef, ...$new_params);
		}catch(Exception $x){
			x($f, $x);
		}
	}
	
	/**
	 * generates a select statement useful for selecting keys from an intersection table
	 * @param string $hostClass : host class in the intersection table
	 * @param string $foreignClass
	 *        	: foreign class with which the host class has an intersection table
	 * @param string $foreignKeyName
	 *        	: name of the foreign key stored in that intersection table. Provide it if you're selecting the foreign data structure, leave it blank if you're selecting the current class.
	 * @param SelectStatement $subquery
	 *        	: optional subquery for specifying key selection criteria. Leave it blank and it will match the an alias for this class's identifying column
	 * @return SelectStatement
	 */
	// XXX TODO this is confusing because it does different things based off whether the second parameter is present; break it into 2 functions, one for selecting host keys and the other for foreign keys. Thse third parameter is also rather confusing
	public static function generateLazyAliasExpression($hostClass, $foreignClass, ?string $foreignKeyName = null, ?SelectStatement $subquery = null): SelectStatement{
		$f = __METHOD__;
		try{
			$print = false;
			if($print){
				Debug::print("{$f} entered");
			}
			if(is_object($hostClass)){
				$hostClass = get_class($hostClass);
			}
			if(is_object($foreignClass)){
				$foreignClass = get_class($foreignClass);
			}
			$idn = $foreignClass::getIdentifierNameStatic();
			if($foreignKeyName !== null){
				$fkn = $foreignKeyName;
			}else{
				$fkn = $idn;
			}
			$intersection = new IntersectionData($hostClass, $foreignClass, $fkn);
			$table = $intersection->getTableName();
			$alias = "{$table}_alias";
			if($foreignKeyName !== null){
				$subq_expr = new ColumnAlias(new ColumnAliasExpression($alias, "foreignKey"), $foreignKeyName);
				$get_key = new ColumnAliasExpression($alias, "hostKey");
			}else{
				$subq_expr = new ColumnAlias(new ColumnAliasExpression($alias, "hostKey"), $idn);
				$get_key = new ColumnAliasExpression($alias, "foreignKey");
			}
			$foreignKey = new ForeignKeyDatum($fkn);
			$foreignKey->setSubqueryTableAlias($alias);
			$foreignKey->setSubqueryDatabaseName($intersection->getDatabaseName());
			deallocate($intersection);
			$foreignKey->setSubqueryTableName($table);
			$foreignKey->setSubqueryExpression($subq_expr);
			if($subquery === null){
				$where1 = new BinaryExpressionCommand($get_key, OPERATOR_EQUALS, new GetDeclaredVariableCommand("t0." . back_quote($idn)));
			}else{
				$where1 = new WhereCondition($get_key, OPERATOR_EQUALS, null, $subquery);
			}
			$where2 = new WhereCondition((new GetDeclaredVariableCommand("{$alias}.relationship"))->withTypeSpecifier("s"), OPERATOR_EQUALS, 's');
			$and = new AndCommand($where1, $where2);
			if(!$and->hasParameters()){
				Debug::error("{$f} AND command lacks parameters immediately after instantiation");
			}elseif($print){
				$decl = $and->getDeclarationLine();
				$did = $and->getDebugId();
				Debug::print("{$f} and command was declared {$decl} with debug ID {$did}");
			}
			$foreignKey->setSubqueryWhereCondition($and);
			$select = $foreignKey->getAliasExpression()->escape(ESCAPE_TYPE_PARENTHESIS);
			deallocate($foreignKey);
			if($print){
				Debug::print("{$f} returning \"".$select->toSQL()."\"");
			}
			return $select;
		}catch(Exception $x){
			x($f, $x);
		}
	}
}
