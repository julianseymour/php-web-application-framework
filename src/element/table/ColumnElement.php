<?php
namespace JulianSeymour\PHPWebApplicationFramework\element\table;

use JulianSeymour\PHPWebApplicationFramework\element\EmptyElement;

class ColumnElement extends EmptyElement
{

	use SpanAttributeTrait;

	public static function getElementTagStatic(): string
	{
		return "col";
	}
}