<?php
namespace JulianSeymour\PHPWebApplicationFramework\security\xsrf;

use function JulianSeymour\PHPWebApplicationFramework\str_contains;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\UriTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class SecondaryHmacCommand extends Command implements JavaScriptInterface, ValueReturningCommandInterface
{

	use UriTrait;

	// protected $action;
	public static function getCommandId(): string
	{
		return "secondary_hmac";
	}

	public function __construct($uri)
	{
		$f = __METHOD__; //SecondaryHmacCommand::getShortClass()."(".static::getShortClass().")->__construct()";
		$print = false;
		parent::__construct();
		$this->setUri($uri);
	}

	public function setUri($uri)
	{
		$f = __METHOD__; //SecondaryHmacCommand::getShortClass()."(".static::getShortClass().")->setUri()";
		$print = false;
		if ($uri == null) {
			unset($this->uri);
			return null;
		} elseif (str_contains($uri, "?")) {
			$uri = explode("?", $uri)[0];
			if ($print) {
				Debug::print("{$f} split string at ? to make new URI \"{$uri}\"");
			}
		}
		return $this->uri = $uri;
	}

	public function evaluate(?array $params = null)
	{
		$f = __METHOD__; //SecondaryHmacCommand::getShortClass()."(".static::getShortClass().")->evaluate()";
		$session = new AntiXsrfTokenData();
		if (! $session->hasAntiXsrfToken()) {
			Debug::error("{$f} session is uninitialized");
			$session->initializeSessionToken(1);
		}
		return $session->getSecondaryHmac($this->getUri());
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		$f = __METHOD__; //SecondaryHmacCommand::getShortClass()."(".static::getShortClass().")->echoInnerJson()";
		Json::echoKeyValuePair('action', $this->getUri(), $destroy);
		parent::echoInnerJson($destroy);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->uri);
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //SecondaryHmacCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		try {
			$action = $this->getUri();
			if ($action instanceof JavaScriptInterface) {
				$action = $action->toJavaScript();
			}
			return "getSecondaryHmac('{$action}')";
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
