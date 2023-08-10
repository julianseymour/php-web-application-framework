<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class IsScrolledIntoViewCommand extends ElementCommand implements ValueReturningCommandInterface
{

	public static function getCommandId(): string
	{
		return "isScrolledIntoView";
	}

	public function toJavaScript(): string
	{
		$idcs = $this->getIdCommandString();
		if ($idcs instanceof JavaScriptInterface) {
			$idcs = $idcs->toJavaScript();
		}
		return "isScrolledIntoView({$idcs})";
	}

	public function evaluate(?array $params = null)
	{
		$f = __METHOD__; //IsScrolledIntoViewCommand::getShortClass()."(".static::getShortClass().")->evaluate()";
		ErrorMessage::unimplemented($f);
	}
}
