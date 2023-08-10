<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

use JulianSeymour\PHPWebApplicationFramework\element\attributes\ForAttributeInterface;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\ForAttributeTrait;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\NameAttributeTrait;

class OutputElement extends Element implements ForAttributeInterface
{

	use ForAttributeTrait;
	use NameAttributeTrait;

	public static function getElementTagStatic(): string
	{
		return "output";
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"useFormAttribute"
		]);
	}

	/*
	 * protected function getTemplateFunctionAttributeCommands(){
	 * $commands = parent::getTemplateFunctionAttributeCommands();
	 * if($this->hasForAttribute()){
	 * $refor = new SetLabelHTMLForCommand($this, $this->getForAttribute());
	 * array_push($commands, $refor);
	 * }
	 * return $commands;
	 * }
	 */
}
