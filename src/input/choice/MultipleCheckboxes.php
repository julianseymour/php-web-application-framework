<?php
namespace JulianSeymour\PHPWebApplicationFramework\input\choice;

use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\input\CheckboxInput;

class MultipleCheckboxes extends MultipleChoiceInput implements StaticElementClassInterface
{

	public function __construct(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		parent::__construct($mode, $context);
		$this->setAutoLabelFlag(true);
	}

	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string
	{
		return CheckboxInput::class;
	}
}
