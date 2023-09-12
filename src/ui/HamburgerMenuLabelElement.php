<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\style\StyleSheetPathTrait;

class HamburgerMenuLabelElement extends LabelElement
{

	use StyleSheetPathTrait;
	
	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		parent::__construct($mode, $context);
		$this->addClassAttribute("hamburger");
	}

	public function generateChildNodes(): ?array
	{
		$f = __METHOD__; //HamburgerMenuLabelElement::getShortClass()."(".static::getShortClass().")->generateChildNodes()";
		try{
			/*
			 * $icon = new DivElement();
			 * $icon->addClassAttribute("close_menu_label");
			 * $icon->setIdAttribute("close_menu_label");
			 */
			$div = new DivElement();
			$m1 = new DivElement();
			$m1->addClassAttribute("morph_icon", "background_color_6");
			// $m1->addClassAttribute("services_icon_morph_1");
			$m1->setAllowEmptyInnerHTML(true);
			$m2 = new DivElement();
			$m2->addClassAttribute("morph_icon", "background_color_6");
			// $m2->addClassAttribute("services_icon_morph_2");
			$m2->setAllowEmptyInnerHTML(true);
			// $div->appendChild($m2);
			$m3 = new DivElement();
			$m3->addClassAttribute("morph_icon", "background_color_6");
			// $m3->addClassAttribute("services_icon_morph_3");
			$m3->setAllowEmptyInnerHTML(true);
			// $div->appendChild($m3);
			$m4 = new DivElement();
			$m4->addClassAttribute("morph_icon", "background_color_6");
			// $m4->addClassAttribute("services_icon_morph_4");
			$m4->setAllowEmptyInnerHTML(true);
			// $div->appendChild($m4);
			$m5 = new DivElement();
			$m5->addClassAttribute("morph_icon", "background_color_6");
			// $m5->addClassAttribute("services_icon_morph_5");
			$m5->setAllowEmptyInnerHTML(true);
			// $div->appendChild($m5);
			$div->appendChild($m1, $m2, $m3, $m4, $m5);
			// $icon->appendChild($div);
			$this->appendChild($div); // icon);
			return $this->getChildNodes();
		}catch(Exception $x) {
			x($f, $x);
		}
	}
}
