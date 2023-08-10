<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

abstract class EmptyElement extends Element
{

	public static function isEmptyElement(): bool
	{
		return true;
	}
}
