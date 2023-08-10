<?php

namespace JulianSeymour\PHPWebApplicationFramework\ui;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\style\StyleSheetPathTrait;

class CloseMenuLabel extends LabelElement
{

	use StyleSheetPathTrait;
	
	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		$f = __METHOD__; //CloseMenuLabel::getShortClass()."(".static::getShortClass().")->__construct()";
		parent::__construct($mode, $context);
		$this->addClassAttribute("close_menu_label");
	}

	public function generateChildNodes(): ?array
	{
		$f = __METHOD__; //CloseMenuLabel::getShortClass()."(".static::getShortClass().")->generateChildNodes()";
		try {
			$close_menu_label = new DivElement();
			$close_menu_label->addClassAttribute("hamburger");
			$div = new DivElement();
			$messenger_fake_line1 = new DivElement();
			$messenger_fake_line1->addClassAttribute("messenger_fake_line1");
			$messenger_fake_line1->setAllowEmptyInnerHTML(true);
			$div->appendChild($messenger_fake_line1);
			$messenger_fake_line2 = new DivElement();
			$messenger_fake_line2->addClassAttribute("messenger_fake_line2");
			$messenger_fake_line2->setAllowEmptyInnerHTML(true);
			$div->appendChild($messenger_fake_line2);
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