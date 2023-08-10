<?php
namespace JulianSeymour\PHPWebApplicationFramework\input\choice;

use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;

class MultipleOptions extends MultipleChoiceInput implements StaticElementClassInterface
{

	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string
	{
		return OptionElement::class;
	}
}
