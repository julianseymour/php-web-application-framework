<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\observer;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\variable\DeclareVariableCommand;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class IntersectionObserverCommand extends ObserverCommand
{

	protected $threshold;

	protected $rootId;

	protected $rootMargin;

	public function hasThreshold()
	{
		return isset($this->threshold);
	}

	public function getThreshold()
	{
		return $this->hasThreshold() ? $this->threshold : 0;
	}

	public function setThreshold($threshold)
	{
		return $this->threshold = $threshold;
	}

	public function hasRootId()
	{
		return isset($this->rootId);
	}

	public function getRootId()
	{
		return $this->rootId;
	}

	public function setRootId($rootId)
	{
		return $this->rootId = $rootId;
	}

	public function hasRootMargin()
	{
		return isset($this->rootMargin);
	}

	public function getRootMargin()
	{
		return $this->hasRootMargin() ? $this->rootMargin : 0;
	}

	public function setRootMargin($rootMargin)
	{
		return $this->rootMargin = $rootMargin;
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		Json::echoKeyValuePair('threshold', $this->getThreshold(), $destroy);
		if ($this->hasRootId()) {
			Json::echoKeyValuePair('rootId', $this->getRootId(), $destroy);
		}
		Json::echoKeyValuePair('rootMargin', $this->getRootMargin(), $destroy);
		parent::echoInnerJson($destroy);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->rootId);
		unset($this->rootMargin);
		unset($this->threshold);
	}

	public static function getCommandId(): string
	{
		return "IntersectionObserver";
	}

	public function getParameterString()
	{
		return parent::getParameterString() . ", options";
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //IntersectionObserverCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		try {
			$string = "";
			$threshold = $this->getThreshold();
			if ($threshold instanceof JavaScriptInterface) {
				$threshold = $threshold->toJavaScript();
			}
			$root_margin = $this->getRootMargin();
			if ($root_margin instanceof JavaScriptInterface) {
				$root_margin = $root_margin->toJavaScript();
			}
			$options = [
				'threshold' => $threshold,
				'rootMargin' => $root_margin
			];
			if ($this->hasRootId()) {
				$root_id = $this->getRootId();
				if ($root_id instanceof JavaScriptInterface) {
					$root_id = $root_id->toJavaScript();
				}
				$options['rootId'] = $root_id;
			}
			$options_declared = DeclareVariableCommand::let("options", $options);
			$options_declared->setEscapeType(ESCAPE_TYPE_OBJECT);
			$string .= $options_declared->toJavaScript() . ";\n";
			return $string . parent::toJavaScript();
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
