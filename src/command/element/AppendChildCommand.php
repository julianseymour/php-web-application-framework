<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;

class AppendChildCommand extends InsertChildCommand
{

	public static function getInsertWhere()
	{
		return "appendChild";
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //AppendChildCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		try {
			$print = false;
			if ($this->getElementCount() > 1) {
				Debug::error("{$f} unimplemented: insert multiple elements");
			}
			if ($this->hasParentNode()) { // && $this->getParentNode()){
				$parent = $this->getParentNode();
				if ($parent instanceof Element) {
					$id = $parent->getIdOverride();
				} else {
					Debug::error("{$f} parent is not an element");
				}
			} elseif ($this->hasReferenceElementId()) {
				$id = $this->getReferenceElementId();
			} else {
				$decl = $this->getDeclarationLine();
				Debug::error("{$f} neither of the above. Declared {$decl}");
			}
			if ($id instanceof JavaScriptInterface) {
				$id = $id->toJavaScript();
			}
			if ($print) {
				Debug::print("{$f} insertion target ID is \"{$id}\"");
			}
			$elements = $this->getElements();
			/*
			 * if(!array_key_exists(0, $elements)){
			 * Debug::printArray(array_keys($elements));
			 * Debug::error("{$f} element 0 does not exist");
			 * }
			 */
			$element = $elements[array_keys($elements)[0]];
			if ($element instanceof ValueReturningCommandInterface) {
				$append_me = $element;
			} elseif (is_object($element) && $element->hasIdOverride()) {
				$append_me = $element->getIdOverride();
			} else {
				$gottype = is_object($element) ? $element->getClass() : gettype($element);
				Debug::error("{$f} element is not a value-returning command, and does not have an ID override; type is \"{$gottype}\"");
			}
			if ($append_me instanceof JavaScriptInterface) {
				$append_me = $append_me->toJavaScript();
			}
			$s = "";
			// $s .= CommandBuilder::log("About to append something called \"{$append_me}\"");
			// $s .= ";\n\t";
			$s .= "{$id}.appendChild({$append_me})";
			return $s;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function resolve()
	{
		$f = __METHOD__; //AppendChildCommand::getShortClass()."(".static::getShortClass().")->resolve()";
		$print = false;
		$elements = $this->getElements();
		foreach ($elements as $element) {
			while ($element instanceof ValueReturningCommandInterface) {
				if ($print) {
					$ec = $element->getClass();
					Debug::print("{$f} element is a value returning media command of class \"{$ec}\"");
				}
				$element = $element->evaluate();
			}
			$this->getParentNode()->appendChild($element);
		}
		return $elements;
	}

	public function evaluate(?array $params = null)
	{
		return $this->resolve();
	}
}
