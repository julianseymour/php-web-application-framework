<?php
namespace JulianSeymour\PHPWebApplicationFramework\image;

use JulianSeymour\PHPWebApplicationFramework\element\CompoundElement;
use JulianSeymour\PHPWebApplicationFramework\element\inline\HypertextAttributeTrait;

abstract class CompoundImageElement extends CompoundElement
{

	use AlternateTextAttributeTrait;
	use HypertextAttributeTrait;
}

