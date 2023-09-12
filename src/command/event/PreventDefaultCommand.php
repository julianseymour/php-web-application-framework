<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\event;

use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\variable\VariableNameTrait;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class PreventDefaultCommand extends Command implements JavaScriptInterface
{

	use VariableNameTrait;

	public function __construct($vn = null)
	{
		if($vn !== null) {
			$this->setVariableName($vn);
		}
	}

	public static function getCommandId(): string
	{
		return "preventDefault";
	}

	public function toJavaScript(): string
	{
		$name = $this->getVariableName();
		if($name instanceof JavaScriptInterface) {
			$name = $name->toJavaScript();
		}
		return "{$name}." . $this->getCommandId() . "()";
	}

	public function getVariableName()
	{
		if(!$this->hasVariableName()) {
			return "event";
		}
		return $this->variableName;
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->variableName);
	}
}
