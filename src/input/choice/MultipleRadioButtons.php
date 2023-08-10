<?php
namespace JulianSeymour\PHPWebApplicationFramework\input\choice;

use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\input\RadioButtonInput;

class MultipleRadioButtons extends MultipleChoiceInput implements StaticElementClassInterface
{

	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string
	{
		return RadioButtonInput::class;
	}
}
