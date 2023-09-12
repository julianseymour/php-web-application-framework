<?php
namespace JulianSeymour\PHPWebApplicationFramework\command;

use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\common\UriTrait;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

/**
 * Update the client's window URL
 *
 * @author j
 */
class PushStateCommand extends Command implements JavaScriptInterface
{

	use UriTrait;

	public function __construct($uri)
	{
		parent::__construct();
		$this->setUri($uri);
	}

	public static function getCommandId(): string
	{
		return "pushState";
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		Json::echoKeyValuePair('uri', $this->getUri(), $destroy);
		parent::echoInnerJson($destroy);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->uri);
	}

	public function toJavaScript(): string
	{
		$uri = $this->getUri();
		if($uri instanceof JavaScriptInterface) {
			$uri = $uri->toJavaScript();
		}elseif(is_string($uri) || $uri instanceof StringifiableInterface) {
			$q = $this->getQuoteStyle();
			$uri = "{$q}" . escape_quotes($uri, $q) . "{$q}";
		}
		return "window.history.pushState(null, null, {$uri});";
	}
}
