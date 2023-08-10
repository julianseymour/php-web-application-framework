<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class GetInnerHTMLCommand extends ElementCommand implements ValueReturningCommandInterface
{

	public static function getCommandId(): string
	{
		return "getInnerHTML";
	}

	public function evaluate(?array $params = null)
	{
		return $this->getElement()->getInnerHTML();
	}

	public function toJavaScript(): string
	{
		$idcs = $this->getIdCommandString();
		if ($idcs instanceof JavaScriptInterface) {
			$idcs = $idcs->toJavaScript();
		}
		return "{$idcs}.innerHTML";
	}
}
