<?php

namespace JulianSeymour\PHPWebApplicationFramework\search;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\getDateTimeStringFromTimestamp;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadoutGenerator;
use JulianSeymour\PHPWebApplicationFramework\paginate\Paginator;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class SearchLoadoutGenerator extends LoadoutGenerator{
	
	use MultipleSearchClassesTrait;
	
	public function getPaginator(UseCase $use_case): ?Paginator{
		$f = __METHOD__;
		$print = false;
		if($this->hasPaginator()) {
			if($print){
				Debug::print("{$f} paginator was already defined");
			}
			return parent::getPaginator($use_case);
		}elseif($print){
			Debug::print("{$f} instantiating a new SearchPaginator");
		}
		$paginator = new SearchPaginator();
		$paginator->setSearchClasses($use_case->getSearchClasses());
		return $this->setPaginator($paginator);
	}
	
	public function getSearchLoadoutGenerator():SearchLoadoutGenerator{
		return $this;
	}
	
	/**
	 * hook for applying additional where conditions
	 *
	 * @param string $classname
	 * @param SelectStatement $query
	 * @return SelectStatement
	 */
	function modifySelectStatement(string $classname, SelectStatement $query, UseCase $use_case){
	}
	
	public function extendSearchParameters($class){
		$f = __METHOD__;
		return null;
		Debug::error("{$f} unimplemented. Class is ".$this->getShortClass().", instantiated ".$this->getDeclarationLine());
		if($this->hasPredecessor()) {
			return $this->getPredecessor()->extendSearchParameters($class);
		}
		return null;
	}
	
	public function hasSelectStatements():bool{
		return $this->hasArrayProperty("selectStatements");
	}
	
	public function getSelectStatements(UseCase $use_case): ?array{
		$f = __METHOD__;
		$print = false;
		if($this->hasSelectStatements()) {
			if($print) {
				Debug::print("{$f} select statements were already generated");
			}
			return $this->getProperty("selectStatements");
		}elseif($print) {
			$did = $this->getDebugId();
			Debug::print("{$f} about to generate select statements for SearchLoadoutGenerator with debug ID {$did}");
		}
		$statements = $this->generateSelectStatements($use_case);
		if(empty($statements)){
			Debug::error("{$f} generated zero select statements");
		}
		return $this->setSelectStatements($statements);
	}
	
	/**
	 * generates an unpaginated select query with timestamp constraints for class $classname
	 *
	 * @param string $classname
	 *        	| name of DataStructure class to generate select query for
	 * @param number $offset
	 *        	| the current user's timezone offset relative to the server time
	 * @return SelectStatement
	 */
	private function generateSelectStatementForClass(string $classname, $offset = 0): ?SelectStatement{
		$f = __METHOD__;
		try{
			$print = $this->getDebugFlag();
			$short_class = get_short_class($classname);
			$use_case = app()->getUseCase();
			$paginator = $this->getPaginator($use_case);
			$fields = $paginator->getSearchFieldsData($short_class);
			$select = $fields->generateSelectStatement();
			if($select == null) {
				if($print) {
					Debug::print("{$f} received a null query for class \"{$classname}\"");
				}
				return null;
			}elseif($select->hasTableName() && $select->getTableName() === "data.fields") {
				Debug::error("{$f} table name is data.fields");
			}
			$template = $fields->getSearchTemplateObject();
			$search_params = getInputParameters();
			if($paginator->hasSearchableTimestamps()) {
				foreach($this->getSearchableTimestamps() as $ts_index => $timestamp) {
					if($timestamp === null) {
						if($print) {
							Debug::print("{$f} searchable timestamp at index \"{$ts_index}\" was nullified because its values were not posted");
						}
						continue;
					}elseif(!$template->hasColumn($ts_index)) {
						if($print) {
							Debug::print("{$f} object does not have a datum at index \"{$ts_index}\"");
						}
						continue;
					}elseif(! array_key_exists($ts_index, $search_params)) {
						if($print) {
							Debug::print("{$f} index \"{$ts_index}\" was not posted");
						}
						$this->setSearchableTimestamp($ts_index, null);
						$check = $this->getSearchableTimestamp($ts_index);
						if($check !== null) {
							$gottype = gettype($check);
							Debug::error("{$f} fuck a duck, looks like nullifying searchable timestamps is busted. Type is \"{$gottype}\"");
						}else{
							Debug::print("{$f} flagging this as deleted nulled that fucker");
							$this->setFlag("nulledThatFucker");
						}
						continue;
					}else{
						$values = $search_params[$ts_index];
						if(empty($values["start"]) || empty($values["end"])) {
							if($print) {
								Debug::print("{$f} start/end time is empty for index \"{$ts_index}\"");
							}
							$this->setSearchableTimestamp($ts_index, null);
							continue;
						}elseif($print) {
							Debug::print("{$f} about to print start and end interval timestamps");
							Debug::printArray($values);
						}
						if(ctype_digit($values['start']) && ctype_digit($values['end'])) {
							if($print) {
								Debug::print("{$f} interval timestamps are already in integer format");
							}
							$start = intval($values['start']);
							$end = intval($values['end']);
						}elseif(is_string($values['start']) && is_string($values['end'])) {
							if($print) {
								Debug::print("{$f} interval timestamps are strings");
							}
							$start = strtotime($values["start"]) + $offset;
							$end = strtotime($values["end"]) + 24 * 60 * 60 - 1 + $offset;
						}else{
							Debug::error("{$f} neither of the above");
						}
						$timestamp->setIntervalStart($start);
						$timestamp->setIntervalEnd($end);
						$start_str = getDateTimeStringFromTimestamp($start);
						$end_str = getDateTimeStringFromTimestamp($end);
						if($print) {
							Debug::print("{$f} start timestamp: \"{$start}\", \"{$start_str}\"; end timestamp: \"{$end}\", \"{$end_str}\"");
						}
						$parameters = [
							new WhereCondition($ts_index, OPERATOR_GREATERTHANEQUALS),
							new WhereCondition($ts_index, OPERATOR_LESSTHANEQUALS)
						];
						$select->pushWhereConditionParameters(...$parameters);
					}
				}
			}
			if(!$select->hasMatchFunction()) {
				Debug::error("{$f} before modification by use case, query statement lacks a match function");
			}elseif($print) {
				Debug::print("{$f} before modification by the use case, query statement is \"{$select}\"");
			}
			$use_case->getLoadoutGenerator(user())->modifySelectStatement($classname, $select, $use_case);
			if(!$select->hasMatchFunction()) {
				Debug::error("{$f} after modification by the use case, query lacks a match function");
			} else if($print) {
				Debug::print("{$f} after handing it over to the use case to mangle, select query is now \"{$select}\"");
			}
			$template = null;
			return $select;
		}catch(Exception $x) {
			x($f, $x);
		}
	}
	
	/**
	 * Generates unpaginated select queries for all the classes searched by this object
	 *
	 * @return array
	 */
	private function generateSelectStatements(UseCase $use_case): ?Array{
		$f = __METHOD__;
		try{
			$print = false;
			$user = user();
			$offset = $user->timezone_offset() * 60 * 60;
			if($print) {
				Debug::print("{$f} user timezone offset is \"{$offset}\"");
			}
			$queries = [];
			$paginator = $this->getPaginator($use_case);
			$columns = $paginator->getColumns();
			if(empty($columns)){
				Debug::error("{$f} no columns to search");
			}
			foreach($columns as $vn => $column) {
				if($column instanceof SearchFieldDatum) {
					if($column->getValue()) {
						if($print) {
							Debug::print("{$f} column \"{$vn}\" is set");
						}
						$classname = $column->getSearchClass();
						if($print) {
							Debug::print("{$f} class name is \"{$classname}\"");
						}
						$select = $this->generateSelectStatementForClass($classname, $offset);
						if($select !== null) {
							if($print) {
								Debug::print("{$f} query for class \"{$classname}\" is \"{$select}\"");
							}
							$queries[$classname] = $select;
						}elseif($print) {
							Debug::print("{$f} null query generated for class \"{$classname}\"");
						}
					}elseif($print) {
						Debug::print("{$f} column \"{$vn}\" is not set");
					}
				}elseif($print){
					Debug::print("{$f} column \"{$vn}\" is not a search field");
				}
			}
			$count = count($queries);
			if($count == 0) {
				if($print) {
					Debug::printPost("{$f} generated 0 queries");
				}
				$this->setObjectStatus(ERROR_NULL_SEARCH_QUERY);
				return null;
			}elseif($print) {
				Debug::print("{$f} generated {$count} queries");
			}
			return $this->setSelectStatements($queries);
		}catch(Exception $x) {
			x($f, $x);
		}
	}
	
	public function setSelectStatements($queries){
		return $this->setArrayProperty("selectStatements", $queries);
	}
	
	public function setSelectStatement($classname, $select){
		return $this->setArrayPropertyValue("selectStatements", $classname, $select);
	}
}

