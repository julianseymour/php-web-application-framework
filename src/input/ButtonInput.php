<?php
namespace JulianSeymour\PHPWebApplicationFramework\input;

class ButtonInput extends ButtonlikeInput
{

	/*
	 * public function __construct(int $mode=ALLOCATION_MODE_UNDEFINED, $context=null){
	 * parent::__construct($mode, $context);
	 * $this->setAttribute("declared", $this->getDeclarationLine());
	 * }
	 */
	public static function getElementTagStatic(): string
	{
		return "button";
	}

	public static function getTypeAttributeStatic(): string
	{
		return INPUT_TYPE_BUTTON;
	}

	public static function isEmptyElement(): bool
	{
		return false;
	}
}
