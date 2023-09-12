<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\error\ErrorMessage;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class ElementExistsCommand extends ElementCommand implements ValueReturningCommandInterface
{

	public static function getCommandId(): string
	{
		return "elementExists";
	}

	public function toJavaScript(): string
	{
		$idcs = $this->getIdCommandString();
		if($idcs instanceof JavaScriptInterface) {
			$idcs = $idcs->toJavaScript();
		}elseif(is_string($idcs) || $idcs instanceof StringifiableInterface) {
			$idcs = single_quote($idcs);
		}
		return "elementExists({$idcs})";
	}

	public function evaluate(?array $params = null)
	{
		$f = __METHOD__; //ElementExistsCommand::getShortClass()."(".static::getShortClass().")->evaluate()";
		ErrorMessage::unimplemented($f);
	}
}
