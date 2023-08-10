<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\control;

use JulianSeymour\PHPWebApplicationFramework\common\DisposableInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptFunction;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

class PromiseSignature implements DisposableInterface, JavaScriptInterface
{

	protected $fulfillmentHandler;

	protected $rejectionHandler;

	public function __construct(JavaScriptFunction $fulfilled, ?JavaScriptFunction $rejected = null)
	{
		$this->setFulfillmentHandler($fulfilled);
		if ($rejected instanceof JavaScriptFunction) {
			$this->setRejectionHandler($rejected);
		}
	}

	public function setFulfillmentHandler(?JavaScriptFunction $fulfilled): ?JavaScriptFunction
	{
		$f = __METHOD__; //PromiseSignature::getShortClass()."(".static::getShortClass().")->setFulfillmentHandler()";
		if ($fulfilled === null) {
			unset($this->fulfillmentHandler);
			return null;
		} elseif (! $fulfilled instanceof JavaScriptFunction) {
			Debug::error("{$f} fulfillment handler must be an instanceof JavaScriptFunction");
		}
		return $this->fulfillmentHandler = $fulfilled;
	}

	public function hasFulfillmentHandler()
	{
		return isset($this->fulfillmentHandler) && $this->fulfillmentHandler instanceof JavaScriptFunction;
	}

	public function getFulfillmentHandler()
	{
		$f = __METHOD__; //PromiseSignature::getShortClass()."(".static::getShortClass().")->getFulfillmentHandler()";
		if (! $this->hasFulfillmentHandler()) {
			Debug::error("{$f} fulfillment handler is undefined");
		}
		return $this->fulfillmentHandler;
	}

	public function setRejectionHandler(?JavaScriptFunction $fulfilled): ?JavaScriptFunction
	{
		$f = __METHOD__; //PromiseSignature::getShortClass()."(".static::getShortClass().")->setRejectionHandler()";
		if ($fulfilled === null) {
			unset($this->fulfillmentHandler);
			return null;
		} elseif (! $fulfilled instanceof JavaScriptFunction) {
			Debug::error("{$f} fulfillment handler must be an instanceof JavaScriptFunction");
		}
		return $this->rejectionHandler = $fulfilled;
	}

	public function hasRejectionHandler()
	{
		return isset($this->rejectionHandler) && $this->rejectionHandler instanceof JavaScriptFunction;
	}

	public function getRejectionHandler()
	{
		$f = __METHOD__; //PromiseSignature::getShortClass()."(".static::getShortClass().")->getRejectionHandler()";
		if (! $this->hasRejectionHandler()) {
			Debug::error("{$f} rejection handler is undefined");
		}
		return $this->rejectionHandler;
	}

	function toJavaScript(): string
	{
		$string = $this->getFulfillmentHandler()->toJavaScript();
		if ($this->hasRejectionHandler()) {
			$string .= ", " . $this->getRejectionHandler()->toJavaScript();
		}
		return $string;
	}

	public function dispose(): void
	{
		unset($this->fulfillmentHandler);
		unset($this->rejectionHandler);
	}
}
