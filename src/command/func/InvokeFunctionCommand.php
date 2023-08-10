<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\func;

use function JulianSeymour\PHPWebApplicationFramework\escape_quotes;
use JulianSeymour\PHPWebApplicationFramework\command\Command;
use JulianSeymour\PHPWebApplicationFramework\common\NamedTrait;
use JulianSeymour\PHPWebApplicationFramework\common\ParametricTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

abstract class InvokeFunctionCommand extends Command implements JavaScriptInterface
{

	use NamedTrait;
	use ParametricTrait;

	public function __construct($name, ...$params)
	{
		$f = __METHOD__; //InvokeFunctionCommand::getShortClass()."(".static::getShortClass().")->__construct()";
		$print = false;
		parent::__construct();
		$this->setName($name);
		if (! empty($params)) {
			if ($print) {
				$count = count($params);
				Debug::print("{$f} {$count} params");
			}
			$this->setParameters($params);
		}
	}

	public function echoJson(bool $destroy = false): void
	{
		if ($this->getEscapeType() == ESCAPE_TYPE_STRING || $this->getEscapeType() == ESCAPE_TYPE_FUNCTION) {
			Json::echo($this->toJavaScript());
		} else {
			parent::echoJson($destroy);
		}
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		Json::echoKeyValuePair('name', $this->getName(), $destroy);
		if ($this->hasParameters()) {
			$param_array = [];
			foreach ($this->getParameters() as $param) {
				if ($param instanceof Element) {
					$param = $param->getIdOverride();
				}
				array_push($param_array, $param);
			}
			Json::echoKeyValuePair('params', $param_array, $destroy);
		}
		parent::echoInnerJson($destroy);
	}

	public function dispose(): void
	{
		parent::dispose();
		unset($this->name);
		unset($this->parameters);
	}

	public function toJavaScript():string{
		$f = __METHOD__; //InvokeFunctionCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		$print = false;
		$fn = $this->getName();
		if ($fn instanceof JavaScriptInterface) {
			$fn = $fn->toJavaScript();
		}
		$params = $this->getParameterString(true);
		$string = "{$fn}({$params})";
		if ($this->hasEscapeType() && $this->getEscapeType() === ESCAPE_TYPE_STRING) {
			$q = $this->getQuoteStyle();
			if ($print) {
				Debug::print("{$f} quote style is \"{$q}\"");
			}
			$string = escape_quotes($string, $q);
			$string = "{$q}{$string}{$q}";
		}
		if ($print) {
			Debug::print("{$f} string is \"{$string}\"");
		}
		return $string;
	}

	/*
	 * public function evaluate(?array $params=null){
	 * return $this->toJavaScript();
	 * }
	 */
}
