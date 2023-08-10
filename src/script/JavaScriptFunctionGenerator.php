<?php
namespace JulianSeymour\PHPWebApplicationFramework\script;

use JulianSeymour\PHPWebApplicationFramework\core\Basic;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;

abstract class JavaScriptFunctionGenerator extends Basic
{

	protected $generatedFunction;

	public abstract function generate($context): ?JavaScriptFunction;

	public function __construct()
	{}

	public function hasGeneratedFunction(): bool
	{
		return isset($this->generatedFunction);
	}

	public function setGeneratedFunction(?JavaScriptFunction $fn): ?JavaScriptFunction
	{
		if ($fn == null) {
			unset($this->generatedFunction);
			return null;
		}
		return $this->generatedFunction = $fn;
	}

	public function getGeneratedFunction(): JavaScriptFunction
	{
		$f = __METHOD__; //JavaScriptFunctionGenerator::getShortClass()."(".static::getShortClass().")->getGeneratedFunction()";
		if (! $this->hasGeneratedFunction()) {
			Debug::error("{$f} generated function is undefined");
		}
		return $this->generatedFunction;
	}

	// XXX TODO have a parameter name list to map parameters to variable names
	public function evaluate($context, $params)
	{
		$f = __METHOD__; //JavaScriptFunctionGenerator::getShortClass()."(".static::getShortClass().")->evaluate()";
		// $context = $params[0];
		// $params = $params[1]; //array_slice($params, 1, count($params)-1);
		$print = false;
		if ($print) {
			if (isset($params)) {
				Debug::print("{$f} about to evaluate function for the following parameters:");
				Debug::printArray($params);
			} else {
				Debug::print("{$f} no parameters");
			}
		}
		if ($this->hasGeneratedFunction()) {
			return $this->getGeneratedFunction()->evaluate($params);
		}
		return $this->setGeneratedFunction($this->generate($context))
			->evaluate($params);
	}

	public function debugPrint($context)
	{
		$f = __METHOD__; //JavaScriptFunctionGenerator::getShortClass()."(".static::getShortClass().")->debugPrint()";
		if (! $this->hasGeneratedFunction()) {
			Debug::print("{$f} did not generate function yet");
			$this->setGeneratedFunction($this->generate($context));
		}
		$this->getGeneratedFunction()->debugPrint();
	}
}
