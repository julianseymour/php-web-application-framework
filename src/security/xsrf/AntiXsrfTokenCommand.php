<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\xsrf;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class AntiXsrfTokenCommand extends Command implements JavaScriptInterface, ValueReturningCommandInterface
{

	public static function getCommandId(): string
	{
		return "xsrf_token";
	}

	public function evaluate(?array $params = null)
	{
		$f = __METHOD__; //AntiXsrfTokenCommand::getShortClass()."(".static::getShortClass().")->evaluate()";
		try {
			$session = new AntiXsrfTokenData();
			if (! $session->hasAntiXsrfToken()) {
				Debug::error("{$f} XSRF token was undefined in SESSION");
				// $session->initializeSessionToken(1);
			}
			return $session->getAntiXsrfToken();
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function toJavaScript(): string
	{
		return "getAntiXsrfToken()";
	}
}
