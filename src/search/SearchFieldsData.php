<?php

namespace JulianSeymour\PHPWebApplicationFramework\search;

use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\OrCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TimestampDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;

class SearchFieldsData extends DataStructure{

	use SearchClassTrait;

	protected $searchTemplateObject;
	
	public function dispose(): void{
		parent::dispose();
		unset($this->searchClass);
		unset($this->searchTemplateObject);
	}
	
	public static function getDefaultPersistenceModeStatic():int{
		return PERSISTENCE_MODE_UNDEFINED;
	}
	
	public static function getPrettyClassName():string{
		return _("Search fields");
	}

	public static function getPrettyClassNames():string{
		return static::getPrettyClassName();
	}

	public static function getDataType(): string{
		return DATATYPE_SEARCH_FIELDS;
	}

	public static function getPhylumName(): string{
		return "searchFields";
	}

	public function setSearchPaginator($sqd){
		return $this->setForeignDataStructure("searchQueryKey", $sqd);
	}

	public function hasSearchPaginator(){
		return $this->hasForeignDataStructure("searchQueryKey");
	}

	public function getSearchPaginator(): ?SearchPaginator{
		$f = __METHOD__;
		if (! $this->hasSearchPaginator()) {
			Debug::error("{$f} search paginator is undefined");
		}
		return $this->getForeignDataStructure("searchQueryKey");
	}

	public function getSearchFieldCount(){
		$f = __METHOD__;
		$count = 0;
		foreach ($this->getFilteredColumns(COLUMN_FILTER_VALUED, "!" . COLUMN_FILTER_VIRTUAL) as $column) {
			$vn = $column->getName();
			if (! $column instanceof SearchFieldDatum) {
				Debug::error("{$f} column \"{$vn}\" is not a search field datum");
			} elseif (! $column->getValue()) {
				// Debug::print("{$f} column \"{$vn}\" is not set");
				continue;
			}
			$count ++;
		}
		// Debug::print("{$f} returning {$count}");
		return $count;
	}

	public static function getKeyGenerationMode(): int{
		return KEY_GENERATION_MODE_NATURAL;
	}

	public static function getIdentifierNameStatic(): ?string{
		return "fieldId";
	}

	public function generateSelectStatement(): ?SelectStatement{
		$f = __METHOD__;
		try {
			$print = $this->getDebugFlag();
			$query = $this->getSearchClass()::selectStatic();
			$sqd = $this->getSearchPaginator();
			$terms = $sqd->getSearchTerms();
			if (empty($terms)) {
				if ($print) {
					Debug::print("{$f} no search terms");
				}
				if ($sqd->hasSearchableTimestamps()) {
					return $query;
				}
				$this->setObjectStatus(ERROR_NULL_SEARCH_QUERY);
				return null;
			}
			$mode = $sqd->getSearchMode();
			$object = $this->getSearchTemplateObject();
			$or = new OrCommand();
			$aliases = [];
			switch ($mode) {
				case SEARCH_MODE_ALL:
				case SEARCH_MODE_ANY:
				case SEARCH_MODE_EXACT:
					foreach ($terms as $term) {
						if (empty($term)) {
							Debug::error("{$f} empty search term");
						}
						foreach ($this->getFilteredColumns(COLUMN_FILTER_VALUED, "!" . COLUMN_FILTER_VIRTUAL) as $column) {
							$match = new MatchFunction();
							$match->setParameterCount(1);
							$vn = $column->getName();
							if ($print) {
								Debug::print("{$f} evaluating variable \"{$vn}\" for class \"{$this->searchClass}\"");
							}
							if (! $column instanceof SearchFieldDatum) {
								Debug::error("{$f} column \"{$vn}\" is not a search field datum");
							} elseif (! $column->getValue()) {
								if ($print) {
									Debug::print("{$f} column \"{$vn}\" is not set");
								}
								continue;
							}
							$datum = $object->getColumn($column->getFieldName());
							if ($print) {
								Debug::print("{$f} column \"{$vn}\" is set");
							}
							$column_name = $datum->getName();
							if($datum->getPersistenceMode() === PERSISTENCE_MODE_ALIAS) {
								if ($print) {
									Debug::print("{$f} column \"{$column_name}\" is aliased");
								}
								$db = $datum->getSubqueryDatabaseName();
								$atn = $datum->getSubqueryTableName();
								$rcn = $datum->getReferenceColumnName();
								if (! array_key_exists($rcn, $aliases)) {
									$aliases[$rcn] = QueryBuilder::select($datum->getSubqueryClass()::getIdentifierNameStatic())->from($db, $atn);
								}
								$alias = $aliases[$rcn];
								$match = new MatchFunction();
								$match->setParameterCount(1);
								$match->pushColumnNames($datum->getSubqueryColumnName());
								if ($alias->hasWhereCondition()) {
									$alias->pushWhereConditionParameters($match);
								} else {
									$alias->setWhereCondition(new OrCommand($match));
								}
								Debug::print("{$f} alias \"{$alias}\"");
								continue;
							}
							$match->pushColumnNames($column_name);
							if ($print) {
								Debug::print("{$f} generated match condition \"{$match}\"");
							}
							$or->pushParameters($match);
						}
						if(!empty($aliases)){
							if ($print) {
								$count = count($aliases);
								Debug::print("{$f} we are searching {$count} aliased columns");
							}
							foreach ($aliases as $rcn => $alias) {
								$where = new WhereCondition($rcn, OPERATOR_IN, 's');
								$where->setSelectStatement($alias);
								$or->pushParameters($where);
							}
							$aliases = [];
						}elseif($print){
							Debug::print("{$f} there are no aliases columns");
						}
						if($match->getColumnNameCount() === 0){
							Debug::printGet("{$f} 0 conditions");
						}
					}
					break;
				/*
				case SEARCH_MODE_EXACT:
					foreach($this->getColumns() as $column){
						$condition = new WhereCondition($terms[0], OPERATOR_CONTAINS);
						array_push($conditions, $condition);
					}
					break;
				*/
				default:
					$gottype = gettype($mode);
					if ($gottype !== gettype(2)) {
						Debug::error("{$f} invalid search mode datatype \"{$gottype}\"");
					}
					Debug::error("{$f} invalid search mode \"{$mode}\"");
			}
			$ret = $query->where($or); // where);
			if ($print) {
				Debug::print("{$f} returning \"{$ret}\"");
			}
			return $ret;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$status = new VirtualDatum("status");
		$fieldId = new VirtualDatum("fieldId");
		array_push($columns, $status, $fieldId);
	}

	public function getVirtualColumnValue(string $columnName){
		switch ($columnName) {
			case "fieldId":
				return $this->getFieldId();
			default:
				return parent::getVirtualColumnValue($columnName);
		}
	}

	public function hasVirtualColumnValue(string $columnName): bool{
		switch ($columnName) {
			case "fieldId":
				return $this->hasSearchClass();
			default:
				return parent::hasVirtualColumnValue($columnName);
		}
	}

	public function getFieldId(){
		$classname = $this->getSearchClass();
		return "fields_".get_short_class($classname);
	}

	private function generateBooleanDatum($c){
		$f = __METHOD__;
		$name = $c->getName();
		$datum = new SearchFieldDatum("search_{$name}");
		$datum->setSearchClass($this->getSearchClass());
		$datum->setFieldName($name);
		if (! $c->hasDataStructure()) {
			Debug::error("{$f} datum at index \"{$name}\" lacks a data structure");
		}
		$human_readable = $c->getHumanReadableName();
		// Debug::print("{$f} human readable variable name is \"{$human_readable}\"");
		$datum->setHumanReadableName($human_readable);
		$datum->setDataStructure($this);
		return $datum;
	}

	public function setSearchTemplateObject($o){
		return $this->searchTemplateObject = $o;
	}

	public function hasSearchTemplateObject():bool{
		return isset($this->searchTemplateObject) && is_object($this->searchTemplateObject);
	}

	public function getSearchTemplateObject(){
		$f = __METHOD__;
		if (! $this->hasSearchTemplateObject()) {
			Debug::error("{$f} search template object is undefined");
		}
		return $this->searchTemplateObject;
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"translate"
		]);
	}

	public function setSearchClass(?string $class): ?string{
		$f = __METHOD__;
		try {
			$print = false;
			if ($this->hasSearchClass()) {
				Debug::error("{$f} query class is already defined");
			}
			$this->searchClass = $class;
			$user = user();
			$object = new $class();
			$object->setForeignDataStructure("searchQueryKey", $this->getSearchPaginator());
			$this->setSearchTemplateObject($object);
			$columns = [];
			foreach ($object->getFilteredColumns(COLUMN_FILTER_SEARCHABLE) as $column_name => $c) {
				$vn = $c->getName();
				if ($c->isSearchable()) {
					if ($c instanceof TimestampDatum) {
						if ($print) {
							Debug::print("{$f} reporting searchable timestamp");
						}
						$this->getSearchPaginator()->reportSearchableTimestamp($c);
						continue;
					/*} elseif ($c instanceof NameDatum && $c->isTranslatable()) {
						$pref = $user->getLanguagePreference();
						if($print){
							Debug::print("{$f} user does not have default language preference");
						}
						if (array_key_exists($pref, $columns)) {
							continue;
						}
						$this->setFlag("translate", true);*/
					} elseif ($print) {
						Debug::print("{$f} variable \"{$vn}\" is not a search name datum, or not translatable");
					}
					$boolean = $this->generateBooleanDatum($c);
					$cn2 = $boolean->getName();
					$columns[$cn2] = $boolean;
					if ($print) {
						Debug::print("{$f} pushed column \"{$cn2}\"");
					}
				} elseif ($print) {
					Debug::print("{$f} variable \"{$vn}\" is not searchable");
				}
			}
			if (empty($columns)) {
				if ($print) {
					Debug::print("{$f} no searchable columns in class \"{$class}\"");
				}
				return $this->getSearchClass();
			}
			$existing = $this->hasColumns() ? $this->getColumns() : [];
			$this->setColumns(array_merge($existing, $columns));
			return $this->getSearchClass();
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
