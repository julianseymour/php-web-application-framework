<?php
namespace JulianSeymour\PHPWebApplicationFramework\ui;

use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\element\LabelElement;

class ThreeDotOptionsLabel extends LabelElement
{

	public static function getDots()
	{
		$dot1 = new DivElement();
		$dot1->addClassAttribute("dot");
		$dot1->addClassAttribute("background_color_6");
		$dot1->setAllowEmptyInnerHTML(true);
		$dot2 = new DivElement();
		$dot2->addClassAttribute("dot");
		$dot2->addClassAttribute("background_color_6");
		$dot2->setAllowEmptyInnerHTML(true);
		$dot3 = new DivElement();
		$dot3->addClassAttribute("dot");
		$dot3->addClassAttribute("background_color_6");
		$dot3->setAllowEmptyInnerHTML(true);
		return [
			$dot1,
			$dot2,
			$dot3
		];
	}

	public function generateChildNodes(): ?array
	{
		$dots = static::getDots();
		$this->appendChild(...$dots);
		return $dots;
	}
}
