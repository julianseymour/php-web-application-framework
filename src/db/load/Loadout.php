<?php
namespace JulianSeymour\PHPWebApplicationFramework\db\load;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\is_abstract;
use function JulianSeymour\PHPWebApplicationFramework\registry;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\KeyListDatum;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\event\AfterLoadEvent;
use JulianSeymour\PHPWebApplicationFramework\query\column\ColumnAlias;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatementInterface;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereConditionalStatement;
use JulianSeymour\PHPWebApplicationFramework\ui\RiggedLoadoutGenerator;
use Exception;
use mysqli;
use mysqli_result;

class Loadout extends Basic
{

	protected $treeSelectStatements;

	protected $userData;

	public function mapSelectStatement(string $phylum, string $class, SelectStatement $query): void{
		$f = __METHOD__;
		$print = false;
		if (! is_array($this->treeSelectStatements)) {
			$this->treeSelectStatements = [];
		}
		if (! array_key_exists($phylum, $this->treeSelectStatements)) {
			$this->treeSelectStatements[$phylum] = [
				$class => $query
			];
		} else {
			$this->treeSelectStatements[$phylum][$class] = $query;
		}
		if ($print) {
			Debug::print("{$f} mapped query \"{$query}\" to phylum \"{$phylum}\" of for class \"{$class}\"");
		}
	}

	public function mapSelectStatementIfUndefined(string $phylum, string $class, SelectStatement $query): void{
		$f = __METHOD__;
		$print = false;
		$did = $this->getDebugId();
		Debug::print("{$f} debug ID is {$did}");
		if (! is_array($this->treeSelectStatements)) {
			if($print){
				Debug::print("{$f} treeSelectStatements array is empty");
			}
			$this->treeSelectStatements = [];
		}
		if (! array_key_exists($phylum, $this->treeSelectStatements)) {
			if($print){
				Debug::print("{$f} phylum {$phylum} was not mapped to anything");
			}
			$this->treeSelectStatements[$phylum] = [
				$class => $query
			];
		} elseif(!array_key_exists($class, $this->treeSelectStatements[$phylum])){
			if($print){
				Debug::print("{$f} {$phylum}.{$class} was not mapped to a query");
			}
			$this->treeSelectStatements[$phylum][$class] = $query;
		}elseif($print){
			Debug::print("{$f} something was already mapped to {$phylum}.{$class}");
		}
	}
	
	public function hasTreeSelectStatements(){
		return is_array($this->treeSelectStatements) && ! empty($this->treeSelectStatements);
	}

	public function debugPrint(){
		Debug::printArray($this->treeSelectStatements);
	}

	public function getTreeSelectStatements(): array{
		$f = __METHOD__;
		if (! $this->hasTreeSelectStatements()) {
			Debug::error("{$f} treeSelectStatements are undefined");
		}
		return $this->treeSelectStatements;
	}

	public static function generate(?array $dependencies): Loadout{
		$f = __METHOD__;
		if (! is_array($dependencies) || empty($dependencies)) {
			Debug::error("{$f} don't call this without something dependencies");
		}
		$loadout = new Loadout();
		$loadout->addDependencies($dependencies);
		return $loadout;
	}

	public function addDependencies($dependencies){
		$f = __METHOD__;
		foreach (array_keys($dependencies) as $phylum) {
			if (! is_array($dependencies[$phylum])) {
				Debug::error("{$f} dependencies[{$phylum}] is not an array");
			}
			foreach (array_keys($dependencies[$phylum]) as $class) {
				$query = $dependencies[$phylum][$class];
				$this->mapSelectStatement($phylum, $class, $query);
			}
		}
		return $this;
	}
	
	public function addDependenciesIfUndefined($dependencies){
		$f = __METHOD__;
		foreach (array_keys($dependencies) as $phylum) {
			if (! is_array($dependencies[$phylum])) {
				Debug::error("{$f} dependencies[{$phylum}] is not an array");
			}
			foreach (array_keys($dependencies[$phylum]) as $class) {
				$query = $dependencies[$phylum][$class];
				$this->mapSelectStatementIfUndefined($phylum, $class, $query);
			}
		}
		return $this;
	}
	
	/**
	 * helper function for loadChildClass.
	 * An alternative to processChildQueryResults when the child keys are stored in an intersection table and loaded with a recursive common table expression.
	 *
	 * @param mysqli $mysqli
	 * @param DataStructure $ds
	 * @param string $class
	 * @param mysqli_result $result
	 * @param array $children
	 * @return array
	 */
	private static function loadRecursiveCTEFromIntersectionTable(mysqli $mysqli, DataStructure $ds, string $class, mysqli_result $result, array &$children){
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				Debug::print("{$f} query has a recursive CTE, and load entry point is the intersection table");
			}
			$idn = $class::getIdentifierNameStatic();
			$results = $result->fetch_all(MYSQLI_ASSOC);
			$child_keys = [];
			$typedef = "";
			// create children and set their identifiers and foreign keys
			foreach ($results as $r) {
				$child = new $class();
				$child->setIdentifierValue($r['hostKey']);
				$status = $child->processIntersectionTableQueryResultArray($r);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} processQueryResultArray returned error status \"{$err}\"");
					$ds->setObjectStatus($status);
					return [];
				} elseif ($child->hasColumn($idn) && $child->hasIdentifierValue()) {
					if ($child->isRegistrable()) {
						$child_key = $child->getIdentifierValue();
						if (registry()->hasObjectRegisteredToKey($child_key)) {
							if ($print) {
								Debug::print("{$f} use case already has an object mapped to key \"{$child_key}\"");
							}
							$child = registry()->getRegisteredObjectFromKey($child_key);
						} else {
							registry()->update($child_key, $child);
						}
					} elseif ($print) {
						Debug::print("{$f} child is not registrable");
					}
					$children[$child->getIdentifierValue()] = $child;
					if ($child->isUninitialized()) {
						$typedef .= "s";
						array_push($child_keys, $child->getIdentifierValue());
					} elseif ($print) {
						Debug::print("{$f} child is not uninitialized");
					}
				} else {
					Debug::error("{$f} neither of the above");
				}
			}
			$result->free_result();
			// load all descendants properly
			$result = $class::selectStatic()->where(WhereCondition::in($idn, count($child_keys)))->prepareBindExecuteGetResult($mysqli, $typedef, ...$child_keys);
			$results = $result->fetch_all(MYSQLI_ASSOC);
			foreach ($results as $r) {
				$child = $children[$r[$child->getIdentifierName()]];
				$status = $child->processQueryResultArray($mysqli, $r);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} processQueryResultArray on child initially loaded from intersection table returned error status \"{$err}\"");
					$ds->setObjectStatus($status);
					return [];
				}
				if ($child->isRegistrable()) {
					registry()->update($child->getIdentifierValue(), $child);
				} elseif ($print) {
					Debug::print("{$f} child is not registrable");
				}
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * helper function for loadChildClass.
	 * Loads all intersection table keys and foreign data structures then calls expandTree on each child
	 *
	 * @param mysqli $mysqli
	 * @param DataStructure $ds
	 * @param array $children
	 * @param bool $recursive
	 * @return int
	 */
	private static function expandChildren(mysqli $mysqli, DataStructure $ds, array &$children, bool $recursive): int{
		$f = __METHOD__;
		try {
			$print = false;
			$i = 0;
			foreach ($children as $child) {
				$child->setIterator($i ++);
				if ($child->getObjectStatus() === STATUS_PRELAZYLOAD) {
					if ($print) {
						Debug::print("{$f} lazy load in progress");
					}
					continue;
				} elseif ($print) {
					$cc = $child->getClass();
					Debug::print("{$f} about to call loadForeignDataStructures on child of class \"{$cc}\"");
				}
				$status = $child->loadForeignDataStructures($mysqli, true);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} loadForeignDataStructures returned error status \"{$err}\"");
					return $ds->setObjectStatus($status);
				} elseif (! $recursive && ! $child->getExpandedFlag()) {
					if ($print) {
						$class = $child->getClass();
						Debug::print("{$f} this is not a recursive CTE, and the child of class {$class} has not been expanded");
					}
					$use_case = app()->getUseCase();
					$did = $use_case->getDebugId();
					if($print){
						$ucc = get_short_class($use_case);
						Debug::print("{$f} about to call getLoadoutGenerator on {$ucc} with debug ID {$did}");
					}
					$generator = $use_case->getLoadoutGenerator(user());
					$has = false;
					if($generator instanceof LoadoutGenerator){
						if($print){
							Debug::print("{$f} about to generate a loadout with generator class ".get_short_class($generator));
						}
						$loadout = $generator->generateNonRootLoadout($child, $use_case);
						if($print){
							$loadout->debugPrint();
						}
						$has = $loadout instanceof Loadout && $loadout->hasTreeSelectStatements();
					}elseif(!Request::isAjaxRequest()){
						if($print){
							Debug::print("{$f} this is not an ajax request, instantiating a new loadout");
						}
						$loadout = new Loadout();
					}else{
						$loadout = null;
					}
					if($loadout === null && !Request::isAjaxRequest()){
						$loadout = new Loadout();
					}
					if ($loadout instanceof Loadout) {
						if(!Request::isAjaxRequest()){
							if($print){
								Debug::print("{$f} this is not an AJAX request, adding new dependencies from RiggedLoadoutGenerator");
							}
							$rigged = new RiggedLoadoutGenerator();
							$loadout->addDependencies(
								$rigged->getNonRootNodeTreeSelectStatements($child, $use_case)
							);
						}elseif($print){
							Debug::print("{$f} this is NOT an ajax request");
						}
						if($loadout->hasTreeSelectStatements()){
							if ($print) {
								Debug::print("{$f} use case \"{$ucc}\" generated the following loadout:");
								$loadout->debugPrint();
							}
							$status = $loadout->expandTree($mysqli, $child);
							if ($status !== SUCCESS) {
								$err = ErrorMessage::getResultMessage($status);
								Debug::error("{$f} expandTree returned error status \"{$err}\"");
								return $ds->setObjectStatus($status);
							}elseif($print){
								Debug::print("{$f} tree expansion successful");
							}
						}else{
							if($has){
								Debug::error("{$f} RiggedLoadoutGenerator destroyed tree select statements");
							}
							if($print){
								Debug::print("{$f} loadout lacks tree select statements");
							}
						}
					} elseif ($print) {
						Debug::print("{$f} generated a null loadout for this child, continuing");
					}
				} elseif ($print) {
					Debug::print("{$f} object has a recursive common table expression, or child is non-hierarchical, or child has already been expanded");
				}
				//$child->setObjectStatus(SUCCESS);
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * Helper function for loadChildClass.
	 * Assigns type definition strings to query $select
	 *
	 * @param DataStructure $ds
	 * @param string $phylum
	 * @param string $class
	 * @param WhereConditionalStatement $select
	 */
	public static function assignTypeSpecifier(string $class, WhereConditionalStatement $select): ?string{
		$f = __METHOD__;
		try {
			$print = false;
			$dummy = new $class();
			$typedef = "";
			$conditions = [];
			if ($select->getFlag("unassigned")) {
				if ($print) {
					Debug::print("{$f} select statement has column alias expression(s) with parameters");
				}
				$unassigned = [];
				foreach ($select->getExpressions() as $expr){
					if(!$expr instanceof ColumnAlias){
						continue;
					}elseif(!$expr->hasParameters()){
						if ($print) {
							Debug::print("{$f} ColumnAlias \"{$expr}\" does not have parameters");
						}
						continue;
					}
					array_push($conditions, $expr);
					$eps = $expr->getParameters();
					if($print){
						if($print){
							Debug::print("{$f} pushing the following parameters extracted from the column alias:");
							Debug::printArray($eps);
						}
					}
					array_push($unassigned, ...$eps);
				}
				if($print){
					Debug::print("{$f} unshifting the following parameters:");
					Debug::printArray($unassigned);
				}
				$select->unshiftParameters(...$unassigned);
				$params = $select->getParameters();
				if(is_array($params) && is_array($params[0])){
					Debug::error("{$f} nested parameter array");
				}
			} elseif ($print) {
				Debug::print("{$f} this select statement does not have any column alias expressions with parameters");
			}
			$flat = $select->getSuperflatWhereConditionArray();
			if (is_string($flat)) {
				Debug::error("{$f} getSuperflatWhereConditionArray returned string \"{$conditions}\"");
			} elseif (! empty($flat)) {
				array_push($conditions, ...$flat);
			} elseif (empty($conditions)) {
				Debug::error("{$f} no conditions for select statement \"{$select}\"");
			} elseif ($print) {
				$condition_count = count($conditions);
				Debug::print("{$f} {$condition_count} conditions for child class \"{$class}\"");
			}
			foreach ($conditions as $i => $condition) {
				if (! is_int($i) && ! is_string($i)) {
					$gottype = gettype($i);
					Debug::error("{$f} array offset has type \"{$gottype}\"");
				}
				if (! $condition instanceof ColumnAlias) {
					if ($condition instanceof SelectStatementInterface && $condition->hasSelectStatement()) {
						Debug::error("{$f} where condition array was not completely flattened");
					} elseif ($condition->hasUnbindableOperator()) {
						if ($print) {
							Debug::print("{$f} no arguments; this is only allowable for IS and IS NOT operators");
						}
						continue;
					}
				} elseif ($print) {
					Debug::print("{$f} one of the conditions is actually a column alias");
				}
				$column_name = $condition->getColumnName();
				if ($condition instanceof ColumnAlias) {
					if ($print) {
						Debug::print("{$f} condition is actually a ColumnAlias");
					}
					$spec = $condition->getTypeSpecifier();
					$typedef .= $spec;
				} else {
					if ($dummy->hasColumn($column_name)) {
						$spec = $dummy->getColumn($column_name)->getTypeSpecifier();
					} else { // condition is accomplice to a Lazy/WhereCondition
						if ($print) {
							Debug::print("{$f} condition \"{$condition}\" for select statement \"{$select}\" is not a column alias, and there is no such column \"{$column_name}\"");
						}
						$spec = $condition->getTypeSpecifier(); //
					}
					$inferred = $condition->inferParameterCount();
					if ($print) {
						Debug::print("{$f} type specifier for column \"{$column_name}\" is \"{$spec}\" with inferred parameter count {$inferred}");
					}
					$typedef .= str_pad("", $inferred, $spec);
					if ($print) {
						Debug::print("{$f} appended type specifier \"{$spec}\" for condition \"{$condition}\"");
					}
				}
			}
			if ($print) {
				Debug::print("{$f} final type specifier is \"{$typedef}\", with the following parameters:");
				Debug::printArray($select->getParameters());
			}
			unset($dummy);
			if (! empty($typedef)) {
				$length = strlen($typedef);
				$count = $select->getParameterCount();
				if ($length !== $count) {
					if ($select->hasWhereCondition()) {
						$where = $select->getWhereCondition();
						if ($where instanceof WhereCondition && $where->hasSelectStatement()) {
							$where->setParameterCount($count);
						}
					} elseif ($print) {
						Debug::print("{$f} select statement lacks a parameter count");
					}
					$qstring = $select->toSQL();
					$decl = $select->getDeclarationLine();
					Debug::warning("{$f} type specifier \"{$typedef}\" length {$length} does not match parameter count {$count} in query statement \"{$qstring}\" for child class \"{$class}\"; query statement was constructed {$decl}. Parameters are as follows:");
					Debug::printArray($select->getParameters());
					Debug::printStackTrace();
				}
				$select->setTypeSpecifier($typedef);
				$select->setFlag("typeSpecified", true);
				return $typedef;
			} elseif ($print) {
				Debug::print("{$f} final type specifier is null or empty string");
			}
			return null;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * Helper function for loadChildClass. Assigns parameters and type definition strings to query $select
	 * @param DataStructure $ds
	 * @param string $phylum
	 * @param string $class
	 * @param WhereConditionalStatement $select
	 */
	/*private static function assignQueryParameters(
		DataStructure $ds,
		string $phylum,
		string $class,
		WhereConditionalStatement $select
	){
			$f = __METHOD__; //Loadout::class."(".static::class.")->()";
			try{
				$print = false;
				if($print){
					$dsc = $ds->getClass();
					Debug::print("{$f} entered for data structure of class \"{$dsc}\" phylum \"{$phylum}\", class \"{$class}\"");
				}
				$dummy = new $class();
				$typedef = "";
				$conditions = [];
				$params = [];
				if($select->getFlag("unassigned")){
					if($print){
						Debug::print("{$f} select statement has column alias expression(s) with parameters");
					}
					foreach($select->getExpressions() as $expr){
						if(!$expr instanceof ColumnAlias){
							continue;
						}elseif(!$expr->hasParameters()){
							if($print){
								Debug::print("{$f} ColumnAlias \"{$expr}\" does not have parameters");
							}
							continue;
						}
						array_push($conditions, $expr);
						$eps = $expr->getParameters();
						if($print){
							Debug::print("{$f} pushing the following parameters extracted from the column alias:");
							Debug::printArray($eps);
						}
						array_push($params, ...$eps);
					}
				}elseif($print){
					Debug::print("{$f} this select statement does not have any column alias expressions with parameters");
				}
				$flat = $select->getSuperflatWhereConditionArray();
				if(is_string($flat)){
					Debug::error("{$f} getSuperflatWhereConditionArray returned string \"{$conditions}\"");
				}elseif(!empty($flat)){
					array_push($conditions, ...$flat);
				}elseif(empty($conditions)){
					Debug::error("{$f} no conditions for select statement \"{$select}\"");
				}elseif($print){
					$condition_count = count($conditions);
					Debug::print("{$f} {$condition_count} conditions for child class \"{$class}\"");
				}
				$use_case = app()->getUseCase();
				if($print){
					$ucc = $use_case->getClass();
					Debug::print("{$f} about to call {$ucc}->getChildSelectionParameters() for child class \"{$class}\"");
				}
				$csp = $use_case->getChildSelectionParameters($ds, $phylum, $class);
				if(!empty($csp)){
					if($print){
						Debug::print("{$f} pushing the following use case-provided child selection parameters:");
						Debug::printArray($csp);
					}
					array_push($params, ...$csp);
				}elseif($print){
					Debug::print("{$f} the use case does not provide any child selection parameters");
				}
				foreach($conditions as $i => $condition){
					if(!is_int($i) && !is_string($i)){
						$gottype = gettype($i);
						Debug::error("{$f} array offset has type \"{$gottype}\"");
					}
					if(!$condition instanceof ColumnAlias){
						if(
							$condition instanceof SelectStatementInterface
							&& $condition->hasSelectStatement()
							){ //instanceof Lazy/WhereCondition){
								Debug::error("{$f} where condition array was not completely flattened");
						}elseif($condition->hasUnbindableOperator()){
							if($print){
								Debug::print("{$f} no arguments; this is only allowable for IS and IS NOT operators");
							}
							continue;
						}
					}else{
						if($print){
							Debug::print("{$f} one of the conditions is actually a column alias");
						}
					}
					$column_name = $condition->getColumnName();
					if($condition instanceof ColumnAlias){
						//$alias_count += $condition->inferParameterCount();
						if($print){
							Debug::print("{$f} condition is actually a ColumnAlias");
						}
						$spec = $condition->getTypeSpecifier();
						$typedef .= $spec;
					}else{
						if($dummy->hasColumn($column_name)){
							$spec = $dummy->getColumn($column_name)->getTypeSpecifier();
						}else{//condition is accomplice to a Lazy/WhereCondition
							if($print){
								Debug::print("{$f} condition \"{$condition}\" for select statement \"{$select}\" is not a column alias, and there is no such column \"{$column_name}\"");
							}
							$spec = $condition->getTypeSpecifier(); //
						}
						$inferred = $condition->inferParameterCount();
						if($print){
							Debug::print("{$f} type specifier for column \"{$column_name}\" is \"{$spec}\" with inferred parameter count {$inferred}");
						}
						$typedef .= str_pad("", $inferred, $spec);
						if($print){
							Debug::print("{$f} appended type specifier \"{$spec}\" for condition \"{$condition}\"");
						}
					}
				}
				if($print){
					Debug::print("{$f} final type specifier is \"{$typedef}\"");
				}
				$dummy = null;
				if(!empty($params) || !empty($typedef)){
					$length = strlen($typedef);
					$count = isset($params) ? count($params) : 0;
					if($length !== $count){
						if($select->hasWhereCondition()){
							$where = $select->getWhereCondition();
							if($where instanceof WhereCondition && $where->hasSelectStatement()){
								$where->setParameterCount($count);
							}
						}elseif($print){
							Debug::print("{$f} select statement lacks a parameter count");
						}
						$qstring = $select->toSQL();
						$decl = $select->getDeclarationLine();
						Debug::warning("{$f} type specifier \"{$typedef}\" length {$length} does not match parameter count {$count} in query statement \"{$qstring}\" for child class \"{$class}\"; query statement was constructed {$decl}. Parameters are as follows:");
						Debug::printArray($params);
						Debug::printStackTrace();
					}
					$select->setParameters($params);
					$select->setTypeSpecifier($typedef);
					$select->setFlag("typeSpecified", true);
				}
			}catch(Exception $x){
				x($f, $x);
			}
	}*/
	
	/**
	 * helper function for loadChildClass.
	 * Processes results of non-recursive queries $results for child nodes of class $class for tree $phylum
	 *
	 * @param mysqli $mysqli
	 * @param DataStructure $ds
	 * @param string $phylum
	 * @param string $class
	 * @param array $results
	 * @param array $children
	 * @param bool $recursive
	 * @return array
	 */
	private static function processChildQueryResults(mysqli $mysqli, DataStructure $ds, string $phylum, string $class, array $results, array &$children, bool $recursive){
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				Debug::print("{$f} query does not have a recursive CTE, or load entry point is something besides the intersection table");
			}
			$idn = $class::getIdentifierNameStatic();
			// $results = is_array($result) ? $result : $result->fetch_all(MYSQLI_ASSOC);
			$count = 0;
			foreach ($results as $r) {
				$child = new $class();
				$status = $child->processQueryResultArray($mysqli, $r);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} processQueryResultArray returned error status \"{$err}\"");
					$ds->setObjectStatus($status);
					return [];
				} elseif ($child->hasColumn($idn) && $child->hasIdentifierValue()) {
					if ($print) {
						$id = $child->getIdentifierValue();
						$count ++;
						Debug::print("{$f} child {$class} #{$count} has ID \"{$id}\"");
					}
					if ($child->isRegistrable()) {
						$child_key = $child->getIdentifierValue();
						if (registry()->hasObjectRegisteredToKey($child_key)) {
							if ($print) {
								$other = registry()->get($child_key);
								$did = $other->getDebugId();
								$decl = $other->getDeclarationLine();
								Debug::error("{$f} registry already has an object mapped to key \"{$child_key}\". It has debug ID {$did} and was instantiated {$decl}");
							}
							$child = registry()->getRegisteredObjectFromKey($child_key);
						} else {
							if ($print) {
								Debug::print("{$f} registry does not already have an object mapped to key \"{$child_key}\"");
							}
							registry()->registerObjectToKey($child_key, $child);
						}
					} elseif ($print) {
						Debug::print("{$f} child is not registrable");
					}
					
				} elseif ($print) {
					Debug::print("{$f} child does not have its identifier");
				}
				if (! $recursive) {
					if ($print) {
						if ($child->getObjectStatus() === STATUS_PRELAZYLOAD) {
							Debug::print("{$f} lazy load of {$class} has not completed");
						} elseif ($print) {
							Debug::print("{$f} query does not have a recursive common table expression -- setting child as foreign data structure list member");
						}
					}
					if($child->hasIdentifierValue()){
						$children[$child->getIdentifierValue()] = $child;
					}else{
						array_push($children, $child);
					}
					$ds->setForeignDataStructureListMember($phylum, $child);
				} elseif ($print) {
					Debug::print("{$f} query has a a recursive common table expression");
				}
			}
			if ($print) {
				Debug::printStackTraceNoExit("{$f} returning ".count($children)." children");
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * load foreign data structures in tree $phylum of class $class with SelectStatement $select
	 *
	 * @param mysqli $mysqli
	 * @param DataStructure $ds
	 * @param string $phylum
	 * @param string $class
	 * @param SelectStatement $select
	 * @return array|NULL
	 */
	public static function loadChildClass(mysqli $mysqli, DataStructure $ds, string $phylum, string $class, SelectStatement $select, bool $expand_children=true): ?array{
		$f = __METHOD__;
		try {
			$print = false;
			if (! class_exists($class)) {
				Debug::error("{$f} class \"{$class}\" does not exist");
			} elseif (is_abstract($class)) {
				Debug::error("{$f} class \"{$class}\" is abstract");
			}
			if (is_string($select)) {
				Debug::error("{$f} query \"{$select}\" is a string and not a QueryStatement");
			}
			$children = [];
			// prepare and execute query
			if ($select->hasParameters() || $select->getFlag("unassigned")) {
				if (! $select->hasTypeSpecifier()) {
					if ($print) {
						Debug::print("{$f} query has parameters but no type specifier; assigning it now");
					}
					static::assignTypeSpecifier($class, $select);
				} elseif ($print) {
					$count = $select->getParameterCount();
					Debug::print("{$f} query has {$count} parameters and an assigned type specifier");
				}
			} elseif ($print) {
				Debug::print("{$f} query has no parameters");
			}
			if ($print) {
				Debug::print("{$f} about to load child class \"{$class}\"");
				if ($select->getFlag("unassigned")) {
					Debug::print("{$f} {$class} select statement was flagged as having parameterized column alias expressions");
				}
				if ($select->hasTypeSpecifier()) {
					$typedef = $select->getTypeSpecifier();
					Debug::print("{$f} select statement \"{$select}\" has type specifier \"{$typedef}\" and the following parameters:");
					Debug::printArray($select->getParameters());
				}
			}
			if ($select->hasTypeSpecifier() || $select->getFlag("unassigned")) {
				$typedef = $select->hasTypeSpecifier() ? $select->getTypeSpecifier() : "";
				$params = $select->hasParameters() ? $select->getParameters() : [];
				if(is_array($params) && is_array($params[0])){
					Debug::error("{$f} nested parameter array");
				}
				if($print){
					Debug::print("{$f} about to call prepareBindExecuteGetResult with type specifier \"{$typedef}\" and the following parameters:");
					Debug::printArray($params);
				}
				$result = $select->prepareBindExecuteGetResult($mysqli, $typedef, ...$params);
			} else {
				$result = $select->executeGetResult($mysqli);
			}
			// fetch results
			if ($result === null) {
				$count = 0;
			} else {
				$count = is_array($result) ? count($result) : $result->num_rows;
			}
			if ($count === 0) {
				if ($print) {
					$key = $ds->hasIdentifierValue() ? $ds->getIdentifierValue() : "[unidentifiable]";
					Debug::print("{$f} no children in class \"{$class}\" for object iwth key \"{$key}\"");
				}
				return [];
			}
			// process results
			$recursive = $select->hasRecursiveCommonTableExpression($mysqli);
			if ($recursive && $select->getLoadEntryPoint() === LOAD_ENTRY_POINT_INTERSECTION) {
				if ($print) {
					Debug::print("{$f} loading recursive common table expression from intersection table");
				}
				static::loadRecursiveCTEFromintersectionTable($mysqli, $ds, $class, $result, $children);
			} else {
				$results = is_array($result) ? $result : $result->fetch_all(MYSQLI_ASSOC);
				if ($print) {
					Debug::print("{$f} about to process results of query \"{$select}\"");
				}
				static::processChildQueryResults($mysqli, $ds, $phylum, $class, $results, $children, $recursive);
				if ($print) {
					Debug::print("{$f} returned from processChildQueryResults");
				}
			}
			if ($recursive) {
				if ($print) {
					Debug::print("{$f} query says it has a recursive common table expression");
				}
				$cte = $select->getWithClause()->getCommonTableExpression(0);
				$foreignKeyName = $cte->getWhereCondition()->getColumnName();
				foreach ($children as /*$child_key =>*/ $child) {
					$foreignKey = $child->getColumnValue($foreignKeyName);
					if (registry()->hasObjectRegisteredToKey($foreignKey)) {
						$parent = registry()->getRegisteredObjectFromKey($foreignKey);
						$parent->setForeignDataStructureListMember($phylum, $child);
					}
				}
				$direct_children = [];
				foreach ($children as /*$child_key =>*/ $child) {
					if ($child->getColumnValue($foreignKeyName) === $ds->getIdentifierValue()) {
						$direct_children[$child->getIdentifierValue()] = $child;
					}
				}
				$children = $direct_children;
			}//
			if ($print) {
				$count = count($children);
				$key = $ds->hasIdentifierValue() ? $ds->getIdentifierValue() : "[unidentifiable]";
				$did = $ds->getDebugId();
				$decl = $ds->getDeclarationLine();
				Debug::print("{$f} successfully loaded {$count} {$class} children in phylum \"{$phylum}\"; identifier is {$key}; debug id is {$did}; created {$decl}");
				$keys = [];
				foreach($children as $child){
					if($child->hasIdentifierValue()){
						$key = $child->getIdentifierValue();
						if(array_key_exists($key, $keys)){
							Debug::error("{$f} loading duplicate key \"{$key}\"");
						}
						$keys[$key] = $child;
					}
				}
				Debug::print("{$f} no duplicate children");
			}
			// load keys stored in intersection tables and foreign data structures
			$status = LazyLoadHelper::loadIntersectionTableKeys($mysqli, $children);
			if ($status !== SUCCESS) {
				$err = ErrorMessage::getResultMessage($status);
				Debug::warning("{$f} LazyLoadHelper::loadIntersectionTableKeys returned error status \"{$err}\"");
			}elseif($print){
				Debug::print("{$f} successfully loaded intersection table keys");
			}
			
			if ($expand_children && ! empty($children)) {
				if($print){
					Debug::print("{$f} about to call expandChildren");
				}
				$status = static::expandChildren($mysqli, $ds, $children, $recursive);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} expandChildren returned error status \"{$err}\"");
					$ds->setObjectStatus($status);
					return [];
				}
			} elseif ($print) {
				Debug::print("{$f} no children to expand");
			}
			
			return $children;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * '
	 * load all trees for DataStructure $ds using this Loadout's $treeSelectStatements,
	 * and assign them as foreign data structure list members
	 *
	 * @param mysqli $mysqli
	 * @param DataStructure $ds
	 * @return int
	 */
	public function expandTree(mysqli $mysqli, DataStructure $ds): int{
		$f = __METHOD__;
		try {
			if ($mysqli->connect_errno) {
				Debug::error("{$f} Failed to connect to MySQL: ({$mysqli->connect_errno}) {$mysqli->connect_error}");
			} elseif (! $mysqli->ping()) {
				Debug::error("{$f} mysqli connection failed ping test: \"" . $mysqli->error . "\"");
			}
			$print = false;
			if ($ds->getExpandedFlag()) {
				$ucc = app()->getUseCase()->getClass();
				$did = $ds->getDebugId();
				Debug::error("{$f} already expanded; my debug ID is \"{$did}\"; use case is {$ucc}");
				return SUCCESS;
			} elseif ($print) {
				Debug::print("{$f} entered");
			}
			$ds->beforeExpandHook($mysqli);
			$ds->setExpandedFlag(true);
			if (! $ds->getFlag("expandForeign")) {
				$status = $ds->loadForeignDataStructures($mysqli, true, 3);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::error("{$f} loadForeignDataStructures returned error status \"{$err}\"");
					return $ds->setObjectStatus($status);
				} elseif ($print) {
					Debug::print("{$f} about to call expandForeignDataStructures");
				}
				$status = static::expandForeignDataStructures($ds, $mysqli);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} expandForeignDataStructures returned error status \"{$err}\"");
					return $ds->setObjectStatus($status);
				}
			} elseif ($print) {
				Debug::print("{$f} already expanded foreign data structures");
			}
			$queries = $this->getTreeSelectStatements();
			if (! empty($queries)) {
				if ($print) {
					$count = count($queries);
					Debug::print("{$f} {$count} different phylums");
				}
				foreach ($queries as $phylum => $classes) {
					if ($print) {
						Debug::print("{$f} phylum \"{$phylum}\"");
					}
					foreach ($classes as $class => $select) {
						if (! is_string($class)) {
							$gottype = is_object($class) ? $class->getClass() : gettype($class);
							Debug::error("{$f} invalid class name type \"{$gottype}\"");
						} elseif (! class_exists($class)) {
							Debug::error("{$f} class \"{$class}\" does not exist");
						} elseif (is_abstract($class)) {
							Debug::error("{$f} class \"{$class}\" is abstract");
						} elseif (! $select instanceof SelectStatement) {
							if ($select == null) {
								Debug::error("{$f} query for class \"{$class}\" is null");
							} elseif (is_array($select)) {
								Debug::error("{$f} query for class \"{$class}\" is an array");
							} elseif (! is_object($select)) {
								Debug::error("{$f} query for class \"{$class}\" is \"{$select}\", not even an object");
							}
							$qc = $select->getClass();
							Debug::error("{$f} query for class \"{$class}\" is a {$qc}, not an instanceof SelectStatement");
						} elseif ($print) {
							Debug::print("{$f} query for class \"{$class}\" is \"{$select}\". About to call loadChildClass");
						}
						static::loadChildClass($mysqli, $ds, $phylum, $class, $select);
					}
				}
			} elseif ($print) {
				Debug::print("{$f} queries array is empty");
			}

			$ds->afterExpandHook($mysqli);

			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/**
	 * call expandTree on all foreign data structures
	 *
	 * @param DataStructure $ds
	 * @param mysqli $mysqli
	 * @param bool $lazy
	 * @param int $recursion_depth
	 * @return string
	 */
	public static function expandForeignDataStructures(DataStructure $ds, ?mysqli $mysqli = null, bool $lazy = false, int $recursion_depth = 0)
	{
		$f = __METHOD__;
		try {
			$print = false;
			if ($print) {
				Debug::print("{$f} entered");
			}
			if ($ds->getFlag("expandForeign")) {
				Debug::error("{$f} this object has already called expandForeignDataStructures");
			}
			$ds->setFlag("expandForeign", true);
			// closure for potentially lazy expanding foreign data structures. If a database link was provided, the closure will be called in this function; otherwise it will be added as an event listener for after the object is loaded
			$closure = function (AfterLoadEvent $event, DataStructure $target) use ($lazy, $recursion_depth, $f, $print) {
				$tc = $target->getClass();
				if ($print) {
					Debug::print("{$f} entered");
				}
				$mysqli = db()->getConnection(PublicReadCredentials::class);
				// remove event, if applicable
				if ($event->hasListenerId() && $target->hasEventListener(EVENT_AFTER_LOAD, $event->getListenerId())) {
					$target->removeEventListener($event);
					if ($print) {
						Debug::print("{$f} removed event listener");
					}
				} elseif ($print) {
					Debug::print("{$f} there is no event listener to remove");
				}
				// recursively load foreign data structures
				if ($recursion_depth > 0 && ! $target->getFlag("expandForeign")) {
					if ($print) {
						Debug::print("{$f} {$tc} has not expanded foreign data structures");
					}
					$status = $target->loadForeignDataStructures($mysqli, $lazy, $recursion_depth - 1);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::error("{$f} loadForeignDataStructures returned error status \"{$err}\"");
						return; // $ds->setObjectStatus($status);
					}
				} elseif ($print) {
					Debug::print("{$f} recursion depth is 0, and/or the target has already expanded its foreign data structures");
				}
				// check whether to prematurely return before expandTree is called. This has to happen after loadForeignDataStructures because some use cases might rely on one of them to generate a loadout
				if (get_class($target)::equals($target, user())) {
					// this is the data structure for the currently authenticated user
					if ($print) {
						Debug::print("{$f} object is the current user data, will not expand in this squalid hellhole of a function");
					}
					$break = true;
				} else {
					$break = $lazy;
				}
				if(app()->hasUseCase()){
					$use_case = app()->getUseCase();
					if ($print) {
						$ucc = $use_case->getClass();
						Debug::print("{$f} target {$tc} does not have a loadout -- generating one now from use case of class \"{$ucc}\"");
					}
					$generator = $use_case->getLoadoutGenerator(user());
					if ($generator instanceof LoadoutGenerator) {
						$loadout = $generator->generateNonRootLoadout($target, $use_case);
						if ($loadout instanceof Loadout) {
							if ($print) {
								Debug::print("{$f} got a loadout");
							}
						} else {
							if ($print) {
								$gottype = is_object($loadout) ? $loadout->getClass() : gettype($loadout);
								Debug::print("{$f} {$tc}'s loadout is \"{$gottype}\"");
							}
							$break = true;
						}
					} else {
						if ($print) {
							Debug::print("{$f} generator class is not defined");
						}
						$break = true;
					}
				}elseif($print){
					Debug::warning("{$f} application runtime doesn't know about a use case. This should almost never happen");
				}
				// recursively expand foreign data structures
				if ($recursion_depth > 0 && ! $target->getFlag("expandForeign")) {
					$status = Loadout::expandForeignDataStructures($target, $mysqli, $lazy, $recursion_depth - 1);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} expandForeignDataStructures returned error status \"{$err}\"");
						return; // $ds->setObjectStatus($status);
					}
				} elseif ($print) {
					Debug::print("{$f} {$tc} has already expanded foreign data structures");
				}
				// expandTree unless if was deemed appropriate to break eariler
				if (! $break && ! $target->getExpandedFlag()) {
					$status = $loadout->expandTree($mysqli, $target);
					if ($status !== SUCCESS) {
						$err = ErrorMessage::getResultMessage($status);
						Debug::warning("{$f} calling expandTree on foreign data structure returned error status \"{$err}\"");
						$target->setObjectStatus($status);
					} elseif ($print) {
						Debug::print("{$f} successfully expanded foreign data structure");
					}
				} elseif ($print) {
					Debug::print("{$f} forgoing expansion at this time");
				}
			};
			// iterate through autoloaded columns
			foreach ($ds->getFilteredColumns(COLUMN_FILTER_AUTOLOAD) as $column_name => $column) {
				if ($print) {
					Debug::print("{$f} column name \"{$column_name}\"");
				}
				if ($column instanceof ForeignKeyDatum && $ds->hasForeignDataStructure($column_name)) {
					if ($print) {
						Debug::print("{$f} about to call expandTree on a foreign data structure at index \"{$column_name}\"");
					}
					$fds = $ds->getForeignDataStructure($column_name);
					if (! $fds instanceof DataStructure) {
						if ($print) {
							Debug::print("{$f} foreign data structure with column name \"{$column_name}\" is non-hierarchical");
						}
						continue;
					} elseif ($fds->getExpandedFlag()) {
						if ($print) {
							Debug::print("{$f} foreign data structure at column \"{$column_name}\" has already been expanded");
						}
						continue;
					}
					$event = new AfterLoadEvent();
					if ($fds->getLoadedFlag() && $mysqli instanceof mysqli) { // execute the closure right now
						if ($print) {
							Debug::print("{$f} eagerly expanding foreign data structure at column \"{$column_name}\"");
						}
						$closure($event, $fds);
					} elseif ($fds->getLoadedFlag()) {
						Debug::error("{$f} column \"{$column_name}\" has already been loaded, and will never fire another after load event");
					} else { // the object is not ready for expansion; attach an event listener to make it happen when it is ready
						if ($print) {
							Debug::print("{$f} adding event listener for lazy expanding foreign data structure at column \"{$column_name}\"");
						}
						$fds->addEventListener(EVENT_AFTER_LOAD, $closure);
					}
				} elseif ($column instanceof KeyListDatum && $ds->hasForeignDataStructureList($column_name)) {
					if ($print) {
						Debug::print("{$f} column \"{$column_name}\" references many items");
					}
					$structs = $ds->getForeignDataStructureList($column_name);
					foreach ($structs as $fds_key => $fds) {
						if ($fds->getExpandedFlag()) {
							if ($print) {
								Debug::print("{$f} foreign data structure at column \"{$column_name}\" with key \"{$fds_key}\" has already been expanded");
							}
							continue;
						}
						if ($print) {
							Debug::print("{$f} about to call expandTree on a foreign data structure at column \"{$column_name}\" with hey \"{$fds_key}\"");
						}
						$event = new AfterLoadEvent();
						if ($mysqli instanceof mysqli) {
							$closure($event, $fds);
						} else {
							$fds->addEventListener(EVENT_AFTER_LOAD, $closure);
						}
					}
				} elseif ($print) {
					Debug::print("{$f} foreign data structure(s) undefined for column \"{$column_name}\"");
				}
			}
			return SUCCESS;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function dispose():void{
		$f = __METHOD__; //"Loadout(".static::getShortClass().")->dispose()";
		parent::dispose();
		if(
			isset($this->treeSelectStatements) 
			&& is_array($this->treeSelectStatements) 
			&& !empty($this->treeSelectStatements)
		){
			foreach ($this->treeSelectStatements as $phylum => $tss) {
				foreach ($tss as $class => $query) {
					if(is_array($query)){
						Debug::warning("{$f} received an array");
						Debug::printArray($query);
						Debug::printStackTrace();
					}
					$query->dispose();
					unset($this->treeSelectStatements[$phylum][$class]);
				}
				unset($this->treeSelectStatements[$phylum]);
			}
			unset($this->treeSelectStatements);
		}
		unset($this->userData);
	}
}
