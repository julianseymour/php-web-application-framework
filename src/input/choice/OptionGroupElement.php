<?php

namespace JulianSeymour\PHPWebApplicationFramework\input\choice;

use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\LabelAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\input\DisabledAttributeTrait;

class OptionGroupElement extends Element{

	use DisabledAttributeTrait;
	use LabelAttributeTrait;

	public static function getElementTagStatic(): string{
		return "optgroup";
	}
}
