<?php
namespace JulianSeymour\PHPWebApplicationFramework\command\element;

use JulianSeymour\PHPWebApplicationFramework\command\ServerExecutableCommandInterface;
use JulianSeymour\PHPWebApplicationFramework\core\Debug;
use JulianSeymour\PHPWebApplicationFramework\element\Element;
use JulianSeymour\PHPWebApplicationFramework\json\Json;
use JulianSeymour\PHPWebApplicationFramework\use_case\UseCase;

abstract class AttributeCommand extends ElementCommand implements ServerExecutableCommandInterface{
	
	/**
	 *
	 * @param Element|string $element
	 * @param array $attributes
	 *        	: list of key value pairs
	 */
	public function __construct($element, $attributes)
	{
		$f = __METHOD__; //AttributeCommand::getShortClass()."(".static::getShortClass().")->__construct()";
		parent::__construct($element);
		if(!empty($attributes)) {
			if(!is_array($attributes)) {
				Debug::printStackTraceNoExit("{$f} received something that is not an array");
			}
			$this->setAttributes($attributes);
		}
	}
	
	public function getAttributes()
	{
		return $this->getProperty("attributes");
	}
	
	public function hasAttributes()
	{
		return $this->hasArrayProperty("attributes");
	}
	
	public function getAttributeCount()
	{
		return $this->getArrayPropertyCount("attributes");
	}
	
	public function setAttributes($attr)
	{
		$f = __METHOD__; //AttributeCommand::getShortClass()."(".static::getShortClass().")->setAttributes()";
		foreach($attr as $key => $value) {
			if($value instanceof UseCase) {
				Debug::error("{$f} attempting to set attribute \"{$key}\" to a UseCase");
			}
		}
		return $this->setArrayProperty("attributes", $attr);
	}
	
	public function hasAttribute($key)
	{
		return $this->hasAttributes() && array_key_exists($key, $this->attributes);
	}
	
	public function getAttribute($key)
	{
		$f = __METHOD__; //AttributeCommand::getShortClass()."(".static::getShortClass().")->getAttribute()";
		$print = false;
		if(!$this->hasAttribute($key)) {
			Debug::error("{$f} attribute \"{$key}\" is undefined");
		}
		$attr = $this->getArrayPropertyValue("attributes", $key);
		if($print) {
			Debug::print("{$f} returning \"{$attr}\"");
		}
		return $attr;
	}
	
	public function echoInnerJson(bool $destroy = false): void
	{
		$f = __METHOD__; //AttributeCommand::getShortClass()."(".static::getShortClass().")->echoInnerJson()";
		Json::echoKeyValuePair("id", $this->getId());
		Json::echoKeyValuePair("attributes", $this->getAttributes(), $destroy);
		parent::echoInnerJson($destroy);
	}
}

