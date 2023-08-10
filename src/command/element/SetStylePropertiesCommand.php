<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use function JulianSeymour\PHPWebApplicationFramework\single_quote;
use function JulianSeymour\PHPWebApplicationFramework\x;
use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\command\ValueReturningCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\common\StringifiableInterface;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\script\JavaScriptInterface;
use JulianSeymour\PHPWebApplicationFramework\style\CssProperty;
use Exception;

class SetStylePropertiesCommand extends ElementCommand implements ServerExecutableCommandInterface
{

	// protected $styleProperties;
	public static function getCommandId(): string
	{
		return "style";
	}

	public function setStyleProperties($properties)
	{
		return $this->setArrayProperty("style", $properties);
		/*
		 * $f = __METHOD__; //SetStylePropertiesCommand::getShortClass()."(".static::getShortClass().")->setStyleProperties()";
		 * if(empty($properties)){
		 * Debug::error("{$f} properties array is empty");
		 * }
		 * return $this->styleProperties = $properties;
		 */
	}

	/**
	 *
	 * @return string[]|CssProperty[]
	 */
	public function getStyleProperties()
	{
		return $this->getProperty('style'); // Properties;
	}

	public function __construct($element, $properties)
	{
		$f = __METHOD__; //SetStylePropertiesCommand::getShortClass()."(".static::getShortClass().")->__construct()";
		try {
			parent::__construct($element);
			/*
			 * $arr = [];
			 * foreach($properties as $property){
			 * if(is_array($property)){
			 * Debug::error("{$f} property is an array");
			 * }
			 * array_push($arr, $property);
			 * }
			 */
			$this->setStyleProperties($properties);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	public function echoInnerJson(bool $destroy = false): void
	{
		$f = __METHOD__; //SetStylePropertiesCommand::getShortClass()."(".static::getShortClass().")->echoInnerJson()";
		try {
			$properties = [];
			foreach ($this->getStyleProperties() as $name => $property) {
				/*
				 * if(is_array($property)){
				 * Debug::error("{$f} singular property is an array");
				 * }
				 */
				if ($property instanceof CssProperty) {
					$value = $property->getValueString();
					/*
					 * if($property->isImportant()){
					 * $value = "{$value} !important";
					 * }
					 */
				} else {
					$value = $property;
				}
				$properties[$name] = $value;
			}
			Json::echoKeyValuePair('properties', $properties, $destroy);
			parent::echoInnerJson($destroy);
		} catch (Exception $x) {
			x($f, $x);
		}
	}

	/*
	 * public function dispose():void{
	 * parent::dispose();
	 * unset($this->styleProperties);
	 * }
	 */
	public function toJavaScript(): string
	{
		$f = __METHOD__; //SetStylePropertiesCommand::getShortClass()."(".static::getShortClass().")->toJavaScript()";
		try {
			$string = "";
			$e = $this->getIdCommandString();
			if ($e instanceof JavaScriptInterface) {
				$e = $e->toJavaScript();
			}
			$i = 0;
			foreach ($this->getStyleProperties() as $name => $property) {
				/*
				 * if(is_array($p)){
				 * Debug::error("{$f} property is an array");
				 * }
				 * $name = $p->getPropertyName();
				 * $value = $p->getPropertyValue();
				 */
				if ($property instanceof JavaScriptInterface) {
					$value = $property->toJavaScript();
				} elseif ($property instanceof CssProperty) {
					$value = $property->getValueString();
				} elseif (is_string($property) || $property instanceof StringifiableInterface) { // XXX check to see if it already has quotes
					$value = single_quote($property);
				} else {
					$value = $property;
				}
				if ($i ++ > 0) {
					$string .= ";\n";
				}
				$string .= "{$e}.style['{$name}'] = {$value}";
			}
			return $string;
		} catch (Exception $x) {
			X($f, $x);
		}
	}

	public function resolve()
	{
		$f = __METHOD__; //SetStylePropertiesCommand::getShortClass()."(".static::getShortClass().")->resolve()";
		try {
			$element = $this->getElement();
			while ($element instanceof ValueReturningCommandInterface) {
				$element = $element->evaluate();
			}
			foreach ($this->getStyleProperties() as $name => $value) {
				if ($value instanceof CssProperty) {
					$value = $value->getValueString();
				}
				$element->setStyleProperties([
					$name => $value
				]);
			}
		} catch (Exception $x) {
			x($f, $x);
		}
	}
}
	