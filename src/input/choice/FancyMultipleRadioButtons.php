<?php
namespace JulianSeymour\PHPWebApplicationFramework\input\choice;

use JulianSeymour\PHPWebApplicationFramework\common\StaticElementClassInterface;
use JulianSeymour\PHPWebApplicationFramework\element\DivElement;
use JulianSeymour\PHPWebApplicationFramework\input\FancyRadioButton;

class FancyMultipleRadioButtons extends MultipleRadioButtons
{

	public function __construct(int $mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		parent::__construct($mode, $context);
		$this->setAutoLabelFlag(true);
	}

	public static function getElementClassStatic(?StaticElementClassInterface $that = null): string
	{
		return FancyRadioButton::class;
	}

	public function hasIndividualWrapperClass(): bool
	{
		return true;
	}

	public function getIndividualWrapperClass(): string
	{
		return DivElement::class;
	}
}
