<?php

namespace JulianSeymour\PHPWebApplicationFramework\search;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\form\AjaxForm;
use JulianSeymour\PHPWebApplicationFramework\input\CheckboxInput;
use JulianSeymour\PHPWebApplicationFramework\input\CheckedInput;
use JulianSeymour\PHPWebApplicationFramework\input\FancyCheckbox;

class SearchFieldsForm extends AjaxForm{

	public static function getFormDispatchIdStatic(): ?string{
		return "search_fields";
	}

	public function generateButtons(string $name): ?array{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	/**
	 *
	 * @param SearchFieldsData $context
	 */
	public function getFormDataIndices(): ?array{
		$f = __METHOD__;
		$context = $this->getContext();
		$indices = [];
		// Debug::print("{$f} about to call getSearchableColumns()");
		foreach($context->getFilteredColumns("!".COLUMN_FILTER_VIRTUAL) as $c){
			$cn = $c->getName();
			if($cn === "searchQueryKey"){
				continue;
			}
			$indices[$cn] = $cn;
		}
		if(count($indices) === 1){
			foreach($indices as $cn){
				$indices[$cn] = CheckboxInput::class;
			}
		}else{
			foreach($indices as $cn){
				$indices[$cn] = FancyCheckbox::class;
			}
		}
		if(empty($indices)){
			Debug::warning("{$f} indices array is empty");
		}
		return $indices;
	}

	public function getDirectives(): ?array{
		$f = __METHOD__;
		ErrorMessage::unimplemented($f);
	}

	public static function getActionAttributeStatic(): ?string{
		return null;
	}

	public function reconfigureInput($input): int{
		$f = __METHOD__;
		$print = false;
		$ret = parent::reconfigureInput($input);
		// $context = $this->getContext();
		if($input instanceof CheckedInput){
			$input->setCheckedAttribute("checked");
		}
		$count = count($this->getFormDataIndices());
		if($count === 1){
			if($print){
				Debug::print("{$f} there is only one column worth searching; hiding its checkbox from view");
			}
			$input->addClassAttribute("hidden");
			$input->setHiddenAttribute("hidden");
		}elseif(!$input->hasWrapperElement()){
			if($print){
				Debug::print("{$f} column count is {$count}");
			}
			$div = new DivElement($this->getAllocationMode());
			$input->setWrapperElement($div);
		}
		return $ret;
	}
}
