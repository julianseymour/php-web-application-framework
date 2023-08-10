<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\input;

use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class ClearInputCommand extends ElementCommand implements ServerExecutableCommandInterface
{

	public static function getCommandId(): string
	{
		return "clearInput";
	}

	public function resolve()
	{
		$element = $this->getElement();
		while ($element instanceof ValueReturningCommandInterface) {
			$element = $element->evaluate();
		}
		$element->setValueAttribute(null);
	}

	public function toJavaScript(): string
	{
		$idcs = $this->getIdCommandString();
		if ($idcs instanceof JavaScriptInterface) {
			$idcs = $idcs->toJavaScript();
		}
		return "{$idcs}.value = null;";
	}
}
