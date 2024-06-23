<?php

namespace JulianSeymour\PHPWebApplicationFramework\search;

use function JulianSeymour\PHPWebApplicationFramework\backwards_ref_enabled;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\expression\OrCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\Datum;
use JulianSeymour\PHPWebApplicationFramework\datum\TimestampDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\VirtualDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\query\QueryBuilder;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use JulianSeymour\PHPWebApplicationFramework\query\where\WhereCondition;
use Exception;

class SearchFieldsData extends DataStructure{

	use SearchClassTrait;

	protected $searchTemplateObject;
	
	public function dispose(bool $deallocate=false): void{
		parent::dispose($deallocate);
		$this->release($this->searchClass, $deallocate);
		$this->release($this->searchTemplateObject, $deallocate);
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
		if(!$this->hasSearchPaginator()){
			Debug::error("{$f} search paginator is undefined");
		}
		return $this->getForeignDataStructure("searchQueryKey");
	}

	public function getSearchFieldCount(){
		$f = __METHOD__;
		$count = 0;
		foreach($this->getFilteredColumns(COLUMN_FILTER_VALUED, "!".COLUMN_FILTER_VIRTUAL) as $column_name => $column){
			if(!$column instanceof SearchFieldDatum){
				Debug::error("{$f} column \"{$column_name}\" is not a search field datum");
			}elseif(!$column->getValue()){
				// Debug::print("{$f} column \"{$column_name}\" is not set");
				continue;
			}
			$count++;
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
		try{
			$print = false && $this->getDebugFlag();
			$query = $this->getSearchClass()::selectStatic();
			$sqd = $this->getSearchPaginator();
			$terms = $sqd->getSearchTerms();
			if(empty($terms)){
				if($print){
					Debug::print("{$f} no search terms");
				}
				if($sqd->hasSearchableTimestamps()){
					return $query;
				}
				$this->setObjectStatus(ERROR_NULL_SEARCH_QUERY);
				return null;
			}
			$mode = $sqd->getSearchMode();
			$object = $this->getSearchTemplateObject();
			$or = new OrCommand();
			$aliases = [];
			switch($mode){
				case SEARCH_MODE_ALL:
				case SEARCH_MODE_ANY:
				case SEARCH_MODE_EXACT:
					foreach($terms as $term){
						if(empty($term)){
							Debug::error("{$f} empty search term");
						}
						foreach($this->getFilteredColumns(COLUMN_FILTER_VALUED, "!".COLUMN_FILTER_VIRTUAL) as $vn => $column){
							$match = new MatchFunction();
							$match->setParameterCount(1);
							$vn = $column->getName();
							if($print){
								Debug::print("{$f} evaluating variable \"{$vn}\" for class \"{$this->searchClass}\"");
							}
							if(!$column instanceof SearchFieldDatum){
								Debug::error("{$f} column \"{$vn}\" is not a search field datum");
							}elseif(!$column->getValue()){
								if($print){
									Debug::print("{$f} column \"{$vn}\" is not set");
								}
								continue;
							}
							$datum = $object->getColumn($column->getColumnName());
							if($print){
								Debug::print("{$f} column \"{$vn}\" is set");
							}
							$column_name = $datum->getName();
							if($datum->getPersistenceMode() === PERSISTENCE_MODE_ALIAS){
								if($print){
									Debug::print("{$f} column \"{$column_name}\" is aliased");
								}
								$db = $datum->getSubqueryDatabaseName();
								$atn = $datum->getSubqueryTableName();
								$rcn = $datum->getReferenceColumnName();
								if(!array_key_exists($rcn, $aliases)){
									$aliases[$rcn] = QueryBuilder::select($datum->getSubqueryClass()::getIdentifierNameStatic())->from($db, $atn);
								}
								$alias = $aliases[$rcn];
								$match = new MatchFunction();
								$match->setParameterCount(1);
								$match->pushColumnNames($datum->getSubqueryColumnName());
								if($alias->hasWhereCondition()){
									$alias->pushWhereConditionParameters($match);
								}else{
									$alias->setWhereCondition(new OrCommand($match));
								}
								Debug::print("{$f} alias \"{$alias}\"");
								continue;
							}
							$match->pushColumnNames($column_name);
							if($print){
								Debug::print("{$f} generated match condition \"{$match}\"");
							}
							$or->pushParameters($match);
						}
						if(!empty($aliases)){
							if($print){
								$count = count($aliases);
								Debug::print("{$f} we are searching {$count} aliased columns");
							}
							foreach($aliases as $rcn => $alias){
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
					if($gottype !== gettype(2)){
						Debug::error("{$f} invalid search mode datatype \"{$gottype}\"");
					}
					Debug::error("{$f} invalid search mode \"{$mode}\"");
			}
			$ret = $query->where($or); // where);
			if($print){
				Debug::print("{$f} returning \"{$ret}\"");
			}
			return $ret;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		$status = new VirtualDatum("status");
		$fieldId = new VirtualDatum("fieldId");
		$paginator = new ForeignKeyDatum("searchQueryKey", RELATIONSHIP_TYPE_MANY_TO_ONE);
		if(!BACKWARDS_REFERENCES_ENABLED){
			$paginator->setRank(RANK_PARENT);
		}
		$paginator->setForeignDataStructureClass(SearchPaginator::class);
		$paginator->volatilize();
		array_push($columns, $status, $fieldId, $paginator);
	}

	public function getVirtualColumnValue(string $columnName){
		switch($columnName){
			case "fieldId":
				return $this->getFieldId();
			default:
				return parent::getVirtualColumnValue($columnName);
		}
	}

	public function hasVirtualColumnValue(string $columnName): bool{
		switch($columnName){
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

	private function generateBooleanDatum(Datum $searchable_column, ?string $search_class=null){
		$f = __METHOD__;
		if($search_class === null){
			if(!$this->hasSearchClass()){
				Debug::error("{$f} search class is undefined");
			}
			$search_class = $this->getSearchClass();
		}
		$name = $searchable_column->getName();
		$boolean_datum = new SearchFieldDatum("search_{$name}");
		$boolean_datum->setSearchClass($search_class);
		$boolean_datum->setColumnName($name);
		if(!$searchable_column->hasDataStructure()){
			Debug::error("{$f} datum at index \"{$name}\" lacks a data structure");
		}
		$human_readable = $searchable_column->getHumanReadableName();
		// Debug::print("{$f} human readable variable name is \"{$human_readable}\"");
		$boolean_datum->setHumanReadableName($human_readable);
		$boolean_datum->setDataStructure($this);
		return $boolean_datum;
	}

	public function setSearchTemplateObject($o){
		if($this->hasSearchTemplateObject()){
			$this->release($this->searchTemplateObject);
		}
		return $this->searchTemplateObject = $this->claim($o);
	}

	public function hasSearchTemplateObject():bool{
		return isset($this->searchTemplateObject) && is_object($this->searchTemplateObject);
	}

	public function getSearchTemplateObject(){
		$f = __METHOD__;
		if(!$this->hasSearchTemplateObject()){
			Debug::error("{$f} search template object is undefined");
		}
		return $this->searchTemplateObject;
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"translate"
		]);
	}

	public function generateSearchFieldColumns(?string $search_class=null, ?SearchPaginator $paginator=null){
		$f = __METHOD__;
		$print = false;
		if($search_class === null){
			if(!$this->hasSearchClass()){
				Debug::error("{$f} this function is useless without search class");
			}
			$search_class = $this->getSearchClass();
		}
		if($paginator === null){
			if(!$this->hasSearchPaginator()){
				Debug::error("{$f} this function is useless without SearchPaginator");
			}
			$paginator = $this->getSearchPaginator();
		}
		$object = new $search_class();
		$paginator->subordinateForeignDataStructure("searchTemplates", $object, "searchQueryKey", RELATIONSHIP_TYPE_ONE_TO_MANY);
		$object->setForeignDataStructure("searchQueryKey", $paginator);
		$this->setSearchTemplateObject($object);
		foreach($object->getFilteredColumns(COLUMN_FILTER_SEARCHABLE) as $column_name => $c){
			if($c instanceof TimestampDatum){
				if($print){
					Debug::print("{$f} reporting searchable timestamp");
				}
				$paginator->reportSearchableTimestamp($c);
				continue;
			}elseif($print){
				Debug::print("{$f} column \"{$column_name}\" is not a TimestampDatum");
			}
			$boolean = $this->generateBooleanDatum($c, $search_class);
			$this->pushColumn($boolean);
			if($print){
				$cn2 = $boolean->getName();
				Debug::print("{$f} pushed column \"{$cn2}\"");
			}
		}
	}
	
	public function setSearchClass(?string $search_class): ?string{
		$f = __METHOD__;
		if($this->hasSearchClass()){
			Debug::error("{$f} query class is already defined");
		}
		return $this->searchClass = $this->claim($search_class);
	}
}
