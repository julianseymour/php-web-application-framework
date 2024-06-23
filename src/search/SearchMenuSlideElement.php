<?php

namespace JulianSeymour\PHPWebApplicationFramework\search;

use JulianSeymour\PHPWebApplicationFramework\ui\MenuSlideElement;

class SearchMenuSlideElement extends MenuSlideElement{

	public function getSlideSelectInput(){
		$input = parent::getSlideSelectInput();
		$input->setIdAttribute("radio_menu-search");
		return $input;
	}

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->setStyleProperty("margin-top", "50px");
		$this->setIdAttribute("menu_search_results");
		$this->setAllowEmptyInnerHTML(true);
	}
}
