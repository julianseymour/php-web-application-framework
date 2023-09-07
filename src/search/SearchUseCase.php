<?php

namespace JulianSeymour\PHPWebApplicationFramework\search;

use function JulianSeymour\PHPWebApplicationFramework\db;
use function JulianSeymour\PHPWebApplicationFramework\directive;
use function JulianSeymour\PHPWebApplicationFramework\getInputParameters;
use function JulianSeymour\PHPWebApplicationFramework\getTypeSpecifier;
use function JulianSeymour\PHPWebApplicationFramework\get_class_filename;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\request;
use function JulianSeymour\PHPWebApplicationFramework\user;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\account\PlayableUser;
use JulianSeymour\PHPWebApplicationFramework\app\Request;
use JulianSeymour\PHPWebApplicationFramework\app\Responder;
use JulianSeymour\PHPWebApplicationFramework\app\pwa\ProgressiveHyperlinkResponder;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\data\DataStructure;
use JulianSeymour\PHPWebApplicationFramework\db\credentials\PublicReadCredentials;
use JulianSeymour\PHPWebApplicationFramework\db\load\Loadout;
use JulianSeymour\PHPWebApplicationFramework\db\load\LoadoutGenerator;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\use_case\ClientUseCaseInterface;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class SearchUseCase extends UseCase implements ClientUseCaseInterface{

	use MultipleSearchClassesTrait;
	
	public static function getJavaScriptClassPath():?string{
		$fn = get_class_filename(SearchUseCase::class);
		return substr($fn, 0, strlen($fn) - 3) . "js";
	}

	public function isPageUpdatedAfterLogin():bool{
		return true;
	}

	public function getActionAttribute():?string{
		return "/search";
	}

	protected function getExecutePermissionClass(){
		return SUCCESS;
	}

	public function hasDataOperandObject():bool{
		return isset($this->dataOperandObject) && is_object($this->dataOperandObject);
	}

	public function getSearchFormClass(){
		if ($this->hasPredecessor()) {
			return $this->getPredecessor()->getSearchFormClass();
		}
		return SearchForm::class;
	}

	public function getSearchForm(){
		$f = __METHOD__;
		$print = false;
		$use_case = $this->hasPredecessor() ? $this->getPredecessor() : $this;
		$generator = $this->getLoadoutGenerator(user());
		if($print){
			Debug::print("{$f} loadout generator class is ".$generator->getShortClass());
		}
		$paginator = $generator->getPaginator($use_case);
		if(!$paginator instanceof SearchPaginator){
			$decl = $paginator->getDeclarationLine();
			Debug::error("{$f} not a search paginator, declared {$decl}");
		}
		$search_form_class = $this->getSearchFormClass();
		$form = new $search_form_class(ALLOCATION_MODE_LAZY, $paginator);
		$form->setActionAttribute($this->getActionAttribute());
		$form->setSuccessCallback($this->getSuccessCallback());
		return $form;
	}

	public function getSuccessCallback(): string{
		return "controller";
	}

	public function getSearchResultElementClass($obj):string{
		return SearchResultElement::class;
	}

	public function getResultContainer(){
		$mode = ALLOCATION_MODE_LAZY;
		$result_container = $this->getInsertHereElement();
		if ($this->hasSearchResults()) {
			$elements = [];
			foreach ($this->getSearchResults() as $r) {
				$ec = $this->getSearchResultElementClass($r);
				$e = new $ec($mode, $r);
				array_push($elements, $e);
			}
			if (! empty($elements)) {
				$result_container->appendChild(...$elements);
			}
		}
		return $result_container;
	}

	public function getPageContent(): ?array{
		$f = __METHOD__;
		if ($this->hasPredecessor()) {
			return $this->getPredecessor()->getPageContent();
		}
		$result_container = $this->getResultContainer();
		return [
			$this->getSearchForm(),
			$result_container
		];
	}

	public function getLoadoutGeneratorClass(?PlayableUser $object = null): ?string{
		$f = __METHOD__;
		$print = false;
		if ($this->hasPredecessor()) {
			$predecessor = $this->getPredecessor();
			$ret = $predecessor->getLoadoutGeneratorClass($object);
			if($print){
				Debug::print("{$f} returning predecessor of class ".get_short_class($predecessor)."'s loadout generator class \"{$ret}\"");
			}
			return $ret;
		}elseif($print){
			Debug::print("{$f} returning SearchLoadoutGenerator");
		}
		return SearchLoadoutGenerator::class;
	}

	public function execute(): int{
		$f = __METHOD__;
		try {
			$print = false;
			if (! static::isSearchEvent()) {
				if ($print) {
					Debug::print("{$f} this is not a search event");
				}
				return $this->setObjectStatus(SUCCESS);
			}
			$search_params = getInputParameters();
			if ($print) {
				Debug::print("{$f} about to print input parameters array");
				Debug::printArray($search_params);
			}
			$generator = $this->getLoadoutGenerator(user());
			if($print){
				Debug::print("{$f} loadout generator class is ".$generator->getShortClass());
			}
			$use_case = $this->hasPredecessor() ? $this->getPredecessor() : $this;
			$paginator = $generator->getPaginator($use_case);
			if (! $paginator->hasProcessedForm()) {
				$form = $this->getSearchForm();
				$status = $paginator->processForm($form, $search_params);
				if ($status !== SUCCESS) {
					$err = ErrorMessage::getResultMessage($status);
					Debug::warning("{$f} processing form returned error status \"{$err}\"");
					return $this->setObjectStatus($status);
				}
			} elseif ($print) {
				Debug::print("{$f} paginator has already processed its search form");
			}
			$queries = $generator->getSearchLoadoutGenerator()->getSelectStatements($use_case);
			$count = count($queries);
			if ($count === 0) {
				Debug::error("{$f} generated 0 queries");
			} elseif ($print) {
				Debug::print("{$f} generated {$count} queries");
			}
			$mysqli = db()->getConnection(PublicReadCredentials::class);
			$terms = $paginator->getSearchTerms();
			$objects = [];
			foreach ($queries as $classname => $query) {
				if (! $query->hasTableName() && ! $query->hasJoinExpressions()) {
					$query->setTableName("undefined");
					Debug::error("{$f} query \"{$query}\" for class \"{$classname}\" lacks a table name");
				} elseif (! $query->hasMatchFunction()) {
					Debug::error("{$f} query statement lacks a match function");
				} elseif ($query->hasTableName() && $query->getTableName() === "data.fields") {
					Debug::error("{$f} table name is data.fields");
				}
				// update type definition string and parameter list for individual class
				$bind_params = $paginator->duplicateMultipleFieldParameters($classname, $terms);
				$param_count = count($bind_params);
				if ($print) {
					Debug::print("{$f} parameter count after repeatedly merging terms is {$param_count}");
				}
				// modify query with searchable timestamps
				if ($paginator->hasSearchableTimestamps()) {
					$bind_timestamps = $paginator->getSearchableTimestampParameters($classname);
					if (! empty($bind_timestamps)) {
						$bind_params = array_merge($bind_params, $bind_timestamps);
					}
				}
				// hooks for extending search terms and type definition string in use case
				$generator = $this->getLoadoutGenerator();
				$hook_params = $generator->extendSearchParameters($classname);
				if (! empty($hook_params)) {
					if ($print) {
						Debug::print("{$f} merging hook parameters");
					}
					$bind_params = array_merge($bind_params, $hook_params);
				} elseif ($print) {
					Debug::print("{$f} hook parameters empty");
				}
				// prepare and execute query
				$typedef = getTypeSpecifier($bind_params);
				$length = strlen($typedef);
				$count = count($bind_params);
				if ($length !== $count) {
					Debug::error("{$f} type definition string length \"{$length}\" does not equal parameter count \"{$count}\"");
				}
				if ($print) {
					$qstring = $query->toSQL();
					Debug::print("{$f} about to execute query \"{$qstring}\" with type definition string \"{$typedef}\" including timestamps if applicable");
					// Debug::printArray($bind_params);
				}
				$result = $query->prepareBindExecuteGetResult($mysqli, $typedef, ...$bind_params);
				$results = $result->fetch_all(MYSQLI_ASSOC);
				if ($print) {
					if (empty($results)) {
						Debug::print("{$f} no results for query \"{$qstring}\"");
						continue;
					} else {
						$count = count($results);
						Debug::print("{$f} {$count} results for query \"{$qstring}\"");
					}
				}
				// create objects from results
				foreach ($results as $r) {
					$object = new $classname();
					$object->processQueryResultArray($mysqli, $r);
					$object->loadIntersectionTableKeys($mysqli);
					$object->setAutoloadFlags(true);
					$generator = $this->getLoadoutGenerator(user());
					if($generator instanceof LoadoutGenerator){
						$loadout = $generator->generateNonRootLoadout($object, $this);
						if ($loadout instanceof Loadout) {
							$loadout->expandTree($mysqli, $object);
						}
					}elseif($print){
						Debug::print("{$f} loadout generator class is undefined");
					}
					$object->loadForeignDataStructures($mysqli, false, 3);
					$object->setSearchResultFlag(true);
					array_push($objects, $object);
				}
			}
			$count = count($objects);
			if ($count === 0) {
				if ($print) {
					Debug::print("{$f} zero search results");
				}
				return $this->setObjectStatus(ERROR_0_SEARCH_RESULTS);
			} elseif ($print) {
				Debug::print("{$f} search returned {$count} results");
			}
			$this->setSearchResults($objects);
			return $this->setObjectStatus(SUCCESS);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function getTransitionFromPermission(){
		return SUCCESS;
	}

	public function setSearchResults($results){
		$f = __METHOD__;
		if ($results === null) {
			Debug::print("{$f} search results are null");
		} elseif (! is_array($results)) {
			Debug::error("{$f} search results is something other than an array or null");
		} elseif (! Request::isXHREvent() && ! Request::isFetchEvent()) {
			$user = user();
			foreach ($results as $r) {
				$phylum = $r->getPhylumName();
				$user->setForeignDataStructureListMember($phylum, $r);
			}
		}
		return $this->setArrayProperty("searchResults", $results);
	}

	public function hasSearchResults():bool{
		return $this->hasArrayProperty("searchResults");
	}

	public function getSearchResults(){
		$f = __METHOD__;
		if (! $this->hasSearchResults()) {
			return null;
			Debug::error("{$f} search results are undefined");
		}
		return $this->getProperty("searchResults");
	}

	public function getInsertHereElement(?DataStructure $ds = null){
		if ($this->hasPredecessor()) {
			return $this->getPredecessor()->getInsertHereElement(null);
		}
		$e = new DivElement(ALLOCATION_MODE_LAZY);
		$e->setIdAttribute("insert_search_results_here");
		$e->setAllowEmptyInnerHTML(true);
		return $e;
	}

	public function getPageContentGenerator(): UseCase{
		if ($this->hasPredecessor()) {
			return $this->getPredecessor();
		}
		return $this;
	}

	public function getResponder(int $status): ?Responder{
		if (request()->getProgressiveHyperlinkFlag()) {
			return new ProgressiveHyperlinkResponder();
		}
		return new SearchResponder();
	}

	public static function isSearchEvent():bool{
		return directive() === DIRECTIVE_SEARCH;
	}

	public function getClientUseCaseName(): ?string{
		$f = __METHOD__;
		if ($this->hasPredecessor()) {
			$predecessor = $this->getPredecessor();
			if (! $predecessor instanceof ClientUseCaseInterface) {
				Debug::error("{$f} predecessor is not a client compatible use case");
			}
			return $predecessor->getClientUseCaseName();
		}
		return "search";
	}
}
