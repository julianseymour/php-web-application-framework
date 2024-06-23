<?php

namespace JulianSeymour\PHPWebApplicationFramework\search;

use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\release;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StaticPropertyTypeTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\NameDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\StringEnumeratedDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\TextDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\input\ToggleInput;
use JulianSeymour\PHPWebApplicationFramework\paginate\Paginator;
use JulianSeymour\PHPWebApplicationFramework\query\select\SelectStatement;
use Exception;
use function JulianSeymour\PHPWebApplicationFramework\substitute;

class SearchPaginator extends Paginator implements StaticPropertyTypeInterface{

	use MultipleSearchClassesTrait;
	use StaticPropertyTypeTrait;

	public static function declarePropertyTypes(?StaticPropertyTypeInterface $that = null): array{
		return array_merge(parent::declarePropertyTypes($that), [
			"searchClasses" => "class",
			"searchableTimestamps" => "?".SearchTimestampDatum::class,
			"selectStatements" => SelectStatement::class
		]);
	}

	public function dispose(bool $deallocate=false): void{
		if($this->hasProperties()){
			$this->releaseProperties($deallocate);
		}
		parent::dispose($deallocate);
		$this->release($this->propertyTypes, $deallocate);
	}

	public static function getPrettyClassName():string{
		return _("Search query");
	}

	public static function getDataType(): string{
		return DATATYPE_SEARCH_QUERY;
	}

	public function hasSearchableTimestamps():bool{
		return $this->hasArrayProperty("searchableTimestamps");
	}

	public function getSearchableTimestamps(){
		return $this->getProperty("searchableTimestamps");
	}

	public static function declareColumns(array &$columns, ?DataStructure $ds = null): void{
		parent::declareColumns($columns, $ds);
		$query = new TextDatum("searchQuery");
		$query->setHumanReadableName(_("Search query"));
		$case_sensitive = new BooleanDatum("caseSensitive");
		$case_sensitive->setHumanReadableName(_("Case sensitive"));
		$case_sensitive->setDefaultValue(false);
		$search_mode = new StringEnumeratedDatum("searchMode", 8);
		$valid = [
			SEARCH_MODE_ANY,
			SEARCH_MODE_ALL,
			SEARCH_MODE_EXACT
		];
		$search_mode->setValidEnumerationMap($valid);
		$search_mode->setValue(SEARCH_MODE_ANY);
		$autosearch = new BooleanDatum("autoSearch");
		$autosearch->setDefaultValue(true);
		$autosearch->setHumanReadableName(_("Automatic search"));
		$autosearch->setElementClass(ToggleInput::class);
		array_push($columns, $query, $case_sensitive, $search_mode, $autosearch);
	}

	public static function declareFlags(): ?array{
		return array_merge(parent::declareFlags(), [
			"translate"
		]);
	}

	public function getPageLinkHTTPQueryParameters($pg){
		$f = __METHOD__;
		try{
			$print = false;
			$params = parent::getPageLinkHTTPQueryParameters($pg);
			// search options
			$params['directive'] = 'search';
			$params['searchMode'] = $this->getSearchMode();
			if($this->isCaseSensitive()){
				$params['caseSensitive'] = $this->isCaseSensitive();
			}
			if($this->hasSearchQuery()){
				$params['searchQuery'] = $this->getSearchQuery();
			}
			// searchable classes
			foreach($this->getColumns() as $column){
				$vn = $column->getName();
				if(!$column instanceof SearchFieldDatum){
					if($print){
						Debug::print("{$f} column at index \"{$vn}\" is not a SearchFieldDatum");
					}
					continue;
				}elseif(!$column->getValue()){
					if($print){
						Debug::print("{$f} column at index \"{$vn}\" is not set");
					}
					continue;
				}
				$params[$column->getName()] = 1;
				$classname = $column->getSearchClass();
				$fields = $this->getSearchFieldsData($classname);
				$superindex = $this->getColumn("fields_".get_short_class($classname))->getName();
				$fields_params = [];
				foreach($fields->getColumns() as $fields_column){
					if(!$fields_column->getValue()){
						continue;
					}
					$fields_params[$fields_column->getName()] = 1;
				}
				if(!empty($fields_params)){
					$params[$superindex] = $fields_params;
				}
			}
			// searchable timestamps
			if($this->hasSearchableTimestamps()){
				$timestamps = $this->getSearchableTimestamps();
				foreach(array_keys($timestamps) as $index){
					$timestamp = $timestamps[$index];
					if($timestamp === null){
						if($print){
							Debug::print("{$f} timestamp datum at index \"{$index}\" was nullified");
						}
						continue;
					}
					$params[$index] = [
						"start" => $timestamp->getIntervalStart(),
						"end" => $timestamp->getIntervalEnd()
					];
				}
			}elseif($print){
				Debug::print("{$f} there are no searchable timestamps");
			}
			return $params;
		}catch(Exception $x){
			x($f, $x);
		}
	}

	public function hasSearchQuery():bool{
		return $this->hasColumnValue("searchQuery");
	}

	public function setSearchQuery($query_string){
		return $this->setColumnValue("searchQuery", $query_string);
	}

	public function getSearchQuery(){
		if(!$this->hasSearchQuery()){
			return null;
		}
		return $this->getColumnValue("searchQuery");
	}

	private function generateBooleanDatum($search_class){
		$datum = new SearchFieldDatum("search".get_short_class($search_class));
		$datum->setSearchClass($search_class);
		$datum->setHumanReadableName(substitute(_("Search %1%"), $search_class::getPrettyClassNames()));
		$datum->setDataStructure($this);
		return $datum;
	}

	private function generateSubordinateFormInput(string $search_class):ForeignKeyDatum{
		$column_name = "fields_".get_short_class($search_class);
		$datum = new ForeignKeyDatum($column_name, RELATIONSHIP_TYPE_ONE_TO_ONE);
		$datum->setDataStructure($this);
		$datum->setForeignDataStructureClass(SearchFieldsData::class);
		$datum->setConverseRelationshipKeyName("searchQueryKey");
		$sqf = new SearchFieldsData();
		$sqf->setSearchClass($search_class);
		$sqf->generateSearchFieldColumns($search_class, $this);
		$this->setForeignDataStructure($column_name, $sqf);
		return $datum;
	}

	public function pushSearchClass(string $class):int{
		$f = __METHOD__;
		$boolean = $this->generateBooleanDatum($class);
		$sub = $this->generateSubordinateFormInput($class);
		$vn1 = $boolean->getName();
		$vn2 = $sub->getName();
		if($this->hasColumn($vn1) || $this->hasColumn($vn2)){
			Debug::error("{$f} already have a column named either \"{$vn1}\" or \"{$vn2}\"");
		}
		$this->pushColumn($vn1, $boolean);
		$this->pushColumn($vn2, $sub);
		return $this->pushArrayProperty("searchClasses", $class);
	}

	public function setSearchClasses($classes){
		$f = __METHOD__;
		$print = false;;
		if(empty($classes)){
			Debug::error("{$f} classes array is empty");
		}elseif($this->hasSearchClasses()){
			Debug::error("{$f} query classes have already been assigned");
		}
		foreach($classes as $c){
			if($print){
				Debug::print("{$f} generating a searchable field for class \"{$c}\"");
			}
			$boolean = $this->generateBooleanDatum($c);
			$sfi = $this->generateSubordinateFormInput($c);
			$this->pushColumn($boolean, $sfi);
		}
		return $this->setArrayProperty("searchClasses", $classes);
	}

	public static function getPrettyClassNames():string{
		return _("Search queries");
	}

	public function getSearchFieldCount($classname)
	{
		$f = __METHOD__;
		$fields = $this->getForeignDataStructure("fields_".get_short_class($classname));
		return $fields->getSearchFieldCount();
	}


	public function getSearchFieldsData(string $classname): ?SearchFieldsData{
		return $this->getForeignDataStructure("fields_".get_short_class($classname));
	}

	public function getSearchMode(){
		return $this->getColumnValue("searchMode");
	}

	public function setSearchMode($mode){
		return $this->setColumnValue("searchMode", $mode);
	}

	public function isCaseSensitive():bool{
		return $this->getColumnValue("caseSensitive");
	}

	public function setCaseSensitive($value){
		return $this->setColumnValue("caseSensitive", $value);
	}

	public function getSearchTerms(){
		$f = __METHOD__;
		$raw = $this->getColumn("searchQuery")->getValue();
		$mode = $this->getSearchMode();
		switch($mode){
			case SEARCH_MODE_ALL:
			case SEARCH_MODE_ANY:
				$exploded = explode(' ', $raw);
				break;
			case SEARCH_MODE_EXACT:
				if($raw === null){
					$exploded = [];
				}else{
					$exploded = [
						preg_replace('/\s+/', ' ', $raw)
					];
				}
				break;
			default:
				Debug::error("{$f} invalid search mode \"{$mode}\"");
		}
		if(empty($exploded)){
			return [];
		}
		if(!$this->isCaseSensitive()){
			foreach(array_keys($exploded) as $e){
				$exploded[$e] = NameDatum::normalize($exploded[$e]);
			}
		}
		$terms = [];
		foreach($exploded as $term){
			if(!empty($term)){
				array_push($terms, $term);
			}
		}
		return $terms;
	}

	public function hasSearchableTimestamp($vn):bool{
		return $this->hasArrayPropertyKey("searchableTimestamps", $vn);
	}

	public function getSearchableTimestamp($vn){
		return $this->getArrayPropertyValue("searchableTimestamps", $vn);
	}

	public function setSearchableTimestamp($vn, $searchable){
		$f = __METHOD__;
		if($this->getDebugFlag() && $vn === "insertTimestamp"){
			Debug::printStackTraceNoExit("{$f} entered");
		}
		$ret = $this->setArrayPropertyValue("searchableTimestamps", $vn, $searchable);
		if($searchable === null){
			$sts = $this->getSearchableTimestamp($vn);
			if($sts !== null){
				Debug::error("{$f} there is something critically wrong with nullifying searchable timestamps");
			}
		}
		return $ret;
	}

	public function reportSearchableTimestamp($datum){
		$vn = $datum->getName();
		$class = $datum->getDataStructure()->getClass();
		if($this->hasSearchableTimestamp($vn)){
			$this->getSearchableTimestamp($vn)->pushSearchClass($class);
		}else{
			$searchable = new SearchTimestampDatum($vn);
			$searchable->setDataStructure($this);
			$searchable->pushSearchClass($class);
			$searchable->setHumanReadableName($datum->getHumanReadableName());
			$this->setSearchableTimestamp($vn, $searchable);
		}
		return $datum;
	}

	public function getSearchableTimestampParameters($classname){
		$f = __METHOD__;
		$fields = $this->getForeignDataStructure("fields_".get_short_class($classname));
		$template = $fields->getSearchTemplateObject();
		$timestamps = $this->getSearchableTimestamps();
		$bind_timestamps = [];
		foreach($timestamps as $index => $timestamp){
			if($timestamp === null){
				Debug::print("{$f} timestamp datum at index \"{$index}\" was nullified");
				continue;
			}elseif(!$template->hasColumn($index)){
				Debug::print("{$f} class \"{$classname}\" does not have a datum \"{$index}\"");
				continue;
			}
			Debug::print("{$f} appending an integer type definition string for timestamp at index \"{$index}\"");
			$start = $timestamp->getIntervalStart();
			$end = $timestamp->getIntervalEnd();
			Debug::print("{$f} adding query parameters for timestamp interval between \"{$start}\" and \"{$end}\"");
			array_push($bind_timestamps, $start, $end);
		}
		return $bind_timestamps;
	}

	/**
	 * Duplicates parameters for use in searching multiple fields
	 *
	 * @param string $classname
	 * @param array $terms
	 * @return array
	 */
	public function duplicateMultipleFieldParameters(string $classname, $terms){
		$f = __METHOD__;
		$term_count = count($terms);
		$field_count = $this->getSearchFieldCount($classname);
		$total_count = $term_count * $field_count;
		$bind_params = $terms;
		while(count($bind_params) < $total_count){
			$param_count = count($bind_params);
			Debug::print("{$f} parameter count {$param_count} is less than total required parameter count {$total_count}");
			$bind_params = array_merge($bind_params, $terms);
		}
		return $bind_params;
	}
}
