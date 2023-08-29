<?php
namespace JulianSeymour\PHPWebApplicationFramework\common;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use JulianSeymour\PHPWebApplicationFramework\common\ArrayPropertyTrait;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;

trait ParametricTrait{

	use ArrayPropertyTrait;

	public function hasParameters(){
		return $this->hasArrayProperty("parameters");
	}

	public function getParameters(){
		return $this->getProperty("parameters");
	}

	public function setParameters($params){
		$f = __METHOD__; //"ParametricTrait(".static::getShortClass().")->setParameters()";
		$print = $this->getDebugFlag();
		if (count($params) === 1 && is_array($params[0])) {
			$params = $params[0];
		}
		if ($print) {
			$count = count($params);
			Debug::print("{$f} prior to setting array property, there are {$count} parameters");
			Debug::printArray(($params));
			$ret = $this->setArrayProperty("parameters", $params);
			$count = $this->getParameterCount();
			Debug::print("{$f} after assigning array property, {$count} parameters");
			return $ret;
		}
		return $this->setArrayProperty("parameters", $params);
	}

	public function getParameterString(bool $js = false): ?string{
		$f = __METHOD__; //"ParametricTrait(".static::getShortClass().")->getParameterString()";
		$print = false;
		if (! $this->hasParameters()) {
			return null;
		}
		$string = "";
		$count = $this->getParameterCount();
		if ($print) {
			Debug::print("{$f} {$count} parameters");
		}
		foreach ($this->getParameters() as $p) {
			if (! empty($string)) {
				$string .= ", ";
			}
			if ($p instanceof Element) {
				if ($p->getTemplateFlag()) {
					$p = $p->getIdOverride();
				} else {
					$p = "this"; // XXX
				}
			} elseif ($js && $p instanceof JavaScriptInterface) {
				$p = $p->toJavaScript();
			} elseif (is_string($p)) {
				$p = single_quote($p);
			}
			if ($p === null){
				$string .= "null";
			}else{
				$string .= $p;
			}
		}
		return $string;
	}

	public function getParameter($i){
		return $this->getArrayPropertyValue("parameters", $i);
	}

	public function getParameterCount(){
		return $this->getArrayPropertyCount("parameters");
	}

	public function pushParameters(...$params){
		return $this->pushArrayProperty("parameters", ...$params);
	}

	public function unshiftParameters(...$params){
		return $this->unshiftArrayProperty("parameters", ...$params);
	}
	
	public function mergeParameters($values){
		return $this->mergeArrayProperty("parameters", $values);
	}

	public function withParameters(...$params){
		$this->setParameters($params);
		return $this;
	}

	public function debugPrintParameters(){
		$f = __METHOD__; //"ParametricTrait(".static::getShortClass().")->debugPrintParameters()";
		if (! $this->hasParameters()) {
			Debug::print("{$f} no parameters");
		} else {
			foreach ($this->getParameters() as $name => $p) {
				$pc = is_object($p) ? $p->getClass() : gettype($p);
				Debug::print("{$f} parameter {$name} is a {$pc}");
			}
		}
	}
}
