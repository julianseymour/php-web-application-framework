<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\table;

use JulianSeymour\PHPWebApplicationFramework\element\Element;

class ColumnGroupElement extends Element
{

	use SpanAttributeTrait;

	public static function getElementTagStatic(): string
	{
		return "colgroup";
	}
}
