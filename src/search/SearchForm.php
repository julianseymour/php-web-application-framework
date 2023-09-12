<?php

namespace JulianSeymour\PHPWebApplicationFramework\search;

use function JulianSeymour\PHPWebApplicationFramework\app;
use function JulianSeymour\PHPWebApplicationFramework\get_short_class;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\str\ConcatenateCommand;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\datum\BooleanDatum;
use JulianSeymour\PHPWebApplicationFramework\datum\foreign\ForeignKeyDatum;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use JulianSeymour\PHPWebApplicationFramework\element\inline\SpanElement;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\DateIntervalInput;
use JulianSeymour\PHPWebApplicationFramework\input\FancyCheckbox;
use JulianSeymour\PHPWebApplicationFramework\input\ToggleInput;
use JulianSeymour\PHPWebApplicationFramework\input\choice\Choice;
use JulianSeymour\PHPWebApplicationFramework\ui\ExpanderElement;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;
use Exception;

class SearchForm extends AjaxForm{

	public static function getFormDispatchIdStatic(): ?string{
		return "search";
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		switch ($name) {
			case DIRECTIVE_SEARCH:
				$button = $this->generateGenericButton($name);
				$button->addClassAttribute("hidden");
				return [
					$button
				];
			default:
				Debug::error("{$f} invalid button name \"{$name}\"");
		}
	}

	public static function getMethodAttributeStatic(): ?string{
		return HTTP_REQUEST_METHOD_GET;
	}

	public static function allowEmptySearchQuery(): bool{
		return false;
	}

	protected function beforeRenderHook(): int{
		$ret = parent::beforeRenderHook();
		$this->setAttribute("callback_success", $this->getSuccessCallback());
		return $ret;
	}

	public function setSuccessCallback(?string $cb): ?string{
		$this->setAttribute("callback_success", $cb);
		return parent::setSuccessCallback($cb);
	}

	public static function getNewFormOption(): bool{
		return true;
	}

	protected function getSearchOptionsElement(): ExpanderElement{
		$f = __METHOD__;
		$mode = $this->getAllocationMode();
		$subcontainer = new ExpanderElement($mode);
		// $subcontainer->setStyleProperties(["padding-left" => "1.25rem"]);
		$subcontainer->setElementTag("div");
		$subcontainer->setTriggerInputType(INPUT_TYPE_CHECKBOX);
		$advanced_options = _("Search options");
		$subcontainer->setExpandLabelString($advanced_options);
		$subcontainer->setCollapseLabelString(_("Close"));
		$etida = "check-advanced_" . $this->getFormDispatchId();
		$subcontainer->setExpandTriggerInputIdAttribute($etida);
		return $subcontainer;
	}

	public function generateChoices($input): ?array{
		$f = __METHOD__;
		$context = $input->getContext();
		$ds = $context->getDataStructure();
		$vn = $context->getName();
		switch ($vn) {
			case "orderBy":
				if($ds->getSearchClassCount() > 1) {
					Debug::error("{$f} this class is not setup to allow reordering of search results for multiple searchable classes");
				}
				$classes = $ds->getSearchClasses();
				$class = $classes[array_keys($classes)[0]];
				$fields = $ds->getForeignDataStructure("fields_".get_short_class($class));
				$options = [
					"" => new Choice("", _("Sort by"), ! $ds->hasOrderBy())
				];
				$searchable_class = $fields->getSearchClass();
				$dumdum = new $searchable_class();
				$orderby = $ds->hasOrderBy() ? $ds->getOrderBy() : null;
				foreach($dumdum->getFilteredColumns(COLUMN_FILTER_SORTABLE) as $ovn => $column) {
					$options[$ovn] = new Choice($ovn, $column->getHumanReadableName(), $orderby !== null && $orderby === $ovn);
				}
				unset($dumdum);
				return $options;
			case "orderDirection":
				return [
					DIRECTION_ASCENDING => new Choice(DIRECTION_ASCENDING, _("Ascending")),
					DIRECTION_DESCENDING => new Choice(DIRECTION_DESCENDING, _("Descending"))
				];
			default:
				return parent::generateChoices($input);
		}
	}
	
	public function getInternalFormElements(array $inputs): ?array{
		$mode = $this->getAllocationMode();
		$contents = [];
		$subcontainer = $this->getSearchOptionsElement();
		$context = $this->getContext();
		if($context->hasSearchableTimestamps()) {
			$searchable = $context->getSearchableTimestamps();
			$indices = array_keys($searchable);
			$mode = $this->getAllocationMode();
			foreach($indices as $index) {
				$input = new DateIntervalInput($mode, $searchable[$index]);
				$prefix = new SpanElement($mode);
				$prefix->setInnerHTML($searchable[$index]->getHumanReadableName());
				$input->pushPredecessor($prefix);
				$infracontainer = new SpanElement($mode);
				$infracontainer->setStyleProperty("border", "1px solid");
				$infracontainer->appendChild($input);
				// $subcontainer->appendChild($infracontainer);
				array_push($contents, $infracontainer);
			}
		}
		$ret = [];
		foreach($inputs as $name => $input) {
			if(is_array($input)) {
				array_push($contents, ...$this->getInternalFormElementsHelper($input));
			}elseif($input instanceof AjaxForm) {
				array_push($contents, ...$this->getInternalFormElementsHelper([
					$input
				]));
			}elseif(false === array_search($name, [
				"searchQuery"
			])) {
				array_push($contents, $input);
			}else{
				array_push($ret, $input);
			}
		}
		$subcontainer->setExpanderContents($contents);
		array_push($ret, $subcontainer);
		return $ret;
	}

	public function getFormDataIndices(): ?array{
		$f = __METHOD__;
		try{
			if(!$this->hasContext()) {
				Debug::error("{$f} context is undefined");
			}
			$context = $this->getContext();
			$indices = [
				"searchQuery" => SearchInput::class,
				"autoSearch" => ToggleInput::class,
				//"caseSensitive" => FancyCheckbox::class,
				//"searchMode" => FancyMultipleRadioButtons::class
			];
			$columns = $context->getColumns();
			foreach($columns as $c) {
				$vn = $c->getName();
				if($c->getFlag("paginator")){
					// Debug::print("{$f} index \"{$vn}\" points to a datum contributed by Paginator");
				}elseif(false !== array_search($vn, [
					"autoSearch",
					"caseSensitive",
					"searchMode"
				])){
					continue;
				}elseif(array_key_exists($vn, $indices)){
					// Debug::print("{$f} index \"{$vn}\" is already part of the array");
					continue;
				}elseif($c instanceof BooleanDatum) {
					$indices[$vn] = FancyCheckbox::class;
				}elseif($c instanceof ForeignKeyDatum) {
					$indices[$vn] = SearchFieldsForm::class;
				}else{
					Debug::error("{$f} index \"{$vn}\" is neither of the above");
				}
			}
			return $indices;
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function reconfigureInput($input): int{
		$f = __METHOD__;
		try{
			$mode = $this->getAllocationMode();
			$context = $this->getContext();
			if($input->hasContext() && $input->getContext() instanceof SearchFieldDatum){
				$input->setCheckedAttribute("checked");
				$div = new DivElement($mode);
				if($context->getSearchClassCount() === 1) {
					$div->addClassAttribute("hidden");
				}
				$input->setWrapperElement($div);
				if(app()->getFlag("debug")){
					$div->setAttribute("context_declared", $input->getContext()->getDeclarationLine());
				}
			}
			$vn = $input->getColumnName();
			switch ($vn) {
				case "autoSearch":
					$input->setIdAttribute(new ConcatenateCommand("autosearch-", $this->getIdAttribute()));
					$input->check();
					break;
				case "limitPerPage":
					$wrapper = new DivElement($mode);
					$input->setWrapperElement($wrapper);
					break;
				case "orderDirection":
					$input->setChoices([
						DIRECTION_ASCENDING => new Choice(DIRECTION_ASCENDING, _("Ascending"), true),
						DIRECTION_DESCENDING => new Choice(DIRECTION_DESCENDING, _("Descending"))
					]);
					return SUCCESS;
				case "searchMode":
					$input->setChoices([
						SEARCH_MODE_ANY => new Choice(SEARCH_MODE_ANY, _("Any terms"), true),
						SEARCH_MODE_ALL => new Choice(SEARCH_MODE_ALL, _("All terms")),
						SEARCH_MODE_EXACT => new Choice(SEARCH_MODE_EXACT, _("Exact phrase"))
					]);
					break;
				case "searchQuery":
					$input->setLabelString(_("Enter search query"));
					$ret = parent::reconfigureInput($input);
					$input->setRequiredAttribute("required");
					$input->setStyleProperties([
						"height" => "50px",
						"border-radius" => "2rem",
						"margin-left" => "15px"
					]);
					$input->setOnKeyUpAttribute("instantSearch(event, this);");
					$search_label = new LabelElement($mode);
					$search_label->setInnerHTML(_("Search"));
					$search_label->addClassAttribute("button-like");
					$btn_id = new ConcatenateCommand("search-", $this->getIdAttribute());
					$search_label->setForAttribute($btn_id);
					$search_label->setStyleProperties([
						"padding" => "0.5rem"
					]);
					$input->pushSuccessor($search_label);
					$input->setStyleProperty("width", "calc(100% - 128px)");
					return $ret;
				default:
			}
			return parent::reconfigureInput($input);
		}catch(Exception $x) {
			x($f, $x);
		}
	}

	public function getDirectives(): ?array{
		return [
			DIRECTIVE_SEARCH
		];
	}

	public static function getActionAttributeStatic(): ?string{
		return "/search";
	}

	protected function getSubordinateFormClass(string $index): ?string{
		return SearchFieldsForm::class;
	}

	public static function getSearchClasses(){
		$f = __METHOD__;
		Debug::error("{$f} override this function in the derived class");
	}

	/**
	 * Shortcut to generate a search form of this class.
	 *
	 * @param int $mode
	 * @param UseCase $use_case
	 * @return SearchForm
	 */
	public static function createSearchFormStatic(int $mode = ALLOCATION_MODE_UNDEFINED): SearchForm{
		$f = __METHOD__;
		$paginator = new SearchPaginator();
		$paginator->setSearchClasses(static::getSearchClasses());
		$form_class = static::class;
		$form = new $form_class($mode, $paginator);
		$form->setActionAttribute(static::getActionAttributeStatic());
		$form->setSuccessCallback(static::getSuccessCallbackStatic());
		return $form;
	}

	/**
	 * ID attribute of the label the user clicks to clear search results
	 *
	 * @return string
	 */
	public static function getClearResultsLabelId(): string{
		$f = __METHOD__;
		Debug::error("{$f} redeclare this in derived classes");
		return CONST_ERROR;
	}

	/**
	 * ID attribute of the query string input
	 *
	 * @return string
	 */
	public static function getSearchQueryInputId(): string{
		$f = __METHOD__;
		Debug::error("{$f} redeclare this in derived classes");
		return CONST_ERROR;
	}

	/**
	 * ID attribute of the element containing the search results
	 *
	 * @return string
	 */
	public static function getSearchResultsContainerId(): string{
		$f = __METHOD__;
		Debug::error("{$f} redeclare this in derived classes");
		return CONST_ERROR;
	}

	/**
	 * ID attribute of the radio button that closes the results container when it's checked
	 *
	 * @return string
	 */
	public static function getCloseResultsInputId(): string{
		$f = __METHOD__;
		Debug::error("{$f} redeclare this in derived classes");
		return CONST_ERROR;
	}

	protected function getClearSearchResultsButton(): LabelElement{
		$mode = $this->getAllocationMode();
		$delete = new LabelElement($mode);
		$delete->setInnerHTML(_("Clear"));
		$delete->addClassAttribute("clear_search_btn");
		$clearButtonId = $this->getClearResultsLabelId();
		$queryInputId = $this->getSearchQueryInputId();
		$searchResultsId = $this->getSearchResultsContainerId();
		$checkInputId = $this->getCloseResultsInputId();
		$delete->setOnClickAttribute("closeSearchResults('{$clearButtonId}', '{$queryInputId}', '{$searchResultsId}', '{$checkInputId}')");
		$delete->setIdAttribute($clearButtonId);
		$delete->setStyleProperties([
			"opacity" => "0",
			"transition" => "opacity 0.25s",
			"padding" => "0.5rem"
		]);
		$delete->addClassAttribute("button-like");
		return $delete;
	}
}
