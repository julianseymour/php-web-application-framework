<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\media\math;

use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\element\inline\HypertextAttributeTrait;

// XXX there are dozens of MathML elements:
// https://developer.mozilla.org/en-US/docs/Web/MathML/Element
class MathElement extends Element
{

	use HypertextAttributeTrait;

	// XXX attributes: mathbackground, mathcolor, display, overflow
	public static function getElemenTag()
	{
		return "math";
	}
}
