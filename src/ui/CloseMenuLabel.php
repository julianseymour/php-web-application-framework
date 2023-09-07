<?php

namespace JulianSeymour\PHPWebApplicationFramework\ui;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\style\StyleSheetPathTrait;

class CloseMenuLabel extends LabelElement{
	
	use StyleSheetPathTrait;
		
	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null){
		parent::__construct($mode, $context);
		$this->addClassAttribute("close_menu_label");
	}

	public function hasLineClassAttributes():bool{
		return $this->hasArrayProperty('lineClassAttributes');
	}
	
	public function setLineClassAttributes($values):?array{
		return $this->setArrayProperty('lineClassAttributes', $values);
	}
	
	public function getLineClassAttributes(){
		return $this->getProperty("lineClassAttributes");
	}
	
	public function generateChildNodes(): ?array{
		$f = __METHOD__;
		try {
			$close_menu_label = new DivElement();
			$close_menu_label->addClassAttribute("hamburger");
			$div = new DivElement();
			$close_menu_line1 = new DivElement();
			$close_menu_line1->addClassAttribute("close_menu_line1");
			$close_menu_line1->setAllowEmptyInnerHTML(true);
			$div->appendChild($close_menu_line1);
			$close_menu_line2 = new DivElement();
			$close_menu_line2->addClassAttribute("close_menu_line2");
			if($this->hasLineClassAttributes()){
				$close_menu_line1->addClassAttribute(...$this->getLineClassAttributes());
				$close_menu_line2->addClassAttribute(...$this->getLineClassAttributes());
			}
			$close_menu_line2->setAllowEmptyInnerHTML(true);
			$div->appendChild($close_menu_line2);
			$close_menu_label->appendChild($div);
			$this->appendChild($close_menu_label);
			return [
				$close_menu_label
			];
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}