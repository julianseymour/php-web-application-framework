<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use Exception;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;

class RemoveAttributeCommand extends ElementCommand implements ServerExecutableCommandInterface
{

	public function __construct($element, ...$attr_names)
	{
		parent::__construct($element);
		$this->setAttributeNames($attr_names);
	}

	public function setAttributeNames($attr_names)
	{
		return $this->setArrayProperty("attributeNames", $attr_names);
	}

	public function hasAttributeNames()
	{
		return $this->hasArrayProperty("attributeNames");
	}

	public static function getCommandId(): string
	{
		return "removeAttribute";
	}

	public function getAttributeNames()
	{
		return $this->getProperty("attributeNames");
	}

	public function toJavaScript(): string
	{
		$f = __METHOD__; //RemoveAttributeCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		try {
			$string = "";
			$id = $this->getIdCommandString();
			if ($id instanceof JavaScriptInterface) {
				$id = $id->toJavaScript();
			}
			$i = 0;
			foreach ($this->getAttributeNames() as $key) {
				if ($key instanceof JavaScriptInterface) {
					$key = $key->toJavaScript();
				} elseif (is_string($key) || $key instanceof StringifiableInterface) {
					$key = single_quote($key);
				}
				$string .= "{$id}.removeAttribute({$key})";
				if ($i ++ > 0) {
					$string .= ";\n";
				}
			}
			return $string;
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		$f = __METHOD__; //RemoveAttributeCommand::getShortClass()."(".static::getShortClass().")->echoInnerJson()";
		Json::echoKeyValuePair("id", $this->getId());
		Json::echoKeyValuePair("attributes", $this->getAttributeNames(), $destroy);
		parent::echoInnerJson($destroy);
	}
	
	public function resolve()
	{
		$f = __METHOD__; //RemoveAttributeCommand::getShortClass()."(".static::getShortClass().")->resolve()";
		try {
			$element = $this->getElement();
			while($element instanceof ValueReturningCommandInterface){
				$element = $element->evaluate();
			}
			foreach ($this->getAttributes() as $key) {
				$element->removeAttribute($key);
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}

}
