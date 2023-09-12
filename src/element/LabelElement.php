<?php
namespace JulianSeymour\PHPWebApplicationFramework\element;

use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\ForAttributeInterface;
use JulianSeymour\PHPWebApplicationFramework\element\attributes\ForAttributeTrait;

class LabelElement extends Element implements ForAttributeInterface
{

	use ForAttributeTrait;

	protected $inputElement;

	public function __construct($mode = ALLOCATION_MODE_UNDEFINED, $context = null)
	{
		parent::__construct($mode, $context);
		$this->setIgnoreForAttribute(false);
	}

	public function dispose(): void
	{
		$f = __METHOD__; //LabelElement::getShortClass()."(".static::getShortClass().")->dispose()";
		$print = false;
		if($print) {
			Debug::printStackTraceNoExit("{$f} entered");
		}
		parent::dispose();
		unset($this->inputElement);
	}

	public function setInputElement($input)
	{
		return $this->inputElement = $input;
	}

	public function hasInputElement()
	{
		return isset($this->inputElement);
	}

	public function getInputElement()
	{
		return $this->inputElement;
	}

	public static function declareFlags(): ?array
	{
		return array_merge(parent::declareFlags(), [
			"ignoreForAttribute"
		]);
	}

	public static function getElementTagStatic(): string
	{
		return "label";
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
