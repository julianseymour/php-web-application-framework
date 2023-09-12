<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\input;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\element\ElementCommand;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class IsInputCheckedCommand extends ElementCommand implements ValueReturningCommandInterface
{

	public static function getCommandId(): string
	{
		return "isChecked";
	}

	public function toJavaScript(): string
	{
		$idcs = $this->getIdCommandString();
		if($idcs instanceof JavaScriptInterface) {
			$idcs = $idcs->toJavaScript();
		}
		return "{$idcs}.checked";
	}

	public function evaluate(?array $params = null)
	{
		$element = $this->getElement();
		while ($element instanceof ValueReturningCommandInterface) {
			$element->evaluate();
		}
		return $element->isChecked();
	}
}
